<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

/**
 * Controla os dispositivos (navegadores) confiáveis de cada usuário.
 *
 * Cada navegador recebe um cookie HttpOnly com um token aleatório; no banco
 * guardamos apenas o hash SHA-256 desse token, associado ao usuário. Um mesmo
 * navegador usado por vários usuários tem um token só e uma linha por usuário.
 */
class device_manager {

    /** @var string prefixo do nome do cookie (completado com $CFG->sessioncookie). */
    const COOKIE_PREFIX = 'MOODLEGEOIPDEVICE';

    /** @var int só atualiza timelastused se a última atualização tiver mais que isso. */
    const LASTUSED_THROTTLE = HOURSECS;

    /** @var string dispositivo confiável, mesmo navegador/SO de quando foi confiado. */
    const STATUS_TRUSTED = 'trusted';

    /** @var string dispositivo desconhecido (sem cookie, cookie inválido ou expirado). */
    const STATUS_UNKNOWN = 'unknown';

    /** @var string dispositivo confiável, mas o navegador ou o sistema operacional mudou. */
    const STATUS_UACHANGED = 'uachanged';

    /**
     * Situação deste navegador em relação aos dispositivos confiáveis do usuário.
     *
     * @return string uma das constantes STATUS_*.
     */
    public static function get_status(int $userid): string {
        global $DB;

        $token = self::get_cookie_token();
        if ($token === null) {
            return self::STATUS_UNKNOWN;
        }

        $record = $DB->get_record('factor_geoip_devices', [
            'userid' => $userid,
            'tokenhash' => self::hash($token),
        ]);
        if (!$record) {
            return self::STATUS_UNKNOWN;
        }

        if ($record->timelastused < time() - self::get_expiry()) {
            $DB->delete_records('factor_geoip_devices', ['id' => $record->id]);
            return self::STATUS_UNKNOWN;
        }

        if ($record->timelastused < time() - self::LASTUSED_THROTTLE) {
            $DB->set_field('factor_geoip_devices', 'timelastused', time(), ['id' => $record->id]);
        }

        if (!empty($record->useragent)
                && self::ua_signature($record->useragent) !== self::ua_signature(self::get_useragent())) {
            return self::STATUS_UACHANGED;
        }
        return self::STATUS_TRUSTED;
    }

    /**
     * Resume o user agent em "navegador|sistema", ignorando versões (que mudam a cada atualização).
     */
    public static function ua_signature(string $ua): string {
        $browsers = [
            'Edge' => '/Edg(e|A|iOS)?\//',
            'Opera' => '/OPR\/|Opera/',
            'Samsung' => '/SamsungBrowser\//',
            'Firefox' => '/Firefox\/|FxiOS\//',
            'Chrome' => '/Chrome\/|CriOS\//',
            'Safari' => '/Safari\//',
        ];
        $systems = [
            'Windows' => '/Windows/',
            'Android' => '/Android/',
            'iOS' => '/iPhone|iPad|iPod/',
            'macOS' => '/Mac OS X|Macintosh/',
            'ChromeOS' => '/CrOS/',
            'Linux' => '/Linux/',
        ];

        $match = function (array $patterns) use ($ua): string {
            foreach ($patterns as $name => $pattern) {
                if (preg_match($pattern, $ua)) {
                    return $name;
                }
            }
            return 'other';
        };
        return $match($browsers) . '|' . $match($systems);
    }

    /**
     * @return bool true se o usuário tem algum dispositivo confiável válido registrado.
     */
    public static function has_devices(int $userid): bool {
        global $DB;

        return $DB->record_exists_select('factor_geoip_devices', 'userid = :userid AND timelastused >= :cutoff', [
            'userid' => $userid,
            'cutoff' => time() - self::get_expiry(),
        ]);
    }

    /**
     * Marca este navegador como dispositivo confiável do usuário (cria o cookie se preciso).
     */
    public static function trust(int $userid, string $ip): void {
        global $DB;

        $token = self::get_cookie_token();
        if ($token === null) {
            $token = bin2hex(random_bytes(32));
            if (!self::set_cookie($token)) {
                // Cabeçalhos já enviados: não dá para gravar o cookie, então não registra.
                return;
            }
        } else {
            // Renova a validade do cookie existente.
            self::set_cookie($token);
        }

        $hash = self::hash($token);
        $useragent = \core_text::substr(self::get_useragent(), 0, 255);
        $existing = $DB->get_record('factor_geoip_devices', ['userid' => $userid, 'tokenhash' => $hash]);
        if ($existing) {
            // Atualiza o user agent: a nova combinação navegador/SO passa a ser a esperada.
            $existing->useragent = $useragent;
            $existing->ip = $ip;
            $existing->timelastused = time();
            $DB->update_record('factor_geoip_devices', $existing);
            return;
        }

        $record = new \stdClass();
        $record->userid = $userid;
        $record->tokenhash = $hash;
        $record->useragent = $useragent;
        $record->ip = $ip;
        $record->timecreated = time();
        $record->timelastused = time();
        $DB->insert_record('factor_geoip_devices', $record);
    }

    /**
     * Remove dispositivos sem uso há mais tempo que a validade configurada.
     */
    public static function delete_expired(): void {
        global $DB;

        $DB->delete_records_select('factor_geoip_devices', 'timelastused < :cutoff',
            ['cutoff' => time() - self::get_expiry()]);
    }

    private static function get_useragent(): string {
        return (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    }

    private static function get_expiry(): int {
        return ((int) get_config('factor_geoip', 'deviceexpiry') ?: 90) * DAYSECS;
    }

    private static function get_cookie_name(): string {
        global $CFG;
        return self::COOKIE_PREFIX . ($CFG->sessioncookie ?? '');
    }

    private static function get_cookie_token(): ?string {
        $token = $_COOKIE[self::get_cookie_name()] ?? '';
        return preg_match('/^[0-9a-f]{64}$/', $token) ? $token : null;
    }

    private static function set_cookie(string $token): bool {
        global $CFG;

        if (headers_sent()) {
            return false;
        }

        setcookie(self::get_cookie_name(), $token, [
            'expires' => time() + self::get_expiry(),
            'path' => empty($CFG->sessioncookiepath) ? '/' : $CFG->sessioncookiepath,
            'domain' => $CFG->sessioncookiedomain ?? '',
            'secure' => is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        // Disponível ainda nesta requisição (get_state é chamado várias vezes por página).
        $_COOKIE[self::get_cookie_name()] = $token;
        return true;
    }

    private static function hash(string $token): string {
        return hash('sha256', $token);
    }
}

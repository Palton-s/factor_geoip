<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

use stdClass;

/**
 * Avalia os sinais de risco de um login e devolve os motivos para exigir o
 * código por e-mail. Lista vazia = login normal, não precisa de código.
 *
 * Sinais avaliados (cada um pode ser ligado/desligado nas configurações):
 *  - distantlocation:  IP a mais de X km da última localização confiável;
 *  - countrychange:    país diferente do da localização confiável;
 *  - impossibletravel: deslocamento desde o último login acima da velocidade máxima;
 *  - anonymousip:      IP de VPN/proxy/Tor;
 *  - failedlogins:     senhas erradas desde o último login bem-sucedido;
 *  - unusualtime:      horário ou dia da semana incomum (fuso do usuário);
 *  - newdevice:        navegador ainda não verificado pelo usuário;
 *  - useragentchange:  dispositivo conhecido, mas com outro navegador/SO.
 */
class risk_checker {

    /** @var int distância mínima (km) para considerar viagem impossível (imprecisão do GeoIP). */
    const TRAVEL_MIN_KM = 100;

    /**
     * @return string[] códigos dos motivos (ver lista acima), na ordem em que foram detectados.
     */
    public static function evaluate(stdClass $user, string $ip): array {
        global $DB;

        $reasons = [];
        $geo = geolocator::lookup($ip);
        $trusted = $DB->get_record('factor_geoip_trusted', ['userid' => $user->id]);
        $challengefirst = (bool) get_config('factor_geoip', 'challengefirstlogin');

        // Localização e país, comparados com a localização confiável.
        if ($geo !== null) {
            if (!$trusted) {
                if ($challengefirst) {
                    $reasons[] = 'distantlocation';
                } else {
                    // Primeiro login: apenas registra a localização como confiável.
                    self::remember_location($user->id, $ip, $geo);
                }
            } else {
                $distancekm = (int) get_config('factor_geoip', 'distancekm') ?: 500;
                $distance = geolocator::distance_km(
                    (float) $trusted->latitude, (float) $trusted->longitude, $geo['lat'], $geo['lon']);
                if ($distance > $distancekm) {
                    $reasons[] = 'distantlocation';
                }
                if (get_config('factor_geoip', 'checkcountry') && !empty($trusted->country)
                        && $geo['country'] !== '' && $trusted->country !== $geo['country']) {
                    $reasons[] = 'countrychange';
                }
            }
        }

        // Viagem impossível, comparada com o último login visto (confiável ou não).
        if ($geo !== null && get_config('factor_geoip', 'checktravel')) {
            if (self::is_impossible_travel($user->id, $geo)) {
                $reasons[] = 'impossibletravel';
            }
            self::remember_last_seen($user->id, $ip, $geo);
        }

        if (get_config('factor_geoip', 'checkanonymous')
                && (tor_list::contains($ip) || geolocator::is_anonymous($ip))) {
            $reasons[] = 'anonymousip';
        }

        if (get_config('factor_geoip', 'checkfailedlogins')) {
            $threshold = (int) get_config('factor_geoip', 'failedloginsthreshold') ?: 3;
            if (self::count_failed_logins($user->id) >= $threshold) {
                $reasons[] = 'failedlogins';
            }
        }

        if (get_config('factor_geoip', 'checkhours') && self::is_unusual_time($user)) {
            $reasons[] = 'unusualtime';
        }

        // Dispositivo por último: o primeiro dispositivo só é confiado sem código
        // se nenhum outro sinal de risco apareceu.
        if (get_config('factor_geoip', 'checkdevice')) {
            $status = device_manager::get_status($user->id);
            if ($status === device_manager::STATUS_UNKNOWN) {
                if (!$reasons && !$challengefirst && !device_manager::has_devices($user->id)) {
                    device_manager::trust($user->id, $ip);
                } else {
                    $reasons[] = 'newdevice';
                }
            } else if ($status === device_manager::STATUS_UACHANGED && get_config('factor_geoip', 'checkuseragent')) {
                $reasons[] = 'useragentchange';
            }
        }

        return $reasons;
    }

    /**
     * Grava a localização atual como a localização confiável do usuário.
     */
    public static function remember_location(int $userid, string $ip, array $geo): void {
        self::upsert('factor_geoip_trusted', $userid, [
            'ip' => $ip,
            'latitude' => $geo['lat'],
            'longitude' => $geo['lon'],
            'country' => $geo['country'],
            'city' => $geo['city'],
            'timecreated' => time(),
        ]);
    }

    private static function remember_last_seen(int $userid, string $ip, array $geo): void {
        self::upsert('factor_geoip_lastseen', $userid, [
            'ip' => $ip,
            'latitude' => $geo['lat'],
            'longitude' => $geo['lon'],
            'country' => $geo['country'],
            'timeseen' => time(),
        ]);
    }

    private static function is_impossible_travel(int $userid, array $geo): bool {
        global $DB;

        $last = $DB->get_record('factor_geoip_lastseen', ['userid' => $userid]);
        if (!$last) {
            return false;
        }

        $distance = geolocator::distance_km((float) $last->latitude, (float) $last->longitude, $geo['lat'], $geo['lon']);
        if ($distance < self::TRAVEL_MIN_KM) {
            return false;
        }

        // Mínimo de 1 minuto para evitar divisão por zero.
        $hours = max(time() - (int) $last->timeseen, MINSECS) / HOURSECS;
        $maxspeed = (int) get_config('factor_geoip', 'travelmaxspeed') ?: 900;
        return ($distance / $hours) > $maxspeed;
    }

    /**
     * Senhas erradas desde o último login bem-sucedido (contador do próprio Moodle).
     */
    private static function count_failed_logins(int $userid): int {
        global $DB;

        // Lido direto do banco: o cache de preferências em $USER pode estar desatualizado.
        return (int) $DB->get_field('user_preferences', 'value',
            ['userid' => $userid, 'name' => 'login_failed_count_since_success']);
    }

    /**
     * Horário dentro da janela incomum ou dia da semana marcado como incomum, no fuso do usuário.
     */
    private static function is_unusual_time(stdClass $user): bool {
        $now = new \DateTime('now', \core_date::get_user_timezone_object($user));
        $hour = (int) $now->format('G');
        $weekday = $now->format('w'); // 0 = domingo ... 6 = sábado.

        $days = array_filter(explode(',', (string) get_config('factor_geoip', 'unusualdays')), 'strlen');
        if (in_array($weekday, $days, true)) {
            return true;
        }

        $start = (int) get_config('factor_geoip', 'unusualhourstart');
        $end = (int) get_config('factor_geoip', 'unusualhourend');
        if ($start === $end) {
            return false; // Janela vazia.
        }
        return $start < $end
            ? ($hour >= $start && $hour < $end)
            : ($hour >= $start || $hour < $end); // Janela que passa da meia-noite (ex.: 22h-6h).
    }

    private static function upsert(string $table, int $userid, array $fields): void {
        global $DB;

        $record = (object) (['userid' => $userid] + $fields);
        $existing = $DB->get_record($table, ['userid' => $userid], 'id');
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record($table, $record);
        } else {
            $DB->insert_record($table, $record);
        }
    }
}

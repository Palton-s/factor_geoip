<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

defined('MOODLE_INTERNAL') || die();

/**
 * Gera, envia por e-mail e valida os códigos de verificação de 6 dígitos
 * disparados quando um login vem de uma localização muito distante da
 * última localização confiável do usuário.
 */
class code_manager {

    /**
     * Revoga códigos antigos, gera um novo e envia por e-mail ao usuário.
     */
    public static function issue(\stdClass $user, string $ip): void {
        global $DB;

        // Revoga qualquer código pendente anterior deste usuário.
        $DB->set_field('factor_geoip_codes', 'revoked', 1, ['userid' => $user->id, 'revoked' => 0]);

        $expiryminutes = (int) get_config('factor_geoip', 'codeexpiry') ?: 10;
        $secret = self::generate_code();

        $record = new \stdClass();
        $record->userid = $user->id;
        $record->secret = $secret;
        $record->ip = $ip;
        $record->attempts = 0;
        $record->timecreated = time();
        $record->timeexpires = time() + ($expiryminutes * MINSECS);
        $record->revoked = 0;
        $DB->insert_record('factor_geoip_codes', $record);

        self::send_email($user, $secret, $ip, $expiryminutes);
    }

    /**
     * @return bool true se o código informado é válido para o usuário.
     */
    public static function verify(\stdClass $user, string $code): bool {
        global $DB;

        $record = $DB->get_record('factor_geoip_codes', [
            'userid' => $user->id,
            'revoked' => 0,
        ], '*', IGNORE_MULTIPLE);

        if (!$record) {
            return false;
        }

        $maxattempts = (int) get_config('factor_geoip', 'maxattempts') ?: 5;

        if ($record->timeexpires < time() || $record->attempts >= $maxattempts) {
            $DB->set_field('factor_geoip_codes', 'revoked', 1, ['id' => $record->id]);
            return false;
        }

        // Comparação em tempo constante para evitar timing attacks.
        if (hash_equals($record->secret, $code)) {
            $DB->set_field('factor_geoip_codes', 'revoked', 1, ['id' => $record->id]);
            return true;
        }

        $DB->set_field('factor_geoip_codes', 'attempts', $record->attempts + 1, ['id' => $record->id]);
        return false;
    }

    private static function generate_code(): string {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private static function send_email(\stdClass $user, string $code, string $ip, int $expiryminutes): void {
        $site = get_site();
        $subject = get_string('email_subject', 'factor_geoip', $site->fullname);

        $geo = geolocator::lookup($ip);
        $location = $geo ? trim(($geo['city'] ?? '') . ' - ' . ($geo['country'] ?? ''), ' -')
            : get_string('unknownlocation', 'factor_geoip');

        $a = new \stdClass();
        $a->code = $code;
        $a->ip = $ip;
        $a->location = $location;
        $a->minutes = $expiryminutes;
        $a->fullname = fullname($user);

        $messagetext = get_string('email_body', 'factor_geoip', $a);
        $messagehtml = format_text($messagetext, FORMAT_PLAIN, ['para' => false]);

        $noreplyuser = \core_user::get_noreply_user();
        email_to_user($user, $noreplyuser, $subject, $messagetext, $messagehtml);
    }
}

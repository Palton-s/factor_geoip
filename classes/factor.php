<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/admin/tool/mfa/classes/local/factor/object_factor_base.php');

use tool_mfa\local\factor\object_factor_base;

/**
 * Fator de MFA adaptativo: só exige um código extra (por e-mail) quando o
 * login atual vem de um IP geograficamente muito distante da última
 * localização confiável do usuário. Logins "normais" passam direto.
 */
class factor extends object_factor_base {

    /** @var string chave de sessão que guarda o IP já validado nesta sessão. */
    const SESSION_KEY = 'factor_geoip_validated_ip';

    /**
     * Decide se este fator está satisfeito, pendente ou não aplicável
     * para o usuário logado no momento.
     */
    public function get_state() {
        global $USER, $SESSION;

        $ip = getremoteaddr();

        // Já validado nesta mesma sessão para este IP: não pergunta de novo.
        if (!empty($SESSION->{self::SESSION_KEY}) && $SESSION->{self::SESSION_KEY} === $ip) {
            return \tool_mfa\plugininfo\factor::STATE_PASS;
        }

        if (!$this->is_login_distant($USER, $ip)) {
            // Localização normal (ou não foi possível geolocalizar): não bloqueia o login.
            return \tool_mfa\plugininfo\factor::STATE_PASS;
        }

        return \tool_mfa\plugininfo\factor::STATE_NEEDSETUP === $this->get_state_from_setup()
            ? \tool_mfa\plugininfo\factor::STATE_NEEDSETUP
            : \tool_mfa\plugininfo\factor::STATE_UNKNOWN;
    }

    /**
     * Verifica se o IP atual está a mais de X km da última localização
     * confiável salva para o usuário, disparando o código por e-mail
     * (uma única vez por tentativa, controlado via sessão) quando estiver.
     */
    private function is_login_distant(\stdClass $user, string $ip): bool {
        global $DB, $SESSION;

        $current = geolocator::lookup($ip);
        if ($current === null) {
            // Sem dados de geolocalização (IP privado, base ausente, etc.) -> não bloqueia.
            return false;
        }

        $trusted = $DB->get_record('factor_geoip_trusted', ['userid' => $user->id]);

        if (!$trusted) {
            $challengefirst = (bool) get_config('factor_geoip', 'challengefirstlogin');
            if (!$challengefirst) {
                $this->remember_location($user->id, $ip, $current);
                return false;
            }
            $distant = true;
        } else {
            $distancekm = (int) get_config('factor_geoip', 'distancekm') ?: 500;
            $distance = geolocator::distance_km(
                (float) $trusted->latitude, (float) $trusted->longitude,
                $current['lat'], $current['lon']
            );
            $distant = $distance > $distancekm;
        }

        if ($distant && empty($SESSION->factor_geoip_code_sent)) {
            code_manager::issue($user, $ip);
            $SESSION->factor_geoip_code_sent = true;
        }

        return $distant;
    }

    private function remember_location(int $userid, string $ip, array $geo): void {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->ip = $ip;
        $record->latitude = $geo['lat'];
        $record->longitude = $geo['lon'];
        $record->country = $geo['country'];
        $record->city = $geo['city'];
        $record->timecreated = time();

        $existing = $DB->get_record('factor_geoip_trusted', ['userid' => $userid]);
        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('factor_geoip_trusted', $record);
        } else {
            $DB->insert_record('factor_geoip_trusted', $record);
        }
    }

    /**
     * Este fator não tem "setup" de usuário (nada para o usuário configurar
     * antes; ele só entra em ação reativamente quando detecta distância).
     */
    public function has_setup() {
        return false;
    }

    public function login_form_render(\MoodleQuickForm &$mform) {
        $mform->addElement('text', 'verificationcode', get_string('verificationcode', 'factor_geoip'));
        $mform->setType('verificationcode', PARAM_ALPHANUM);
        $mform->addRule('verificationcode', get_string('required'), 'required', null, 'client');
        $mform->addElement('static', 'geoipinfo', '', get_string('checkemail_desc', 'factor_geoip'));
    }

    public function login_form_definition_after_data(\MoodleQuickForm &$mform) {
        // Nada adicional necessário.
    }

    public function login_form_validation($data) {
        global $USER;

        $errors = [];
        if (empty($data['verificationcode']) || !code_manager::verify($USER, $data['verificationcode'])) {
            $errors['verificationcode'] = get_string('error:invalidcode', 'factor_geoip');
        }
        return $errors;
    }

    /**
     * Chamado pelo tool_mfa após o fator ser confirmado com sucesso.
     */
    public function post_pass_state() {
        global $USER, $SESSION;

        $ip = getremoteaddr();
        $geo = geolocator::lookup($ip);
        if ($geo !== null) {
            $this->remember_location($USER->id, $ip, $geo);
        }

        $SESSION->{self::SESSION_KEY} = $ip;
        unset($SESSION->factor_geoip_code_sent);

        parent::post_pass_state();
    }

    public function get_summary_condition(): string {
        return get_string('summarycondition', 'factor_geoip');
    }
}

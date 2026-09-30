<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip;

use stdClass;
use tool_mfa\local\factor\object_factor_base;
use tool_mfa\plugininfo\factor as factorinfo;

/**
 * Fator de MFA adaptativo: só exige um código extra (por e-mail) quando o
 * login apresenta algum sinal de risco (ver risk_checker): localização
 * distante, mudança de país, viagem impossível, VPN/proxy/Tor, senhas erradas,
 * horário incomum, dispositivo novo ou navegador/SO diferente.
 * Logins "normais" passam direto.
 */
class factor extends object_factor_base {

    /** @var string chave de sessão que guarda o IP já validado nesta sessão. */
    const SESSION_KEY = 'factor_geoip_validated_ip';

    /** @var string chave de sessão com a avaliação de risco (IP avaliado e motivos). */
    const EVAL_KEY = 'factor_geoip_eval';

    /**
     * Decide se este fator está satisfeito ou pendente para o usuário logado.
     */
    public function get_state(): string {
        global $USER, $SESSION;

        // Estado já definido pelo tool_mfa nesta sessão (pass/fail após o formulário).
        $state = parent::get_state();
        if ($state !== factorinfo::STATE_UNKNOWN) {
            return $state;
        }

        $ip = getremoteaddr();

        // Já validado nesta mesma sessão para este IP: não pergunta de novo.
        if (!empty($SESSION->{self::SESSION_KEY}) && $SESSION->{self::SESSION_KEY} === $ip) {
            return factorinfo::STATE_PASS;
        }

        // Avalia uma única vez por sessão/IP: get_state é chamado várias vezes por
        // página e algumas checagens (ex.: viagem impossível) atualizam o histórico.
        $eval = $SESSION->{self::EVAL_KEY} ?? null;
        if (!is_array($eval) || $eval['ip'] !== $ip) {
            $eval = ['ip' => $ip, 'reasons' => risk_checker::evaluate($USER, $ip)];
            $SESSION->{self::EVAL_KEY} = $eval;
        }

        return $eval['reasons'] ? factorinfo::STATE_UNKNOWN : factorinfo::STATE_PASS;
    }

    /**
     * Só mostra o formulário de código quando há algum sinal de risco.
     */
    public function has_input(): bool {
        return $this->get_state() === factorinfo::STATE_UNKNOWN;
    }

    /**
     * Este fator não tem "setup" de usuário (nada para o usuário configurar
     * antes; ele só entra em ação reativamente quando detecta risco).
     */
    public function has_setup(): bool {
        return false;
    }

    /**
     * O tool_mfa só considera o fator "ativo" para o usuário se houver um
     * registro dele na tabela tool_mfa. Como este fator não tem setup, cria o
     * registro automaticamente (mesmo padrão dos fatores iprange e email).
     */
    public function get_all_user_factors(stdClass $user): array {
        global $DB;

        $records = $DB->get_records('tool_mfa', ['userid' => $user->id, 'factor' => $this->name]);
        if (!empty($records)) {
            return $records;
        }

        $record = [
            'userid' => $user->id,
            'factor' => $this->name,
            'createdfromip' => $user->lastip ?? getremoteaddr(),
            'timecreated' => time(),
            'timemodified' => time(),
            'revoked' => 0,
        ];
        $record['id'] = $DB->insert_record('tool_mfa', $record, true);
        return [(object) $record];
    }

    public function possible_states(stdClass $user): array {
        return [
            factorinfo::STATE_PASS,
            factorinfo::STATE_FAIL,
            factorinfo::STATE_UNKNOWN,
        ];
    }

    public function login_form_definition(\MoodleQuickForm $mform): \MoodleQuickForm {
        $mform->addElement('static', 'geoipinfo', '',
            get_string('checkemail_desc', 'factor_geoip', $this->get_reasons_text()));
        $mform->addElement(new \tool_mfa\local\form\verification_field());
        $mform->setType('verificationcode', PARAM_ALPHANUM);
        return $mform;
    }

    /**
     * Envia o código por e-mail (uma única vez por tentativa, controlado via sessão).
     */
    public function login_form_definition_after_data(\MoodleQuickForm $mform): \MoodleQuickForm {
        global $USER, $SESSION;

        if (empty($SESSION->factor_geoip_code_sent)) {
            code_manager::issue($USER, getremoteaddr(), $this->get_reasons_text());
            $SESSION->factor_geoip_code_sent = true;
        }
        return $mform;
    }

    /**
     * Texto legível com os motivos do desafio atual (ex.: "dispositivo novo").
     */
    private function get_reasons_text(): string {
        global $SESSION;

        $reasons = $SESSION->{self::EVAL_KEY}['reasons'] ?? [];
        return implode('; ', array_map(fn($r) => get_string('reason_' . $r, 'factor_geoip'), $reasons));
    }

    public function login_form_validation(array $data): array {
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
    public function post_pass_state(): void {
        global $USER, $SESSION;

        $ip = getremoteaddr();
        $geo = geolocator::lookup($ip);
        if ($geo !== null) {
            risk_checker::remember_location($USER->id, $ip, $geo);
        }
        if (get_config('factor_geoip', 'checkdevice')) {
            device_manager::trust($USER->id, $ip);
        }

        $SESSION->{self::SESSION_KEY} = $ip;
        unset($SESSION->factor_geoip_code_sent, $SESSION->{self::EVAL_KEY});

        parent::post_pass_state();
    }

    public function get_summary_condition(): string {
        return get_string('summarycondition', 'factor_geoip');
    }
}

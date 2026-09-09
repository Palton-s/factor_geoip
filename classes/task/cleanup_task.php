<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Remove códigos de verificação expirados/revogados antigos.
 */
class cleanup_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('cleanuptask', 'factor_geoip');
    }

    public function execute() {
        global $DB;

        // Mantém histórico por 1 dia após expirar/revogar, só por auditoria; depois apaga.
        $cutoff = time() - DAYSECS;
        $DB->delete_records_select('factor_geoip_codes', 'timeexpires < :cutoff', ['cutoff' => $cutoff]);
    }
}

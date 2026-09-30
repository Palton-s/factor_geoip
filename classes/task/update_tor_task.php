<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip\task;

/**
 * Atualiza a lista local de nós de saída do Tor.
 */
class update_tor_task extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('updatetortask', 'factor_geoip');
    }

    public function execute() {
        if (!get_config('factor_geoip', 'checkanonymous')) {
            mtrace('factor_geoip: verificação de VPN/proxy/Tor desligada, nada a fazer.');
            return;
        }
        $count = \factor_geoip\tor_list::update();
        mtrace("factor_geoip: lista do Tor atualizada ({$count} IPs).");
    }
}

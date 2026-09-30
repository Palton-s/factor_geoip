<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

/**
 * Passos de atualização do factor_geoip.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_factor_geoip_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026093004) {
        // Tabela de dispositivos confiáveis.
        $table = new xmldb_table('factor_geoip_devices');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('tokenhash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('useragent', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ip', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timelastused', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('userid_tokenhash', XMLDB_INDEX_UNIQUE, ['userid', 'tokenhash']);
        $table->add_index('timelastused', XMLDB_INDEX_NOTUNIQUE, ['timelastused']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Padrões das novas configurações (a verificação de dispositivo vem ligada).
        if (get_config('factor_geoip', 'checkdevice') === false) {
            set_config('checkdevice', 1, 'factor_geoip');
        }
        if (get_config('factor_geoip', 'deviceexpiry') === false) {
            set_config('deviceexpiry', 90, 'factor_geoip');
        }

        upgrade_plugin_savepoint(true, 2026093004, 'factor', 'geoip');
    }

    if ($oldversion < 2026093005) {
        // Localização do login mais recente, para detectar viagem impossível.
        $table = new xmldb_table('factor_geoip_lastseen');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('ip', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
        $table->add_field('latitude', XMLDB_TYPE_NUMBER, '10, 6', null, XMLDB_NOTNULL, null, null);
        $table->add_field('longitude', XMLDB_TYPE_NUMBER, '10, 6', null, XMLDB_NOTNULL, null, null);
        $table->add_field('country', XMLDB_TYPE_CHAR, '2', null, null, null, null);
        $table->add_field('timeseen', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN_UNIQUE, ['userid'], 'user', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Padrões das novas verificações.
        $defaults = [
            'checkuseragent' => 1,
            'checkcountry' => 1,
            'checktravel' => 1,
            'travelmaxspeed' => 900,
            'checkanonymous' => 1,
            'anonmmdbpath' => '',
            'checkfailedlogins' => 1,
            'failedloginsthreshold' => 3,
            'checkhours' => 1,
            'unusualhourstart' => 0,
            'unusualhourend' => 6,
            'unusualdays' => '',
        ];
        foreach ($defaults as $name => $value) {
            if (get_config('factor_geoip', $name) === false) {
                set_config($name, $value, 'factor_geoip');
            }
        }

        upgrade_plugin_savepoint(true, 2026093005, 'factor', 'geoip');
    }

    return true;
}

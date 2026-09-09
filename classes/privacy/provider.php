<?php
// This file is part of Moodle - http://moodle.org/

namespace factor_geoip\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('factor_geoip_trusted', [
            'userid' => 'privacy:metadata:factor_geoip_trusted:userid',
            'ip' => 'privacy:metadata:factor_geoip_trusted:ip',
            'latitude' => 'privacy:metadata:factor_geoip_trusted:latitude',
            'longitude' => 'privacy:metadata:factor_geoip_trusted:longitude',
            'country' => 'privacy:metadata:factor_geoip_trusted:country',
            'city' => 'privacy:metadata:factor_geoip_trusted:city',
            'timecreated' => 'privacy:metadata:factor_geoip_trusted:timecreated',
        ], 'privacy:metadata:factor_geoip_trusted');

        $collection->add_database_table('factor_geoip_codes', [
            'userid' => 'privacy:metadata:factor_geoip_trusted:userid',
            'ip' => 'privacy:metadata:factor_geoip_trusted:ip',
            'timecreated' => 'privacy:metadata:factor_geoip_trusted:timecreated',
        ], 'privacy:metadata:factor_geoip_codes');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        // Dados são sempre em nível de sistema (login), não amarrados a um contexto de curso/módulo.
        $contextlist = new contextlist();
        $contextlist->add_system_context();
        return $contextlist;
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        $trusted = $DB->get_record('factor_geoip_trusted', ['userid' => $user->id]);
        if ($trusted) {
            writer::with_context(\context_system::instance())->export_data(
                [get_string('pluginname', 'factor_geoip'), 'trusted_location'],
                $trusted
            );
        }

        $codes = $DB->get_records('factor_geoip_codes', ['userid' => $user->id]);
        if ($codes) {
            writer::with_context(\context_system::instance())->export_data(
                [get_string('pluginname', 'factor_geoip'), 'verification_codes'],
                (object) ['count' => count($codes)]
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        global $DB;
        $DB->delete_records('factor_geoip_trusted');
        $DB->delete_records('factor_geoip_codes');
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        $DB->delete_records('factor_geoip_trusted', ['userid' => $userid]);
        $DB->delete_records('factor_geoip_codes', ['userid' => $userid]);
    }

    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        global $DB;
        foreach ($userlist->get_userids() as $userid) {
            $DB->delete_records('factor_geoip_trusted', ['userid' => $userid]);
            $DB->delete_records('factor_geoip_codes', ['userid' => $userid]);
        }
    }
}

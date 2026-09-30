<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'factor_geoip\task\cleanup_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
    [
        'classname' => 'factor_geoip\task\update_tor_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '*/6',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
];

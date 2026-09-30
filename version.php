<?php
// This file is part of Moodle - http://moodle.org/
//
// factor_geoip - fator de MFA adaptativo baseado em geolocalização de IP.

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2026093004;
$plugin->requires  = 2023100900; // Moodle 4.3+ (tool_mfa no core)
$plugin->component = 'factor_geoip';
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '1.1.0';

// Depende do framework tool_mfa (https://moodle.org/plugins/tool_mfa).
$plugin->dependencies = [
    'tool_mfa' => 2019111800,
];

<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading(
        'factor_geoip/heading',
        get_string('pluginname', 'factor_geoip'),
        get_string('heading_desc', 'factor_geoip')
    ));

    // Caminho para o banco MaxMind GeoLite2-City.mmdb no servidor.
    // Baixe gratuitamente criando conta em https://www.maxmind.com/en/geolite2/signup
    $settings->add(new admin_setting_configfile(
        'factor_geoip/mmdbpath',
        get_string('mmdbpath', 'factor_geoip'),
        get_string('mmdbpath_desc', 'factor_geoip'),
        ''
    ));

    // Distância (km) a partir da qual o login é considerado "muito distante".
    $settings->add(new admin_setting_configtext(
        'factor_geoip/distancekm',
        get_string('distancekm', 'factor_geoip'),
        get_string('distancekm_desc', 'factor_geoip'),
        500,
        PARAM_INT
    ));

    // Validade do código enviado por e-mail, em minutos.
    $settings->add(new admin_setting_configtext(
        'factor_geoip/codeexpiry',
        get_string('codeexpiry', 'factor_geoip'),
        get_string('codeexpiry_desc', 'factor_geoip'),
        10,
        PARAM_INT
    ));

    // Tentativas máximas antes de invalidar o código.
    $settings->add(new admin_setting_configtext(
        'factor_geoip/maxattempts',
        get_string('maxattempts', 'factor_geoip'),
        get_string('maxattempts_desc', 'factor_geoip'),
        5,
        PARAM_INT
    ));

    // Se marcado, o primeiro login de um usuário (sem localização confiável ainda)
    // já exige verificação por e-mail. Se desmarcado, o primeiro login apenas
    // grava a localização como confiável, sem exigir código.
    $settings->add(new admin_setting_configcheckbox(
        'factor_geoip/challengefirstlogin',
        get_string('challengefirstlogin', 'factor_geoip'),
        get_string('challengefirstlogin_desc', 'factor_geoip'),
        0
    ));
}

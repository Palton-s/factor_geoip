<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

// O tool_mfa só inclui este arquivo quando $adminroot->fulltree é verdadeiro
// (e $ADMIN não existe neste escopo), então não há checagem de fulltree aqui.
$settings->add(new admin_setting_heading(
    'factor_geoip/heading',
    get_string('pluginname', 'factor_geoip'),
    get_string('heading_desc', 'factor_geoip')
));

// Configurações padrão exigidas por todo fator do tool_mfa.
$enabled = new admin_setting_configcheckbox('factor_geoip/enabled',
    new lang_string('settings:enablefactor', 'tool_mfa'),
    new lang_string('settings:enablefactor_help', 'tool_mfa'), 0);
$enabled->set_updatedcallback(function () {
    \tool_mfa\manager::do_factor_action('geoip', get_config('factor_geoip', 'enabled') ? 'enable' : 'disable');
});
$settings->add($enabled);

$settings->add(new admin_setting_configtext('factor_geoip/weight',
    new lang_string('settings:weight', 'tool_mfa'),
    new lang_string('settings:weight_help', 'tool_mfa'), 100, PARAM_INT));

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

// Se marcado, logins de um navegador/dispositivo ainda não verificado pelo
// usuário também exigem o código por e-mail.
$settings->add(new admin_setting_configcheckbox(
    'factor_geoip/checkdevice',
    get_string('checkdevice', 'factor_geoip'),
    get_string('checkdevice_desc', 'factor_geoip'),
    1
));

// Dias sem uso até um dispositivo confiável precisar ser verificado de novo.
$settings->add(new admin_setting_configtext(
    'factor_geoip/deviceexpiry',
    get_string('deviceexpiry', 'factor_geoip'),
    get_string('deviceexpiry_desc', 'factor_geoip'),
    90,
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

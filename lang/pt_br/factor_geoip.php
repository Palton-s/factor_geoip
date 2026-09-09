<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'MFA adaptativo por geolocalização de IP';
$string['heading_desc'] = 'Exige um código enviado por e-mail quando o login acontece a partir de uma localização muito distante da última localização confiável do usuário.';

$string['mmdbpath'] = 'Caminho do arquivo GeoLite2-City.mmdb';
$string['mmdbpath_desc'] = 'Caminho absoluto, no servidor, para a base de geolocalização MaxMind GeoLite2-City. Baixe gratuitamente em maxmind.com/en/geolite2/signup.';

$string['distancekm'] = 'Distância limite (km)';
$string['distancekm_desc'] = 'Se o login atual estiver a mais que esta distância (em km) da última localização confiável, o código extra é exigido.';

$string['codeexpiry'] = 'Validade do código (minutos)';
$string['codeexpiry_desc'] = 'Tempo, em minutos, até o código enviado por e-mail expirar.';

$string['maxattempts'] = 'Tentativas máximas';
$string['maxattempts_desc'] = 'Número de tentativas incorretas permitidas antes de invalidar o código.';

$string['challengefirstlogin'] = 'Exigir código no primeiro login';
$string['challengefirstlogin_desc'] = 'Se marcado, o primeiro login de cada usuário (sem localização confiável salva ainda) já exige o código por e-mail. Se desmarcado, o primeiro login apenas grava a localização como confiável.';

$string['verificationcode'] = 'Código de verificação';
$string['checkemail_desc'] = 'Detectamos um login a partir de uma localização incomum. Enviamos um código de verificação para o seu e-mail cadastrado.';
$string['error:invalidcode'] = 'Código inválido ou expirado.';
$string['unknownlocation'] = 'localização desconhecida';
$string['summarycondition'] = 'quando o login vier de uma localização muito distante do habitual';

$string['email_subject'] = 'Código de verificação de login - {$a}';
$string['email_body'] = 'Olá {$a->fullname},

Detectamos um login na sua conta a partir de uma localização incomum:

IP: {$a->ip}
Localização aproximada: {$a->location}

Se foi você, use o código abaixo para continuar (válido por {$a->minutes} minutos):

{$a->code}

Se você não reconhece este acesso, ignore este e-mail e considere trocar sua senha.';

$string['privacy:metadata:factor_geoip_trusted'] = 'Última localização de login confiável de cada usuário.';
$string['privacy:metadata:factor_geoip_trusted:userid'] = 'ID do usuário.';
$string['privacy:metadata:factor_geoip_trusted:ip'] = 'IP de origem do login confiável.';
$string['privacy:metadata:factor_geoip_trusted:latitude'] = 'Latitude aproximada do IP.';
$string['privacy:metadata:factor_geoip_trusted:longitude'] = 'Longitude aproximada do IP.';
$string['privacy:metadata:factor_geoip_trusted:country'] = 'País aproximado do IP.';
$string['privacy:metadata:factor_geoip_trusted:city'] = 'Cidade aproximada do IP.';
$string['privacy:metadata:factor_geoip_trusted:timecreated'] = 'Quando a localização foi registrada.';
$string['privacy:metadata:factor_geoip_codes'] = 'Códigos de verificação por e-mail emitidos para logins de localizações incomuns.';
$string['cleanuptask'] = 'Limpeza de códigos de verificação expirados (MFA geoip)';

<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'MFA adaptativo por geolocalização de IP';
$string['heading_desc'] = 'Exige um código enviado por e-mail quando o login apresenta algum sinal de risco: dispositivo ou navegador novo, localização distante, mudança de país, viagem impossível, VPN/proxy/Tor, senhas erradas ou horário incomum.';

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

$string['info'] = 'Pede um código enviado por e-mail somente quando o login apresenta algum sinal de risco.';
$string['logintitle'] = 'Confirme que é você';
$string['logindesc'] = 'Digite o código de verificação enviado para o seu e-mail.';
$string['loginoption'] = 'Receber um código por e-mail';
$string['loginsubmit'] = 'Verificar código';
$string['verificationcode'] = 'Código de verificação';
$string['checkemail_desc'] = 'Precisamos confirmar este acesso ({$a}). Enviamos um código de verificação para o seu e-mail cadastrado.';
$string['error:invalidcode'] = 'Código inválido ou expirado.';
$string['unknownlocation'] = 'localização desconhecida';
$string['summarycondition'] = 'quando o login apresentar algum sinal de risco (dispositivo novo, local ou horário incomum, VPN/Tor, senhas erradas)';

$string['email_subject'] = 'Código de verificação de login - {$a}';
$string['email_body'] = 'Olá {$a->fullname},

Detectamos um login na sua conta que precisa de confirmação.

Motivo: {$a->reason}

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
$string['checkdevice'] = 'Verificar dispositivo';
$string['checkdevice_desc'] = 'Se marcado, logins a partir de um navegador/dispositivo que o usuário ainda não verificou também exigem o código por e-mail. O primeiro dispositivo de cada usuário é confiado automaticamente, a menos que "Exigir código no primeiro login" esteja marcado.';
$string['deviceexpiry'] = 'Validade do dispositivo confiável (dias)';
$string['deviceexpiry_desc'] = 'Um dispositivo confiável que ficar este número de dias sem uso precisa ser verificado novamente.';
$string['reason_newdevice'] = 'acesso a partir de um dispositivo novo';
$string['reason_distantlocation'] = 'acesso a partir de uma localização incomum';
$string['privacy:metadata:factor_geoip_devices'] = 'Navegadores/dispositivos que o usuário verificou como confiáveis.';
$string['privacy:metadata:factor_geoip_devices:tokenhash'] = 'Hash do token aleatório guardado no cookie do dispositivo.';
$string['privacy:metadata:factor_geoip_devices:useragent'] = 'User agent do navegador quando o dispositivo foi confiado.';
$string['privacy:metadata:factor_geoip_devices:timelastused'] = 'Quando o dispositivo foi usado pela última vez.';
$string['privacy:metadata:factor_geoip_lastseen'] = 'Localização do login mais recente de cada usuário, usada para detectar viagem impossível.';
$string['privacy:metadata:factor_geoip_lastseen:timeseen'] = 'Quando o login foi visto.';

$string['checkuseragent'] = 'Verificar mudança de navegador/SO';
$string['checkuseragent_desc'] = 'Se marcado, um dispositivo confiável que passar a informar outro navegador ou sistema operacional (versões são ignoradas) exige o código por e-mail.';

$string['heading_location'] = 'País e viagem impossível';
$string['checkcountry'] = 'Verificar mudança de país';
$string['checkcountry_desc'] = 'Se marcado, um login de um país diferente do país da localização confiável exige o código, independentemente da distância.';
$string['checktravel'] = 'Verificar viagem impossível';
$string['checktravel_desc'] = 'Se marcado, exige o código quando a distância até o login anterior dividida pelo tempo decorrido passar da velocidade máxima. Distâncias abaixo de 100 km são ignoradas (imprecisão do GeoIP).';
$string['travelmaxspeed'] = 'Velocidade máxima de deslocamento (km/h)';
$string['travelmaxspeed_desc'] = 'Velocidades acima desta são consideradas impossíveis. 900 km/h é aproximadamente a velocidade de um avião comercial.';

$string['heading_anonymous'] = 'VPN, proxy e Tor';
$string['checkanonymous'] = 'Verificar VPN/proxy/Tor';
$string['checkanonymous_desc'] = 'Se marcado, logins a partir de nós de saída do Tor (lista oficial, atualizada a cada 6 horas por uma tarefa agendada) e, se configurada, de IPs marcados pela base GeoIP2 Anonymous IP exigem o código.';
$string['anonmmdbpath'] = 'Caminho do arquivo GeoIP2-Anonymous-IP.mmdb (opcional)';
$string['anonmmdbpath_desc'] = 'Caminho absoluto, no servidor, para a base MaxMind GeoIP2 Anonymous IP (paga). Necessária para detectar VPNs comerciais e proxies; sem ela, só o Tor é detectado.';
$string['updatetortask'] = 'Atualizar lista de nós de saída do Tor (MFA geoip)';
$string['error:torupdate'] = 'Não foi possível atualizar a lista de nós de saída do Tor: {$a}';

$string['heading_failedlogins'] = 'Senhas erradas';
$string['checkfailedlogins'] = 'Verificar senhas erradas';
$string['checkfailedlogins_desc'] = 'Se marcado, exige o código quando a senha foi digitada errada várias vezes desde o último login bem-sucedido do usuário.';
$string['failedloginsthreshold'] = 'Limite de senhas erradas';
$string['failedloginsthreshold_desc'] = 'Número de senhas erradas desde o último login bem-sucedido que dispara o código.';

$string['heading_time'] = 'Horário incomum';
$string['checkhours'] = 'Verificar horário incomum';
$string['checkhours_desc'] = 'Se marcado, logins dentro da janela de horário ou nos dias abaixo (no fuso horário do usuário) exigem o código.';
$string['unusualhourstart'] = 'Início da janela incomum';
$string['unusualhourstart_desc'] = 'Início da janela de horário incomum (inclusive).';
$string['unusualhourend'] = 'Fim da janela incomum';
$string['unusualhourend_desc'] = 'Fim da janela de horário incomum (exclusive). A janela pode passar da meia-noite (ex.: 22:00 a 06:00). Início igual ao fim desativa a janela.';
$string['unusualdays'] = 'Dias incomuns';
$string['unusualdays_desc'] = 'Logins nos dias da semana marcados sempre exigem o código.';

$string['reason_countrychange'] = 'acesso a partir de outro país';
$string['reason_impossibletravel'] = 'deslocamento impossível desde o login anterior';
$string['reason_anonymousip'] = 'acesso a partir de VPN, proxy ou Tor';
$string['reason_failedlogins'] = 'várias senhas erradas antes deste acesso';
$string['reason_unusualtime'] = 'acesso em horário incomum';
$string['reason_useragentchange'] = 'navegador ou sistema operacional diferente';

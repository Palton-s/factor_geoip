# factor_geoip — MFA adaptativo por geolocalização de IP (Moodle)

Fator adicional para o plugin **[tool_mfa](https://moodle.org/plugins/tool_mfa)**
(Multi-Factor Authentication for Moodle). Ele **não substitui** o tool_mfa —
é um fator novo que se registra nele.

## Como funciona

1. Usuário faz login.
2. O fator pega o IP (`getremoteaddr()`) e resolve a localização aproximada
   usando uma base **MaxMind GeoLite2-City** local (arquivo `.mmdb`).
3. Compara com a última localização "confiável" salva na tabela
   `factor_geoip_trusted`.
4. Se a distância (Haversine) ultrapassar o limiar configurado
   (padrão: 500 km), o Moodle exige um passo extra: um código de 6 dígitos
   é enviado por e-mail via `email_to_user()` (o mecanismo de e-mail nativo
   do Moodle — não precisa configurar SMTP separado, usa o que o site já
   tiver).
5. Se o código bater, o login prossegue e a nova localização vira a
   "confiável" para a próxima vez.
6. Se o login for de uma localização "normal", o fator passa direto (sem
   fricção nenhuma para o usuário).

## Instalação

1. Instale primeiro o **tool_mfa**:
   `admin/tool/mfa` — https://moodle.org/plugins/tool_mfa
2. Copie esta pasta para `admin/tool/mfa/factor/geoip/` na sua instalação
   Moodle (o nome da pasta deve ser `geoip`).
3. Baixe a base MaxMind GeoLite2-City (grátis, precisa de conta):
   https://www.maxmind.com/en/geolite2/signup
   Salve o arquivo `GeoLite2-City.mmdb` em local legível pelo processo PHP
   do Moodle (fora do webroot público).
4. Instale a biblioteca leitora do formato `.mmdb` via Composer, dentro da
   pasta do plugin:
   ```
   cd admin/tool/mfa/factor/geoip
   composer require geoip2/geoip2
   ```
   (isso cria `vendor/`, já referenciado pelo `geolocator.php`)
5. Acesse **Administração do site → Plugins → Verificar atualizações de
   plugins** para instalar o fator (roda `db/install.xml`).
6. Vá em **Administração do site → Segurança → MFA** e:
   - Habilite o fator "MFA adaptativo por geolocalização de IP".
   - Configure o caminho do `.mmdb`, a distância limite e a validade do
     código em **Plugins → Fatores de autenticação → factor_geoip**.
7. **Configure um peso** para o fator na tela de fatores do tool_mfa: como
   ele é reativo (só bloqueia quando detecta distância), normalmente você
   quer que ele sozinho já complete os 100% exigidos quando disparar —
   ajuste o peso conforme os outros fatores ativos no seu site.

## Importante — pontos de atenção antes de ir para produção

- **Atrás de proxy/load balancer/Cloudflare**: configure
  `$CFG->getremoteaddrconf` no `config.php` do Moodle para o Moodle ler o
  IP real do `X-Forwarded-For`/`CF-Connecting-IP` corretamente — senão
  todo login vai parecer vir do IP do proxy.
- **Atualize a base GeoLite2** periodicamente (a MaxMind libera novas
  versões toda semana); crie uma tarefa cron própria de download, ou use
  `geoipupdate` (ferramenta oficial da MaxMind) num cron do servidor.
- **Falso positivo com VPN corporativa**: usuários que sempre entram via
  VPN vão "aprender" a localização da VPN como confiável, o que é o
  comportamento esperado.
- **API da classe base (`object_factor_base`)** pode variar entre versões
  do tool_mfa — confira contra a versão instalada no seu site
  (`admin/tool/mfa/classes/local/factor/object_factor_base.php`) e ajuste
  as assinaturas de método em `classes/factor.php` se necessário.
- Este código é um **scaffold funcional**, não testado em uma instância
  Moodle real ainda — rode os testes manuais de login normal / login
  distante / código errado / código expirado antes de publicar.

## Estrutura

```
factor_geoip/
├── classes/
│   ├── factor.php            # Integração com tool_mfa (get_state, formulário, etc.)
│   ├── geolocator.php         # Consulta MaxMind + cálculo de distância (Haversine)
│   ├── code_manager.php       # Gera/envia/valida o código por e-mail
│   ├── privacy/provider.php   # GDPR/LGPD — export e delete de dados pessoais
│   └── task/cleanup_task.php  # Cron: apaga códigos expirados antigos
├── db/
│   ├── install.xml            # Tabelas: factor_geoip_trusted, factor_geoip_codes
│   └── tasks.php               # Agenda o cleanup_task
├── lang/{en,pt_br}/factor_geoip.php
├── settings.php                # Tela de configuração do admin
└── version.php
```

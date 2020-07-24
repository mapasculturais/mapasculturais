


## themes.active

Define o tema ativo no site principal. Deve ser informado o namespace do tema e neste deve existir uma classe Theme.

ex: `Name\Space` (deve existir a classe `\Name\Space\Theme`)


 - definível pela variável de ambiente **ACTIVE_THEME**
 - o valor padrão é `'MapasCulturais\Themes\BaseV1'`
 - definido em `protected/application/conf/conf-base.d/0.main.php:10`


## app.siteName
Nome do site. É utilizado para a formação dos títulos das páginas.

 - definível pela variável de ambiente **SITE_NAME**
 - o valor padrão é `i::__('Mapas Culturais')`
 - definido em `protected/application/conf/conf-base.d/0.main.php:13`


## app.siteDescription
Breve descrição do site. É utilizado como texto de compartilhamento da página principal do site.

 - definível pela variável de ambiente **SITE_DESCRIPTION**
 - o valor padrão é `i::__('O Mapas Culturais é uma plataforma livre para mapeamento cultural.')`
 - definido em `protected/application/conf/conf-base.d/0.main.php:16`


## app.lcode

Define a linguagem a ser utilizada.
É possível definir mais de um valor e desta forma a linguagem será escolhida baseado na configuração do navegador do usuário

ex: `pt_BR,es_ES` ou `es_ES`


 - definível pela variável de ambiente **APP_LCODE**
 - o valor padrão é `'pt_BR'`
 - definido em `protected/application/conf/conf-base.d/0.main.php:27`


## app.mode

Define o modo em que a aplicação está rodando.
Os valores possíveis são as constantes `APPMODE_PRODUCTION`, `APPMODE_STAGING` e `APPMODE_DEVELOPMENT`.


 - definível pela variável de ambiente **APP_MODE**
 - o valor padrão é `APPMODE_PRODUCTION`
 - definido em `protected/application/conf/conf-base.d/0.main.php:33`


## doctrine.isDev

Define se o Doctrine está rodando em modo de desenvolvimento, o que basicamente evita o cacheamento dos metadados do mapeamento do ORM.
[Mais informações na documentação do Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/advanced-configuration.html#development-vs-production-configuration)

Por padrão o valor é verdadeiro quando o app.mode for `APPMODE_DEVELOPMENT`.


 - definível pela variável de ambiente **DOCTRINE_ISDEV**
 - o valor padrão é `env('APP_MODE', APPMODE_PRODUCTION) == APPMODE_DEVELOPMENT`
 - definido em `protected/application/conf/conf-base.d/0.main.php:41`


## slim.debug

Define se o Slim Framework está rodando em modo debug, o que faz o slim exibir os erros na tela.

Por padrão o valor é verdadeiro quando a aplicação não estiver rodando no modo `APPMODE_PRODUCTION`.


 - definível pela variável de ambiente **SLIM_DEBUG**
 - o valor padrão é `env('APP_MODE', APPMODE_PRODUCTION) != APPMODE_PRODUCTION`
 - definido em `protected/application/conf/conf-base.d/0.main.php:48`


## api.accessControlAllowOrigin
Valor do header Access-Control-Allow-Origin da api de leitura.

 - definível pela variável de ambiente **API_ACCESS_CONTROL_ALLOW_ORIGIN**
 - o valor padrão é `'*'`
 - definido em `protected/application/conf/conf-base.d/0.main.php:58`


## app.cache.namespace

 - definível pela variável de ambiente **CACHE_NAMESPACE**
 - o valor padrão é `@$_SERVER['HTTP_HOST']`
 - definido em `protected/application/conf/conf-base.d/cache.php:7`


## app.registeredAutoloadCache.lifetime

 - definível pela variável de ambiente **CACHE_AUTOLOAD**
 - definido em `protected/application/conf/conf-base.d/cache.php:19`


## app.assetsUrlCache.lifetime

 - definível pela variável de ambiente **CACHE_ASSETS_URL**
 - definido em `protected/application/conf/conf-base.d/cache.php:20`


## app.fileUrlCache.lifetime

 - definível pela variável de ambiente **CACHE_FILE_URL**
 - o valor padrão é `604800`
 - definido em `protected/application/conf/conf-base.d/cache.php:21`


## app.eventsCache.lifetime

 - definível pela variável de ambiente **CACHE_EVENTS**
 - o valor padrão é `600`
 - definido em `protected/application/conf/conf-base.d/cache.php:22`


## app.subsiteIdsCache.lifetime

 - definível pela variável de ambiente **CACHE_SUBSITE_ID**
 - o valor padrão é `120`
 - definido em `protected/application/conf/conf-base.d/cache.php:23`


## app.permissionsCache.lifetime

 - definível pela variável de ambiente **CACHE_PERMISSIONS**
 - o valor padrão é `120`
 - definido em `protected/application/conf/conf-base.d/cache.php:24`


## app.registerCache.lifeTime

 - definível pela variável de ambiente **CACHE_REGISTER**
 - o valor padrão é `600`
 - definido em `protected/application/conf/conf-base.d/cache.php:25`


## app.apiCache.lifetime

 - definível pela variável de ambiente **CACHE_API**
 - o valor padrão é `120`
 - definido em `protected/application/conf/conf-base.d/cache.php:26`


## app.apiCache.lifetimeByController => notification

 - definível pela variável de ambiente **CACHE_API_NOTIFICATION**
 - definido em `protected/application/conf/conf-base.d/cache.php:29`


## app.apiCache.lifetimeByController => event

 - definível pela variável de ambiente **CACHE_API_EVENT**
 - o valor padrão é `25`
 - definido em `protected/application/conf/conf-base.d/cache.php:30`


## cep.endpoint

 - definível pela variável de ambiente **CEP_ENDPOINT**
 - o valor padrão é `'http://www.cepaberto.com/api/v2/ceps.json?cep=%s'`
 - definido em `protected/application/conf/conf-base.d/cep.php:5`


## cep.token_header

 - definível pela variável de ambiente **CEP_TOKEN_HEADER**
 - o valor padrão é `'Authorization: Token token="%s"'`
 - definido em `protected/application/conf/conf-base.d/cep.php:6`


## cep.token

 - definível pela variável de ambiente **CEP_TOKEN**
 - o valor padrão é `''`
 - definido em `protected/application/conf/conf-base.d/cep.php:7`


## doctrine.database => host

 - definível pela variável de ambiente **DB_HOST**
 - o valor padrão é `'db'`
 - definido em `protected/application/conf/conf-base.d/db.php:5`


## doctrine.database => dbname

 - definível pela variável de ambiente **DB_NAME**
 - o valor padrão é `'mapas'`
 - definido em `protected/application/conf/conf-base.d/db.php:6`


## doctrine.database => user

 - definível pela variável de ambiente **DB_USER**
 - o valor padrão é `'mapas'`
 - definido em `protected/application/conf/conf-base.d/db.php:7`


## doctrine.database => password

 - definível pela variável de ambiente **DB_PASS**
 - o valor padrão é `'mapas'`
 - definido em `protected/application/conf/conf-base.d/db.php:8`


## doctrine.database => server_version

 - definível pela variável de ambiente **DB_VERSION**
 - o valor padrão é `10`
 - definido em `protected/application/conf/conf-base.d/db.php:9`


## slim.log.enabled

 - definível pela variável de ambiente **LOG_ENABLED**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:39`


## app.log.path

 - definível pela variável de ambiente **LOG_PATH**
 - o valor padrão é `realpath(BASE_PATH . '..') . '/logs/'`
 - definido em `protected/application/conf/conf-base.d/logs.php:40`


## app.log.query

 - definível pela variável de ambiente **LOG_QUERY**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:41`


## app.log.hook

 - definível pela variável de ambiente **LOG_HOOK**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:42`


## app.log.requestData

 - definível pela variável de ambiente **LOG_REQUESTDATA**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:43`


## app.log.translations

 - definível pela variável de ambiente **LOG_TRANSLATIONS**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:44`


## app.log.apiCache

 - definível pela variável de ambiente **LOG_APICACHE**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:45`


## app.log.apiDql

 - definível pela variável de ambiente **LOG_APIDQL**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/logs.php:46`


## mailer.user

 - definível pela variável de ambiente **MAILER_USER**
 - o valor padrão é `"admin@mapasculturais.org"`
 - definido em `protected/application/conf/conf-base.d/mailer.php:5`


## mailer.psw

 - definível pela variável de ambiente **MAILER_PASS**
 - o valor padrão é `"password"`
 - definido em `protected/application/conf/conf-base.d/mailer.php:6`


## mailer.protocol

 - definível pela variável de ambiente **MAILER_PROTOCOL**
 - o valor padrão é `'ssl'`
 - definido em `protected/application/conf/conf-base.d/mailer.php:7`


## mailer.server

 - definível pela variável de ambiente **MAILER_SERVER**
 - o valor padrão é `'localhost'`
 - definido em `protected/application/conf/conf-base.d/mailer.php:8`


## mailer.port

 - definível pela variável de ambiente **MAILER_PORT**
 - o valor padrão é `'465'`
 - definido em `protected/application/conf/conf-base.d/mailer.php:9`


## mailer.from

 - definível pela variável de ambiente **MAILER_FROM**
 - o valor padrão é `'suporte@mapasculturais.org'`
 - definido em `protected/application/conf/conf-base.d/mailer.php:10`


## mailer.alwaysTo

 - definível pela variável de ambiente **MAILER_ALWAYSTO**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/mailer.php:11`


## maps.includeGoogleLayers

 - definível pela variável de ambiente **MAPS_USE_GOOGLE_LAYERS**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/maps.php:4`


## app.useGoogleGeocode

 - definível pela variável de ambiente **MAPS_USE_GOOGLE_GEOCODE**
 - o valor padrão é `false`
 - definido em `protected/application/conf/conf-base.d/maps.php:5`


## app.googleApiKey

 - definível pela variável de ambiente **MAPS_GOOGLE_API_KEY**
 - o valor padrão é `''`
 - definido em `protected/application/conf/conf-base.d/maps.php:6`
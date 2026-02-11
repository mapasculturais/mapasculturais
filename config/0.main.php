<?php 
use \MapasCulturais\i;
// $_ENV['APP_MODE'] = 'development';

return [
    /*
    Define o tema ativo no site principal. Deve ser informado o namespace do tema e neste deve existir uma classe Theme.

    ex: `Name\Space` (deve existir a classe `\Name\Space\Theme`)
    */
    'themes.active' => env('ACTIVE_THEME', 'redemapas'),

    
    /*
    Define a url do site

    ex: `https://mapacultural.com.br/`
    */
    'base.url' => env('BASE_URL', ''),

    /* Nome do site. É utilizado para a formação dos títulos das páginas. */
    'app.siteName' => env('SITE_NAME', i::__('Mapas Culturais')),

    /* Breve descrição do site. É utilizado como texto de compartilhamento da página principal do site. */
    'app.siteDescription' => env('SITE_DESCRIPTION', i::__('O Mapas Culturais é uma plataforma livre para mapeamento cultural.')),
    
    /* Ids dos selos verificadores. Para utilizar múltiplos selos informe os ids separados por vírgula. */
    'app.verifiedSealsIds' => explode(',', env('VERIFIED_SEALS', '1')),
    
    /* 
    Define a linguagem a ser utilizada. 
    É possível definir mais de um valor e desta forma a linguagem será escolhida baseado na configuração do navegador do usuário

    ex: `pt_BR,es_ES` ou `es_ES` 
    */
    'app.lcode' => env('APP_LCODE', 'pt_BR'),

    /* 
    Define o valor padrão para o metadado En_pais
    
    ex: `BR` ou `AR` 
    */
    'app.defaultCountry' => env('APP_DEFAULT_COUNTRY', "BR"),
    
    /* 
    Define o modo em que a aplicação está rodando. 
    Os valores possíveis são as constantes `APPMODE_PRODUCTION`, `APPMODE_STAGING` e `APPMODE_DEVELOPMENT`.
    */
    'app.mode' => env('APP_MODE', APPMODE_PRODUCTION),

    'app.executeJobsImmediately' => env('APP_EXECUTE_JOBS_IMMEDIATELY', false),
    'app.recreateCacheImmediately' => env('APP_RECREATE_CACHE_IMMEDIATELY', false),
    

    /* 
    Define a moeda a ser utilizada. 
    É possível definir mais de uma moeda e desta forma a moeda será escolhida com base na configuração do navegador do usuário.

    Exemplo: 'BRL,EUR' ou 'EUR' 
    */
    'app.currency' => env('APP_CURRENCY', 'BRL'),

    /* 
    Define se o Doctrine está rodando em modo de desenvolvimento, o que basicamente evita o cacheamento dos metadados do mapeamento do ORM.
    [Mais informações na documentação do Doctrine](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/advanced-configuration.html#development-vs-production-configuration)

    Por padrão o valor é verdadeiro quando o app.mode for `APPMODE_DEVELOPMENT`.
    */
    'doctrine.isDev' => env('DOCTRINE_ISDEV', env('APP_MODE', APPMODE_PRODUCTION) == APPMODE_DEVELOPMENT),
    
    /* Valor do header Access-Control-Allow-Origin da api de leitura. */
    'api.accessControlAllowOrigin' => env('API_ACCESS_CONTROL_ALLOW_ORIGIN', '*'),

    /** função para sanitizar o nome do arquivo */
    'app.sanitize_filename_function' => function($filename) {
        return $filename;
    },

    /* Define quantidade de memória utilizada para exportação dos dados */
    'app.export.memoryLimit' => env('EXPORT_MEMORY_LIMIT', '4096M'),

    /*
    Define valores de inicialização do PHP para rotas específicas
    
    para usar como variável de ambiente deve ser passado um json 
    ex: [{"API agent/find":{"memory_limit":"1024M"}}]

    configurando via PHP:
    ex:
    ```
    [
        'API agent/find' => [
            'memory_limit' => '1024M',
            'max_execution_time' => -1
        ],
        'API *' => [
            'memory_limit' => '256M',
            'max_execution_time' => -1
        ],
    ]
    ```
    */
    'ini.set' => json_decode(env('PHP_INI_SET', '[]')),

    /*Define mensagem padrão :
        Ex: 'Precisa de ajuda? Clique para falar com nossa equipe de suporte por chat. Ou envie um email para ',
        
    */
    'footer.supportMessage' => '',

    /* Define se o usuário será redirecionado para a edição do perfil caso o perfil não esteja validado */
    'app.redirect_profile_validate' => env('APP_REDIRECT_PROFILE_VALIDATE', false),
    
    /* Lista de MIME types bloqueados */
    'app.not_allowed_mime_types' => env('APP_NOT_ALLOWED_MIME_TYPES', "html|php|javascript|css|executable|msdownload|bat|cmd|installer|bash|diskimage|android|java|octet-stream"),

];

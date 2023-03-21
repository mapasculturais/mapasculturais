> As alterações sugeridas aqui devem ser processadas no arquivos de configuração principal da aplicação presente em src/protected/application/conf/config.php

## Plugins e Módulos

A definição de novos plugins no sistema Mapas Culturais mudou estruturalmente, funcionando na estrutura abaixo:

```
'plugins' => array(
  'ProjectPhases' => ['namespace' => 'ProjectPhases'],
  'AgendaSingles' => ['namespace' => 'AgendaSingles']
)
```
### Site de Origem
Este plugin efetua o registro do site de origem de todas entidades da instalação Mapas Culturais quando inseridas ou atualizadas. É criado um metadado na entidade chamado ```site``` e o conteúdo informado na configuração é gravado na tabela de metadados da entidade gravada. A configuração é feita da seguinte forma:

```
'plugins' => array(
  'OriginSite' => [
                  'namespace' => 'OriginSite',
                  'config'    => ['siteId' => 'http://url.da.instalacao.com.br']
  ]
);
```

Ao final, deve ser assim:

```
'plugins.enabled' => array('endereco', 'notifications', 'em-cartaz'),
'plugins' => array(
  'ProjectPhases' => ['namespace' => 'ProjectPhases'],
     'AgendaSingles' => ['namespace' => 'AgendaSingles'],
     'OriginSite' => [
        'namespace' => 'OriginSite',
        'config'    => ['siteId' => 'url.da.instalacao.com.br']
     ]
),
```
### Sufixo da URL do domínio
Esse plugin é para configurar a exibição do domínio do campo de url de domínio no formulário do subsites do Saas quando todas as instalações pertencem ao mesmo domínio. A configuração abaixo deve ser incluída no array de plugins da instalação com o formato da exibição do sufixo da url do domínio:

```
'plugins' => array(
  'SubsiteDomainSufix' => [
    'namespace' => 'SubsiteDomainSufix',
    'config' => [
      'sufix' => function () {
        return 'domain-sufix.gov.br';
      }
    ]
  ]
)
```

### Notificações
As notificações têm o propósito de comunicar ou solicitar aprovação de relacionamento entre entidades no sistema.
Para habilitar as notificações do sistema, é necessário acrescentar no array de plugins da configuração da instalação:

```
'plugins' => array("notifications");
```

É possível configurar o tempo de intervalo de verificação de novas notificações informando o período em segundos na diretiva abixo na configuração da instalação:

```
'notifications.interval' => 60,  // seconds
```

Existem as notificações ao usuário após longo período de ausência de acesso ao sistema pelo usuário, que é determinado em quantidade de dias que o usuário será notificado e solicitando atualização de suas informações caso necessário:
```
'notifications.user.access'     => 90,  // days
```
E as notificações de entidades sem atualização, definido em dias:
```
'notifications.entities.update' => 90,  // days
```
**Observação**: Para deixar desabilitadas as notificações de usuário e entidades sem atualização, é só deixar suas diretivas de definição de dias para notificar com o valor 0 (zero).

Habilita a notificação ao usuário por notificação e email o número de dias configurado antes da data de expiração do selo atribuído a algum registro de entidade (Agente/Espaço/Evento):
```
'notifications.seals.toExpire' => 90,  // days
```
**Observação**: Esta configuração funciona em conjunto com os plugins de [Selos Certificadores](https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_seal.md) e [Mailer](https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#mailer)

### Mailer
Assim como as notificações no sistema, o Mailer tem o propósito de comunicar sobre ocorrências no Mapas enviando e-mails para o usuário do sistema, independente se os usuários acessam ou não a plataforma.
Para habilitar os emails do sistema, é necessário acrescentar no array de plugins da configuração da instalação:

```
'plugins.enabled' => array("mailer");
```

Em SO unix like, é utilizado o serviço do sendmail para que o envio de e-mails aconteça, e serão necessárias algumas informações para que a autenticação de e-mail aconteça o envio das mensagens:

#### Usuário que será utilizado para autenticação no servidor de e-mail:
```
'mailer.user' => "admin@mapasculturais.org"
```
#### Senha de usuário para autenticação no servidor de e-mail:
```
'mailer.psw'  => "password"
```
#### Protocolo que será utilizado em conexão criptografada:
```
'mailer.protocol' => 'ssl'
```
#### URL do servidor de envio de e-mail:
```
'mailer.server' => 'smtp.gmail.com'
```
#### Qual porta será utilizada para efetuar a conexão:
```
'mailer.port'   => '465'
```
#### Qual será o e-mail de remetente:
```
'mailer.from' => 'suporte@mapasculturais.org'
```
Em SOs Unix like é possível ter o envio de mensagens avisando a necessidade de atualização de entidades configurando um script do Mailer no serviço Cron, configurando a execução da rotina da pasta scripts notifications.sh de acordo com a periodicidade desejada.

### Botão de Denúncia e Sugestão
Esse módulo é para configurar a funcionalidade de denúncia e/ou sugestões nos singles das entidades do Mapas Culturais. Habilita o botão *Denúncia* e *Contato* que serão exibidos na parte inferior direita do single da entidade:

```
'module.CompliantSuggestion' => [
    'compliant' => true,
    'suggestion' => true
],
,
```
As chaves `compliant` e `suggestion` recebe um valor *boolean* habilitando ou desabilitando o botão da funcionalidade.
**IMPORTANTE**: Esta funcionalidade trabalha em conjunto com o plugin [MAILER](https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#mailer)

### Templates
Os templates é uma forma de customizar o formato dos e-mails que são enviados pelo Mapas Culturais para cada situação existente de notificação por e-mail. Por padrão contempla os seguintes processos de envio de e-mail:

Esse plugin é para configurar a funcionalidade de denúncia e/ou sugestões nos singles das entidades do Mapas Culturais. Habilita o botão *Denúncia* e *Contato* que serão exibidos na parte inferior direita do single da entidade:
* Boas vindas ao Mapas
* Nova entidade cadastrada (Agente/Espaço/Projeto/Evento)
* Longo tempo sem acesso do usuário ao Mapas. Esta mensagem, o seu envio funciona em conjunto com o plugin [Notificações] (https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#notificações)
* Necessidade de atualização de registros do Mapas (Agente/Espaço/Projeto/Evento). Esta mensagem, o seu envio funciona em conjunto com o plugin [Notificações] (https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#notificações)
* Sugestão de conteúdo no sistema. Esta mensagem, o seu envio funciona em conjunto com o plugin [Botão de Denúncia e Sugestão] (https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#botão-de-denúncia-e-sugestão)
* Denúncia de conteúdo no sistema. Esta mensagem, o seu envio funciona em conjunto com o plugin [Botão de Denúncia e Sugestão] (https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#botão-de-denúncia-e-sugestão)

Todos os templates utilizados devem se criado utilizando a notação HTML e armazenados na pasta */src/protected/application/themes/BaseV1/templates*, e devem ser configurados no arquivo conf-base.php ou config.php da aplicação com as seguintes definições:
```
'mailer.templates' => [
        'welcome' => [
            'title' => "Bem-vindo(a) ao Mapas Culturais",
            'template' => 'welcome.html'
        ],
        'last_login' => [
            'title' => "Acesse a Mapas Culturais",
            'template' => 'last_login.html'
        ],
        'new' => [
            'title' => "Novo registro",
            'template' => 'new.html'
        ],
        'update_required' => [
            'title' => "Acesse a Mapas Culturais",
            'template' => 'update_required.html'
        ],
        'compliant' => [
            'title' => "Denúncia - Mapas Culturais",
            'template' => 'compliant.html'
        ],
        'suggestion' => [
            'title' => "Mensagem - Mapas Culturais",
            'template' => 'suggestion.html'
        ]
    ],
```
Os templates e e-mails sõ serão enviados se existir o template no array dentro do arquivo de configuração do Mapas. Para customizar templates por tema, é só criar a pasta _templates_ dentro da pasta do tema e incluir os arquivos de .html dos templates desejados.

**IMPORTANTE**: Esta funcionalidade trabalha em conjunto com o plugin [Mailer](https://github.com/hacklabr/mapasculturais/blob/master/documentation/docs/mc_config_plugins.md#mailer)

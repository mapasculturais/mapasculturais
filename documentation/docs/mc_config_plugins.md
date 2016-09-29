## Plugins

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
                        'config'    => ['siteId' => 'lugaresdacultura.org.br']
                                ]
        ),
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
Observação: Para deixar as desabilitadas as notificações de usuário e entidades sem atualização, é só deixar suas diretivas de definição de dias para notificar com o valor 0 (zero).



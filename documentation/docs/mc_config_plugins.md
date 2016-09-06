## Plugins

A definição de novos plugins no sistema Mapas Culturais mudou estruturalmente, funcionando na estrutura abaixo:

```
plugins => array(
  'ProjectPhases' => ['namespace' => 'ProjectPhases'],
  'AgendaSingles' => ['namespace' => 'AgendaSingles']
);
```

### Notificações
Para habilitar as notificações do sistema, é necessário acrescentar no array de plugins da configuração da instalação:

``
plugins => array("notifications");
``

## Plugins

A definição de novos plugins no sistema Mapas Culturais mudou estruturalmente deixando de ser a diretiva ```plugins.enableds => array('agenda.php','emcartaz.php')``` para a estrutura abaixo:

```
plugins => array(
  'ProjectPhases' => ['namespace' => 'ProjectPhases'],
  'AgendaSingles' => ['namespace' => 'AgendaSingles']
);
```

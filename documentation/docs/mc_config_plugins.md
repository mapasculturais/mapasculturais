## Plugins

A definição de novos plugins no sistema Mapas Culturais mudou estruturalmente deixando de ser a diretiva '''plugins.enableds => array('agenda.php','emcartaz.php')''' para a estrutura abaixo:

```
plugins => array(
  'ProjectPhases' => ['namespace' => 'ProjectPhases'],
  'AgendaSingles' => ['namespace' => 'AgendaSingles']
);
```

e sendo possível customizar plugins seguindo a estrutura de pastas na pasta do tema se necessário:

```
\plugins
  \AgendaSingles
                \assets
                        \js
                            agenda-singles.js
                        \css
                \layouts
                        \parts
                            agenda-singles.php
                            agenda-singles--content.php
                            agenda-singles--header.php
                            agenda-singles--tab.php
```

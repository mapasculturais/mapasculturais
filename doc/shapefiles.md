Padronização dos SHAPEFILEs para uso na plataforma Mapas Culturais
==================================================================
Os SHAPEFILEs devem, preferencialmente:

- Estar no sistema de coordenadas WGS84 (EPSG 4326), que é o sistem de coordenadas usado pelo Sistema de Posicionamento Global (GPS) e pelos Mapas Culturais. É possível converter de praticamente qualquer outro sistema de coordenadas para o WGS84usando o Quantum GIS;
- Consistir de um SHAPEFILE bidimensional por grupo de dados (bairros, distritos, regiões, municípios...), não múltiplos arquivos. É possível combinar múltiplos SHAPEFILEs em um usando o plugin MMQGIS do Quantum GIS e é possível converter de polyline e outros formatos vetoriais para o padrão de polígonos de duas dimensões usando o Quantum GIS;
- Conter legendas codificadas em UTF-8. Uma legenda por polígono;

Referência: http://suite.opengeo.org/opengeo-docs/dataadmin/pgGettingStarted/shp2pgsql.html

Utilizando os Shapefiles na aplicação
=====================================

Os shapefiles podem ser usados para duas finalidades

1. Como referência para gerar metadados para as entidades salvas
-------------------------------------------------------------

Cada vez que uma entidade é salva, a aplicação faz uma consulta na base de dados geográfica para saber dentro de quais polígonos aquela entidade está. Ao salvar um espaço, por exemplo, a aplicação pode automaticamente deduzir que ele está na subprefeitura da Sé, no município de São Paulo e no estado de São Paulo, e salvar esses três metadados diretamente na entidade.

Para isso, é preciso: 

- carregar os polígonos na tabela Geodivision
- editar o arquivo de configuração e adicionar a informação das divisões geográficas que serão usadas. 

Ex:
'app.geoDivisionsHierarchy' => array(
            'zona' => 'Zona',
            'subprefeitura' => 'Subprefeitura',
            'distrito' => 'Distrito'
        ),
        
  Onde 'zona', 'subprefeitura' e 'distrito' são os valores da coluna 'type' da tabela Geodivision. 
  
  Essa configuração criará, automaticamente, os metadados geoZona, geoSubprefeitura e geoDistrito.
  
2. Como camadas na visualização do mapa
---------------------------------------

Incluir documentação

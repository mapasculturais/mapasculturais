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
  
2. Como adicionar camadas na visualização do mapa
---------------------------------------

[necessita teste e aprimoramento de procedimento]

Na base de dados do mapas, há uma tabela geo_division com os seguintes campos: 

---------
id                - serial - PK
parente_id - integer
type            - character varying (32)
cod             - character varying (32)
name          - character varying (32)
geom          - geometry

---------

Os campos ID, name e geom são os mais importantes. 

------------------

**0. Tenha os arquivos de formas**
Você necessitará tem, se possível no ambiente em que deseja fazer a importação, os arquivos de formas (shapefiles). Eles devem vir em um diretório com os seguintes arquivos:

NOME-DO-ARQUIVO.shp
NOME-DO-ARQUIVO.shx
NOME-DO-ARQUIVO.dbf 


**1. Verifique se PostGis está respondendo requisições.**

Execute os seguintes comandos:
```
# su postgres
$ psql -U postgres -d mapas -c "SELECT postgis_version()"
```

Você verá uma mensagem como essa:

```
            postgis_version           
---------------------------------------
 2.1 USE_GEOS=1 USE_PROJ=1 USE_STATS=1
(1 row)
```

**2 - Converta os arquivos de formato shape (.shp) para formato base de dados (.sql)**

Exemplo de conversão:

```
shp2pgsql -W LATIN1 -s -I mapasculturais/shapefiles/BAIRRO_POP.shp BAIRRO-TEMPORARIO > bairro-shapefiles.sql
```
obs: é importante colocar um nome temporário para não sobreescrever alguma tabela da base.

**3 - Insira o arquivo `.sql` gerador em uma nova tabela.**

O arquivo .sql gerado tem comandos para criação de uma nova tabela com nome designado e inserção de registros. Rode esse comando para criar e inserir registros na tabela mapas:

```
$ psql -U mapas -d mapas -a -f /caminho/para/arquivo/bairro-shapefiles.sql
```

**4 - Popule a tabela geo_division na base do mapas**

Modelo de população de tabela:
```
 $ insert into geo_division (type, cod, name, geom) (select 'bairro', cod_docto_, nome_bairr, geom from "bairro-temporario");
```

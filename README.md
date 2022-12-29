[![Join the chat at https://t.me/joinchat/WCYOkiRbAWmxQM2y](https://patrolavia.github.io/telegram-badge/chat.png)](https://t.me/joinchat/WCYOkiRbAWmxQM2y)

# Mapas Culturais

Em julho de 2013, agentes culturais de vários países da América Latina e do Brasil se reuniram para discutir a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro surgiram as bases para a criação de Mapas Culturais, um software livre que permite o aprimoramento da gestão cultural dos municípios e estados.

Mapas Culturais é uma plataforma colaborativa que reúne informações sobre agentes, espaços, eventos e projetos culturais, fornecendo ao poder público uma radiografia da área de cultura e ao cidadão um mapa de espaços e eventos culturais da região. A plataforma está alinhada ao Sistema Nacional de Informação e Indicadores Culturais do Ministério da Cultura (SNIIC) e contribui para a realização de alguns dos objetivos do Plano Nacional de Cultura.

A plataforma já está em uso em diversos municipios, estados, no governo federal em diversos projetos do ministério da cultura e até mesmo fora do Brasil no Uruguai. Instalações recentes: 


### Instalações em nível federal ou internacional
- IberculturaViva - https://mapa.iberculturaviva.org/
- Mapa Uruguai - http://culturaenlinea.uy/
- SNIIC - http://mapas.cultura.gov.br/
- Museus - http://museus.cultura.gov.br/
- Sistema Nacional de Bibliotecas Públicas - http://bibliotecas.cultura.gov.br/
- Cultura Viva - http://culturaviva.gov.br/
- Pontos de Memória - http://pontosdememoria.cultura.gov.br/

### Instalações estaduais
- Distrito Federal - http://mapa.cultura.df.gov.br/
- Ceará - https://mapacultural.secult.ce.gov.br/
- Espírito Santo - https://mapa.cultura.es.gov.br/
- Goiás - https://mapagoiano.cultura.go.gov.br/
- Maranhão - http://ma.mapas.cultura.gov.br/
- Mato Grosso - https://mapas.mt.gov.br/
- Mato Grosso do Sul - https://www.mapacultural.ms.gov.br/
- Pará - https://mapacultural.pa.gov.br/
- Paraíba - http://pb.mapas.cultura.gov.br/
- Pernambuco - https://www.mapacultural.pe.gov.br/
- Sergipe - http://mapas.cultura.se.gov.br/
- Tocantins - http://mapa.cultura.to.gov.br/
### Instalações municipais
- Ilheus - http://ilheus.ba.mapas.cultura.gov.br/
- Camaçari - https://mapacultural.camacari.ba.gov.br/
- Senhor do Bonfim - http://senhordobonfim.ba.mapas.cultura.gov.br/
- Chorozinho - https://mapacultural.chorozinho.ce.gov.br/
- Sobral - https://cultura.sobral.ce.gov.br/
- Juazeiro do Norte - https://mapacultural.juazeiro.ce.gov.br/
- Belo Horizonte - http://mapaculturalbh.pbh.gov.br/
- Santa Luzia - http://mapacultural.santaluzia.mg.gov.br/
- Ipatinga - http://mapacultural.ipatinga.mg.gov.br/
- Varzea Grande - http://varzeagrande.mt.mapas.cultura.gov.br/
- João Pessoa - http://jpcultura.joaopessoa.pb.gov.br/
- Londrina - https://londrinacultura.londrina.pr.gov.br/
- Foz do Iguaçu - http://mapadaculturafoz.pmfi.pr.gov.br/
- Maringa - http://maringacultura.maringa.pr.gov.br:38081/
- Rio das Ostras - http://mapadacultura.riodasostras.rj.gov.br/
- Laguna - http://laguna.sc.mapas.cultura.gov.br/
- Novo Hamburgo - http://mapacultural.novohamburgo.rs.gov.br/
- Rio Grande - http://mapacultural.riogrande.rs.gov.br/
- São Paulo - http://spcultura.prefeitura.sp.gov.br/
- Santo André - http://culturaz.santoandre.sp.gov.br/
- São Caetano do Sul - http://mapacultural.saocaetanodosul.sp.gov.br/
- Osasco - http://osasco.sp.mapas.cultura.gov.br/
- Franco da Rocha - http://francodarocha.sp.mapas.cultura.gov.br/
- Guaruja - http://mapadacultura.guaruja.sp.gov.br/
- Varzea Paulista - http://janelacultural.varzeapaulista.sp.gov.br/
- Itu - http://mapacultural.itu.sp.gov.br/
- Guarulhos - http://grucultura.guarulhos.sp.gov.br/
- Itapetininga - http://mapacultural.itapetininga.sp.gov.br/

## Sobre a aplicação
Mapas Culturais é uma aplicação web server-side baseada em linguagem PHP e banco de dados Postgres, entre outras tecnologias e componentes, que propicia um ambiente virtual para mapeamento, divulgação e gestão de ativos culturais. 

## Projetos correlatos

* [Mapas Culturais APP](https://github.com/hacklabr/mapasculturais-app)
* [Cultural Magazine Theme](https://github.com/hacklabr/cultural)
* [Mapas SDK](https://github.com/centroculturalsp/MapasSDK)
* [Multiple Local Auth](https://github.com/LibreCoopUruguay/MultipleLocalAuth)


### Documentação 

A documentação pode ser navegada no endereço (http://docs.mapasculturais.org)

Toda documentação da aplicação está na pasta [documentation](documentation). Principais referências: 
- [Deploy](documentation/docs/mc_deploy.md)
- [Deploy Docker](documentation/docs/mc_developer_docker_enviroment.md)
- [API](documentation/docs/mc_config_api.md)
- [Guia do desenvolvedor](documentation/docs/mc_developer_guide.md)
- [Como contribuir](documentation/docs/mc_developer_contribute.md)
- [Habilitar um novo tema](documentation/docs/mc_deploy_theme.md)
- [Desenvolver um novo tema](documentation/docs/mc_developer_theme.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](documentation/docs/mc_deploy_shapefiles.md)

### [Software] Requisitos para Instalação
Lista dos principais softwares que compõe e aplicação. Maiores detalhes, ver documentação de [instalação](documentation/docs/mc_deploy.md) ou [guia do desenvolvedor](documentation/docs/mc_developer_guide.md). 

- [Ubuntu Server >= 18.04](http://www.ubuntu.com) ou [Debian Server >= 10](https://www.debian.org.)
- [PHP = 7.2](http://php.net)
  - [php-gd](http://php.net/manual/pt_BR/book.image.php)
  - [php-cli](https://packages.debian.org/pt-br/jessie/php5-cli)
  - [php-json](http://php.net/manual/pt_BR/book.json.php)
  - [php-curl](http://php.net/manual/pt_BR/book.curl.php)
  - [php-pgsql](http://php.net/manual/pt_BR/book.pgsql.php)
  - [php-apc](http://php.net/manual/pt_BR/book.apc.php)
- [Composer](https://getcomposer.org/)
- [PostgreSQL >= 10](http://www.postgresql.org/)
- [Postgis >= 2.2](http://postgis.net)
- [Node.JS >= 8.x](https://nodejs.org/en/)
  - [NPM](https://www.npmjs.com/)
  - [Terser](https://terser.org/)
  - [UglifyCSS](https://www.npmjs.com/package/gulp-uglifycss)
- [Ruby](https://www.ruby-lang.org/pt)
  - [Sass gem](https://rubygems.org/gems/sass/versions/3.4.22)

### [Hardware] Requisitos para instalação

Para instalações de pequeno/medio porte nas quais o número de entidades, isto é, número de agentes, espaços, projetos e evento,giram em torno de 2000 ativos, recomenda-se o mínimo de recursos para um servidor (aplicação + base de dados):

* 2 cores de CPU;
* 2gb de RAM;
* 50mbit de rede;

Desejável:

*  4 cores de CPU;
* 4gb de RAM;
* 100mbit de rede;

Para instalações em cidades de grande porte onde o número de entidades, isto é, número de agentes, espaços, projetos e evento, giram em torno de dezenas de milhares de ativos de cada, recomenda-se o mínimo de recursos para um servidor:

* 4 cores de CPU
* 4gb de RAM
* 100mbit de rede

Recomendado:
* 8 cores de CPU
* 8gb de RAM
* 500mbit de rede

Vale lembrar que os requisitos de hardware podem variar de acordo com a latência da rede, velocidade dos cores dos cpus, uso de proxies, entre outros fatores. Recomendamos aos sysadmin da rede em que a aplicação será instalada um monitoramento de tráfego e uso durante o período de 6 meses a 1 ano para avaliação de cenário de uso. 

### Canais de comunicação

* [Lista de discussão](https://groups.google.com/forum/?hl=en#!forum/mapas-culturais)
* Telegram: [![Join the chat at https://t.me/joinchat/WCYOkiRbAWmxQM2y](https://patrolavia.github.io/telegram-badge/chat.png)](https://t.me/joinchat/WCYOkiRbAWmxQM2y)
 

### Stories & Tests
- Travis:
[![Build Status](https://secure.travis-ci.org/mapasculturais/mapasculturais.png)](http://travis-ci.org/mapasculturais/mapasculturais)

### Licença de uso e desenvolvimento

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 


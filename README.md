# Mapa Cultural do Ceará

Em julho de 2013, agentes culturais de vários países da América Latina e do Brasil se reuniram para discutir a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro surgiram as bases para a criação de Mapas Culturais, um software livre que permite o aprimoramento da gestão cultural dos municípios e estados.

O projeto originalmente denominado Mapas Culturais é uma plataforma colaborativa que reúne informações sobre agentes, espaços, eventos e projetos culturais, fornecendo ao poder público uma radiografia da área de cultura e ao cidadão um mapa de espaços e eventos culturais da região. A plataforma está alinhada ao Sistema Nacional de Informação e Indicadores Culturais do Ministério da Cultura (SNIIC) e contribui para a realização de alguns dos objetivos do Plano Nacional de Cultura.

A plataforma já está em uso em diversos municipios, estados, no governo federal em diversos projetos do ministério da cultura e até mesmo fora do Brasil, no Uruguai. 

## Projeto Original (Mapas Culturais)
O projeto original atualmente é mantido de forma aberta e colaborativa, gerenciada pelo time de desenvolvimento do @HackLab

O repositório se encontra aqui: <https://github.com/mapasculturais/mapasculturais>

> Caso queira saber mais sobre o projeto MapasCulturais, gerenciado pelo HackLab, [Clique aqui](https://github.com/mapasculturais/mapasculturais/README.md)

## Este Fork
Devido a motivações internas e organizacionais, o Pessoal do Ceará, optou por seguir um caminho um pouco diferente, tanto em relação a arquitetura da aplicação, bem como, ao gerenciamento colaborativo do projeto, o que resultou neste repositório.

Temos muito a agradecer tudo que foi construído até a versão XXX, e a partir dela fizemos algumas mudanças que podem ser melhor detalhadas aqui:

- [Nova Arquitetura](./app/README.md) de arquivos e diretórios
- [Conexão](./app/README.md) com o Banco de Dados
- [Como colaborar](./app/README.md) criando issues
- [Como implementar](./app/README.md) novos códigos através de pull requests

## Tecnologias

- PHP7^
  - Symfony packages
  - Slim packages
  - Doctrine
  - PHP DI
  - PHPUnit
- PostgreSQL

---

## Instalação rápida com docker compose 

`docker compose up -d`

### Ferramentas

#### PHP Composer

`docker run --rm -it -v $PWD:/app composer:latest install`

#### Migração do Banco de Dados

`docker compose exec backend php src/tools/apply-updates.php`
`docker compose exec backend php src/tools/apply-multicore-db-update.php`

#### Frontend

`nvm use 18 && cd src && pnpm install --recursive && pnpm build`

npx oxlint@latest

#### Phpdoc

`docker run --rm -v ${PWD}/src:/data phpdoc/phpdoc:3`

#### Phpunit

`docker compose exec backend ./vendor/bin/phpunit`

#### Phpcs

`docker compose exec backend ./vendor/bin/phpcs -d memory_limit=1024M src/**/*.php`

#### Phplint

`docker run --rm -it -v $PWD:/app overtrue/phplint:latest /app/src/`

#### HTTP Endpoints

`egrep -r "function API_|function GET_|function POST_|function PATCH_|function PUT_|function DELETE_|function ALL_" src`

## Instalação
A maneira mais simples e segura para instalar o MapaCultural é seguindo [Este tutorial]()

## Documentação do Código

- [Getting Started]()
- API
  - [API V1](https://mapacultural.secult.ce.gov.br/api/v2/docs) do projeto original
  - [API V2](https://mapacultural.secult.ce.gov.br/api/v2/docs) baseada em RestFul, implementada neste fork
- Autenticação
  - Web
  - API V2

## Documentação Legada

A documentação pode ser navegada no endereço (http://docs.mapasculturais.org)

Toda documentação da aplicação está na pasta [documentation](documentation). Principais referências: 
- [API](http://docs.mapasculturais.org/apidoc/index.html?doctype=api)
- [API - exemplos](documentation/docs/mc_config_api.md)
- [Guia do desenvolvedor](documentation/docs/mc_developer_guide.md)
- [Como contribuir](documentation/docs/mc_developer_contribute.md)
- [Desenvolver um novo tema](documentation/docs/mc_developer_theme.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](documentation/docs/mc_deploy_shapefiles.md)
- [Deploy diretamente no sistema operacional](https://docs.mapasculturais.org/mc_deploy/) - **NÃO RECOMENDADO**
- [Habilitar um novo tema](documentation/docs/mc_deploy_theme.md)

## Mais Informações

Acesse aqui para ver a documentação do projeto original [aqui](./help/README.md)

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

### Licença de uso e desenvolvimento

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 


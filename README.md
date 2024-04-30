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


### Licença de uso e desenvolvimento

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 


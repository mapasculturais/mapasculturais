# Rede Mapas

Em julho de 2013, representantes culturais de diversos países da América Latina e do Brasil se uniram em um encontro histórico para debater a criação de uma ferramenta de mapeamento de iniciativas culturais e gestão cultural. Desse encontro, emergiram os alicerces para o desenvolvimento dos Mapas Culturais, um software de código aberto que possibilita o aprimoramento da gestão cultural em âmbito municipal e estadual.

O projeto originalmente chamado Mapas Culturais é uma plataforma colaborativa que agrega informações detalhadas sobre agentes, espaços, eventos e projetos culturais. Com isso, oferece ao poder público uma visão abrangente da área da cultura, enquanto proporciona ao cidadão um mapa interativo de espaços e eventos culturais da região. Alinhada com o Sistema Nacional de Informação e Indicadores Culturais do Ministério da Cultura (SNIIC), essa plataforma desempenha um papel crucial na realização de diversos objetivos estabelecidos pelo Plano Nacional de Cultura.

A plataforma já está em uso em diversos municipios, estados, no governo federal em diversos projetos do ministério da cultura e até mesmo fora do Brasil, no Uruguai. 

Mais detalhes sobre a História: [Clique aqui](https://rede.mapas.tec.br/)

## Projeto Original (Mapas Culturais)
O projeto original atualmente é mantido de forma aberta e colaborativa, gerenciada pelo time de desenvolvimento do @HackLab

O repositório se encontra aqui: <https://github.com/mapasculturais/mapasculturais>

> Caso queira saber mais sobre o projeto MapasCulturais, gerenciado pelo HackLab, [Clique aqui](https://github.com/mapasculturais/mapasculturais/README.md)

## Este Fork

> Fork de Repositório (Fork de Código): No desenvolvimento de software, especialmente em plataformas de controle de versão como GitHub, um fork é a criação de uma cópia de um repositório de código. Isso permite que o desenvolvedor trabalhe no código de forma independente, podendo fazer modificações e melhorias sem afetar o projeto original. Caso essas modificações sejam benéficas, podem ser integradas ao projeto original por meio de um pull request.

Optamos por criar um fork do projeto original com o objetivo de evoluir o sistema e oferecer uma plataforma ainda mais eficiente e acessível para a gestão cultural. As melhorias incluem uma nova arquitetura de arquivos e diretórios, uma conexão aprimorada com o banco de dados e um guia detalhado de colaboração, permitindo uma maior participação da comunidade no desenvolvimento e manutenção do projeto. Este fork visa não apenas otimizar o desempenho e a usabilidade do sistema, mas também fortalecer a rede de agentes culturais e facilitar o acesso à informação e à cultura.

Agradecemos profundamente a todos que contribuíram até a última versão do projeto. A partir dela, realizamos várias mudanças significativas que podem ser detalhadas aqui:

- [Nova Arquitetura](./app/README.md) de arquivos e diretórios
- [Conexão](./app/README.md) com o Banco de Dados
- [Como colaborar](./help/CREATE-ISSUES.md) criando issues
- [Como implementar](./app/CREATE-PULL-REQUESTS.md) novos códigos através de pull requests

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
A maneira mais simples e segura para instalar o MapaCultural é seguindo [Este tutorial](./help/INSTALL.md)

## Documentação do Novo Código

- [Getting Started](./app/README.md)
- API
  - [API V1](https://mapacultural.secult.ce.gov.br/mapas/docs/v1) do projeto original
  - [API V2](https://mapacultural.secult.ce.gov.br/mapas/docs/v2) baseada em RestFul, implementada neste fork
- Autenticação
  - Web (ainda em desenvolvimento)
  - API V2 (ainda em desenvolvimento)

## Documentação Legada

A documentação pode ser navegada no endereço (http://docs.mapasculturais.org)

<details>
<summary>Saiba mais</summary>

Toda documentação da aplicação está na pasta [documentation](documentation). Principais referências:
- [API](http://docs.mapasculturais.org/apidoc/index.html?doctype=api)
- [API - exemplos](documentation/docs/mc_config_api.md)
- [Guia do desenvolvedor](documentation/docs/mc_developer_guide.md)
- [Como contribuir](documentation/docs/mc_developer_contribute.md)
- [Desenvolver um novo tema](documentation/docs/mc_developer_theme.md)
- [Importação de arquivos de dados geoespaciais (Shapefiles)](documentation/docs/mc_deploy_shapefiles.md)
- [Deploy diretamente no sistema operacional](https://docs.mapasculturais.org/mc_deploy/) - **NÃO RECOMENDADO**
- [Habilitar um novo tema](documentation/docs/mc_deploy_theme.md)
</details>


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


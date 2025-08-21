> ⚠ COMUNICADO - REESTRUTURAÇÃO E ADEQUAÇÃO AO PADRÃO ADOTADO PELA COMUNIDADE  
>  
> O MinC está trabalhando na adequação do mapadacultura ao padrão oficial adotado pela comunidade Mapas Culturais, com base no repositório [mapasculturais/BaseProject](https://github.com/mapasculturais/mapasculturais-base-project).  
>  
> Está em curso uma adequação técnica e estrutural que visa assegurar maior consistência, interoperabilidade e sustentabilidade para todos os entes públicos que utilizam ou pretendem utilizar a plataforma.  
>  
> ⚙ Durante este período de transição, recomenda-se que novas implementações utilizem o [mapasculturais/BaseProject](https://github.com/mapasculturais/mapasculturais-base-project) como referência principal.  
>  
> ✅ Em breve, o MinC disponibilizará um novo repositório, derivado do BaseProject, que será adotado como referência para entes públicos que utilizam ou desejam implantar a plataforma Mapas Culturais.  
>  
> Agradecemos a compreensão e reiteramos nosso compromisso com a evolução, transparência e fortalecimento do ecossistema Mapas Culturais.
>

---
# Sobre
Mapas Culturais é uma aplicação web server, utilizando no backend linguagem PHP com Slim Framework e banco de dados Postgres. No frontend, linguagem Javascript com VueJS para componentização, propiciando um ambiente virtual para mapeamento, divulgação e gestão de ativos culturais.

## Para começar agora

1. Baixe o código do projeto a partir do repositório no github
2. Garanta que o seu ambiente de desenvolvimento possui as dependências do projeto( Docker, Docker Compose, Make)
3. Execute o comando `make dev` para criar os containers e executar o comando `make init` e `make init_dev` para iniciar o ambiente de desenvolvimento
4. Execute o comando `make db-restore`, informe a senha do banco de dados: mapas
5. Execute o comando `make db-migrations` para atualizar o banco de dados com as últimas modificações
6. Acesse o site em http://localhost:4242

<details>
<summary>

## Histórico

</summary>

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
- Maranhão - https://mapadacultura.ma.gov.br/
- Mato Grosso - https://mapas.mt.gov.br/
- Mato Grosso do Sul - https://www.mapacultural.ms.gov.br/
- Pará - https://mapacultural.pa.gov.br/
- Paraíba - http://pb.mapas.cultura.gov.br/
- Pernambuco - https://www.mapacultural.pe.gov.br/
- Sergipe - http://mapas.cultura.se.gov.br/
- Tocantins - https://mapadacultura.secult.to.gov.br/
- Acre - https://mapadacultura.ac.gov.br/
- Roraima - https://mapadacultura.rr.gov.br/

### Instalações municipais

- Aparecida de Goiânia - https://portaldacultura.aparecida.go.gov.br/
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
- Toledo/PR - https://cultura.toledo.pr.gov.br/
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
- Lagoa Santa - https://mapacultural.lagoasanta.mg.gov.br/


## Projetos correlatos
* [Mapas Cuturais Base Project](https://github.com/hacklabr/mapasculturais-app) - Repositório de projeto base para novas instalações.
* [Multiple Local Auth](https://github.com/mapasculturais/mapasculturais-MultipleLocalAuth) - Plugin de autenticação local + oauth.
* [Mapas SDK](https://github.com/centroculturalsp/MapasSDK)
* [Mapas Culturais APP](https://github.com/hacklabr/mapasculturais-app)
* [Cultural Magazine Theme](https://github.com/hacklabr/cultural)

</details>

<details>
<summary>

## Documentação

</summary>

Uma [nova documentação](https://mapasculturais.gitbook.io/bem-vindo-a-a-documentacao-do-mapas/) está sendo escrita no gitbook, organizada em três seções:
- [Documentação para usuários](https://mapasculturais.gitbook.io/documentacao-para-usuarios/)
- [Documentação para desenvolvedores](https://mapasculturais.gitbook.io/documentacao-para-desenvolvedores/formacao-para-desenvolvedores/)
- [Documentação para devops](https://mapasculturais.gitbook.io/documentacao-para-devops/instalacao/)

</details>

<details>
<summary>

## Documentação Legada

</summary>

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

</details>

<details>
<summary>

## [Software] Requisitos para Instalação

</summary>

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

</details>

<details>
<summary>

## [Hardware] Requisitos para instalação

</summary>

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

</details>

## Canais de comunicação

* Jitsi: https://meet.jit.si/MapasCulturais
* Canal do Telegram: [![Join the chat at https://t.me/joinchat/WCYOkiRbAWmxQM2y](https://patrolavia.github.io/telegram-badge/chat.png)](https://t.me/joinchat/WCYOkiRbAWmxQM2y)

###Regras do grupo do Telegram MAPAS CULTURAIS [DEV]

* Antes de postar, releia e analise se o conteúdo:
Ofenda as pessoas do grupo;
Se o conteúdo tem procedência. Só poste se você pode defender a autenticidade do assunto, consulte fontes seguras;
Seja propositivo, não faça críticas desnecessárias;
Se precisar chamar a atenção de alguém, faça com mensagens privadas, direto para os responsáveis;
Evite “ser chato”;
Qualquer membro que se sentir ofendido por outro, poderá informar aos admins para análise do conteúdo
Não faça publicidade e propagandas fora do tema Preferencialmente mande mensagem, mas audio não está proibido Nada de correntes: Repassar correntes é de muito mau gosto, até mesmo aquelas de “utilidade pública”. **Evite *

* Atitutdes proibidas, com possibilidade de ser removido do grupo:

Fazer propaganda, sem a permissão dos moderadores;
Envio de links de grupos sem a prévia autorização dos moderadores;
Postar assuntos que não sejam pertinentes ao propósito do grupo;
Postar mensagens com conteúdo de brincadeiras, piadas, racismo, pornografia, correntes, ativismo político, homofóbicos, sexualmente explícitos e abusivos;
Link externo para páginas inadequadas de forma evitar SPAM;
Debates políticos com propósitos partidários. 
  
## Licença de uso e desenvolvimento

Mapas Culturais é um software livre licenciado com [GPLv3](http://gplv3.fsf.org). 


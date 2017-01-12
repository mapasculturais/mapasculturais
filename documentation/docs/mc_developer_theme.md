# Customização do tema do Mapas Culturais
Este documento trata de uma customização simples do tema base do Mapas Culturais para novas instalações, e se restringe a customização das cores, imagens, textos e configuração do posicionamento inicial dos mapas. Este é o nível de customização utilizado nos diversos temas do repositório, como por exemplo os temas de Blumenau (blumenaumaiscultura.com.br), Ceará (mapa.cultura.ce.gov.br), Sobral (cultura.sobral.ce.gov.br) e SaoJose (lugaresdacultura.org.br).

Neste documento será feito um tema de exemplo para uma _Secretaria Municipal de Cultura_ cuja sigla é _**SECONDO**_, de uma cidade fictícia chamada _**Macondo**_. O nome do site será _**Macondo Cultural**_.

Veja também como [criar campos de informações adicionais no seu tema](mc_developer_theme_add_metadata.md).

#### Índice

- [Estrutura do Tema](#estrutura-do-tema)
- [Criando o tema](#criando-o-tema)
    - [Estílos](#estílos)
        - [Definição das variáveis SASS](#definição-das-variáveis-sass)
        - [Sobrescrevendo estilos](#sobrescrevendo-estilos)
        - [Compilando os arquivos SASS](#compilando-os-arquivos-sass)
    - [Imagens](#imagens)
        - [Substituindo imagens](#substituindo-imagens)
        - [Utilizando imagens nos templates](#utilizando-imagens-nos-templates)
        - [Utilizando imagens nos estilos](#utilizando-imagens-nos-estilos)
    - [Substituindo partes dos templates](#substituindo-partes-dos-templates)
    - [Textos](#textos)
    - [Páginas "Sobre" e "Como Usar"](#páginas-sobre-e-como-usar)
    - [Configurações fixas do tema](#configurações-fixas-do-tema)
        - [Configurando os mapas](#configurando-os-mapas)
        - [Divisões geográficas](#divisões-geográficas)
- [Criando um repositório do tema no Github](#criando-um-repositório-do-tema-no-github)
    - [Criando o repositório](#criando-o-repositório)
    - [Publicando alterações](#publicando-alterações)
    - [Clonando e atualizando a partir do repositório](#clonando-e-atualizando-a-partir-do-repositório)
- [Estrutura de arquivos do tema BaseV1](#estrutura-de-arquivos-do-tema-basev1)

## Estrutura do Tema
O tema padrão que do mapas é o tema `BaseV1`, localizado na pasta `src/protected/application/themes`, e esse tema contém todos os templates, imagens, estilos e scripts que são usados para renderizar as páginas (Ex: Mapa, Painel, Home..).

É possível estender o tema criando uma pasta, de modo que todos os arquivos que forem adicionados no tema "filho" irão sobrescrever os arquivos originais do tema `BaseV1`, isso inclui os templates, imagens, estilos e scripts. A estrutura completa está na seção [Estrutura de arquivos do tema BaseV1](#estrutura-de-arquivos-do-tema-basev1).

> **IMPORTANTE:** É necessário tomar cuidado ao sobrescrever os arquivos, já que posteriormente os arquivos do tema `BaseV1` podem ser alterados (incluindo uma nova funcionalidade, por exemplo) e isso não afetará o tema estendido.

Antes de decidir sobrescrever um arquivo, é indicado que verificar se não é possível efetuar a alteração desejada utilizando `hooks`, que pode ser consultado na documentação [*Guia do Desenvolvedor*](../developer-guide.md)

## Criando o tema
O primeiro passo é copiar o template de tema, disponível na pasta _src/protected/application/themes/**TemplateV1**_, para a pasta do novo tema: _src/protected/application/themes/**Macondo**_ ¹.

Feito isto renomeie o _namespace_ **TemplateV1**, na primeira linha do arquivo **Theme.php**, para o namespace do Tema (de preferência o mesmo nome da pasta)
```PHP
<?php
namespace Macondo;
...
```

¹ A princípio o tema pode estar em qualquer pasta acessível pelo usuário que roda a aplicação, porém por [limitações do SASS](http://stackoverflow.com/questions/5589067/sass-set-variable-at-compile-time) é necessário colocar _hardcoded_ o caminho para o tema BaseV1. Por esta razão colocamos o tema dentro da pasta _src/protected/application/themes/_ e importamos os arquivos SASS do tema BaseV1 utilizando a linha abaixo:
```
@import "../../../../BaseV1/assets/css/sass/main";
```
### Estilos
O tema _BaseV1_ do Mapas Culturais utiliza uma _extensão da linguagem CSS_ chamada [SASS](http://sass-lang.com/), e é através desta que são feitas as personalizações dos estilos do tema.

A personalização das cores, fontes e estilos é feita de duas maneiras: definindo os valores das variáveis utilizadas nos diversos arquivos sass (.scss) e, nos casos em que a definição das variáveis não é suficiente, sobrescrevendo as classes/estilos CSS.

#### Definição das variáveis SASS
A definição de novos valores para as variáveis deve ser feita no arquivo **assets/css/sass/_variables.scss** do tema filho, e uma lista completa das variáveis, assim como seus valores padrão, pode ser encontrada no arquivo [_variables.scss](../../src/protected/application/themes/BaseV1/assets/css/sass/globals/_variables.scss) do tema _BaseV1_.

No exemplo abaixo são definidas as variáveis das cores principais² que são utilizadas em diversos lugares do tema¹.
```CSS
$brand-primary: #5064A5 !default;
$brand-event:   #F7931D !default;
$brand-agent:   #5FB643 !default;
$brand-space:   #AB1F24 !default;
$brand-project: #795099 !default;
$brand-seal: 	#795099 !default;
$brand-devs:    #CE5AA1 !default;
```

¹ Para saber com mais precisão onde uma variável é utilizada e, por consequência, o que será afetado e onde estarão os efetitos de uma modificação, a melhor maneira é fazer uma busca pelo nome da variável (por exemplo _$brand-primary_) dentro da pasta [sass](../../src/protected/application/themes/BaseV1/assets/css/sass/) do tema BaseV1.

² Alterar as cores dos **eventos**, **agentes** e/ou **espaços** implica em recriar os arquivos dos _pins_ (ver os arquivos de imagens que começam com _pin-_ e _agrupador-_)  utilizados nos mapas para estas entidades.

#### Sobrescrevendo estilos
Quando a modificação que se deseja fazer é mais complexa e não é possível de fazê-la com uma simples mudança de valor de uma variável, ou a propriedade que se quer alterar não é definida por uma variável, ou ainda se a alteração deve ser num caso muito específico, como exemplo o tamanho da caixinha de edição do nome de agentes, o caminho a ser adotado é sobrescrever a classe/estilo.

Este nível de personalização deve ser feito no arquivo **assets/css/sass/_overrides.scss** do tema filho.

No exemplo abaixo mudamos a altura da imagem de _verificado_ para 100px;
```CSS
.widget-verified {
    height: 100px;
}
```

Se for utilizar imagens nos estilos, como por exemplo imagens de fundo, ver a seção [Utilizando imagens nos estilos](#utilizando-imagens-nos-estilos).

#### Compilando os arquivos SASS
Durante o processo de desenvolvimento do tema, a melhor maneira de se compilar o sass é utilizando o comando **sass --watch** (ver [documentação do sass](http://sass-lang.com/documentation/file.SASS_REFERENCE.html#using_sass)) que este recompilará o _.css_ sempre que houver modificações nos arquivos _.css_.

Entre pela linha de comando na pasta **assets/css** do tema filho e execute o seguinte comando:
```BASH
sass --watch sass/main.scss:main.css
```

### Imagens
#### Substituindo imagens
Qualquer imagem utilizada pelo tema _BaseV1_ pode ser substituida facilmente bastando, para isto, colocar uma imagem de mesmo nome na pasta **assets/img** do tema filho.

Por exemplo, para personalizar as imagens utilizadas como selo de que o conteúdo é verificado/oficial, basca colocar os arquivos com o brasão da instituição na pasta **assets/img**, obviamente respeitando os tamanhos e proporções (largura e altura).
```
assets/img/unverified-seal.png
assets/img/verified-icon.png
assets/img/verified-seal.png
assets/img/verified-seal-small.png
```

#### Utilizando imagens nos templates
Para utilizar uma nova imagem no tema filho, primeiro coloque o arquivo da imagem na pasta **assets/img** e em seguida chame o arquivo da seguinte forma no template, utilizando a função **asset** do tema:
```HTML+PHP
<img src="<?php $this->asset('img/nome-da-imagem.png'); ?>" >
```

#### Utilizando imagens nos estilos
Para utilizar uma imagem nos estilos é necessário _pedir_ para a aplicação _publicar_ esta imagem antes de utilizá-la. Para tal basta chamar, no método **_publishAssets** do arquivo **Theme.php** do tema filho, a função **asset** do tema passando _false_ como segundo parâmtro, que indicando que não é para imprimir a url do asset publicado.

exemplos:
```PHP
    protected function _publishAssets() {
        // somente publica o asset
        $this->asset('img/imagem-de-fundo.png', false);

        // publica e coloca a url do asset publicado no objeto MapasCulturais.assets para o js
        $this->jsObject['assets']['logo-instituicao'] = $this->asset('img/logo-instituicao.png', false);
    }
```

### Substituindo _partes_ dos templates
Da mesma forma como acontece com as imagens, quaquer arquivo **.php** das pastas **views** e **layouts** pode ser substituido facilmente, bastando para isto, que seja criado um arquivo com o mesmo nome no tema filho.

Os três arquivos mais comumente substituídos, e que já são incluídos no template de tema _TemplateV1_, são os seguintes:
```
layouts/parts/editable-entity-logo.php  // logo que aparece na barrinha cinza da edição de entidades
layouts/parts/header-about-nav.php      // menu da direita do header
layouts/parts/header-logo.php           // logo do site, a esquerda do header
```

### Textos
Os textos utilizados na home e em alguns outros lugares do site, como nos textos das [páginas "Sobre" e "Como Usar"](#páginas-sobre-e-como-usar), são definidos no método **_getTexts** arquivo **Theme.php**. O template de tema _TemplateV1_ contém todos os textos _configuráveis_ comentados com os valores padrão.

abaixo configuramos alguns textos de acordo com nossa instalação fictícia:
```PHP
            'site: name' => 'Macondo Cultural',
            'site: in the region' => 'na cidade de Macondo',
            'site: of the region' => 'da cidade de Macondo',
            'site: owner' => 'Secretaria Municipal de Cultura de Macondo',
            'site: by the site owner' => 'pela Secretaria Municipal de Cultura de Macondo',

            'home: abbreviation' => "SECONDO",
            'home: colabore' => "Colabore com o Macondo Cultural",

            'search: verified results' => 'Resultados da SECONDO',
            'search: verified' => "SECONDO"
```
### Páginas "Sobre" e "Como Usar"
Os textos das páginas [Sobre](../../src/protected/application/themes/BaseV1/pages/sobre.md) e [Como Usar]((../../src/protected/application/themes/BaseV1/pages/como-usar.md)) são pensados para serem genéricos e utilizam dos textos definidos [acima](#textos).

Caso os textos fornecidos pelo tema BaseV1 não supra as necessidades, basta criar os arquivos na pasta **pages** do tema filho e escrever, utilizando as linguagens Markdown ou HTML para formatação, os novos textos.

## Configurações fixas do tema
### Configurando os mapas
O primeiro passo para configurar¹ o mapas da busca e das páginas de criação de agentes e espaços, é conseguir os valores exatos da latitude, longitude e zoom.

A maneira mais simples de se fazer isto é através da própria página de busca, posicionando e definindo o zoom do mapa exatamente como ele deve se encontrar quando um usuário entrar na busca, e copiando os valores que aparecem na URL como no exemplo abaixo:

```
/busca/##(global:(enabled:(agent:!t),filterEntity:agent,map:(center:(lat:10.590252550882942,lng:-74.18719768524169),zoom:15)))
latitude:  10.590252550882942
longitude: -74.18719768524169
zoom:      15
```

Após obter os valores, defina no arquivo **conf-base.php** do tema filho os valores das chaves **'maps.center'** e **'maps.zoom.default'** com os valores obtidos.

```PHP
    // latitude,longitude do centro do mapa da busca e do mapa da criação de agentes e espaços
    'maps.center' => [10.590252550882942, -74.18719768524169],

    // zoom padrão do mapa da busca
    'maps.zoom.default' => 15,
```
¹ Há mais opções de configurações dos mapas que não serão abordadas neste documento mas que estão comentadas com os valores padrão no arquivo **conf-base.php** do TemplateV1.

### Divisões geográficas
As divisões geográficas configuradas devem ser as mesmas que foram importadas pelos shapefiles. No nosso exemplo usaremos somente _zona_ e _bairro_.

```PHP
    'app.geoDivisionsHierarchy' => [
        'zona'          => 'Zona',
        'bairro'        => 'Bairro'
    ],
```

## Criando um repositório do tema no Github
É recomendado que ao criar o tema ou efetuar alterações no tema, as alterações estejam em um repositório git para posteriormente seja possível clonar o tema de qualquer lugar, seja para utilizar em um servidor em produção ou para utilizar um ambiente de desenvolvimento.

### Criando o repositório
Para criar o repositório é necessário seguir os seguintes passos:
- Criar uma conta no [Github](github.com)
- No menu superior direito apertar a opção **New Repository**
- Preencher a caixa de texto **Repository name** com o nome do repositório (recomendável utilizar o mesmo nome escolhido na pasta do tema, para que ao clonar o repositório o nome fique correto)
- Apertar o botão **Create repository**
- Executar os seguintes comandos após criar o repositório (alterando **Macondo** para o nome do seu repositório/tema):
```
$ echo "# Macondo" >> README.md
$ git init
$ git add --all
$ git commit -m "first commit"
$ git remote add origin https://github.com/[endereco_github]/Macondo.git
$ git push -u origin master
```

### Publicando alterações
Dentro da pasta do tema, se alguma alteração for feita em qualquer arquivo será possível ver os arquivos que foram alterados utilizando o comando `git status`, mostrando os arquivos modificados. Ao alterar arquivos o resultado deve ser parecido com isso:
```
On branch master
Your branch is up-to-date with 'origin/master'.
Changes not staged for commit:
  (use "git add <file>..." to update what will be committed)
  (use "git checkout -- <file>..." to discard changes in working directory)

	modified:   Theme.php
	modified:   conf-base.php

no changes added to commit (use "git add" and/or "git commit -a")
```
Neste exemplo, houveram alterações nos arquivos *Theme.php* e no arquivo *conf-base.php*. Para publicar esse arquivos é necessário utilizar os comandos
- `git add [nome_do_arquivo]`
- `git commit -m "[mensagem_com_descricao_da_alteracao]"`
- `git push`

Como no exemplo, para publicar os arquivos *Theme.php* e *conf-base.php* os comando seriam assim:
```
$git add Theme.php
$git add conf-base.php
$git commit -m "Alterações de configurações do Tema"
$git push
```

### Clonando e atualizando a partir do repositório
Após as alterações estarem publicadas em um repositório do Github, podemos obter o código publicado com o comando `git clone https://github.com/[endereco_github]/Macondo`, isso irá criar uma pasta chamada **Macondo** onde o comando foi executado. Preferencialmente executado da pasta `themes` da instalação do mapas:

```
$ cd src/protected/application/themes
$ git clone https://github.com/[endereco_github]/Macondo
```

Se alguma alteração for efetuada no repositório do Github e for necessário atualizar o tema localmente, pode se utilizar o comando `git pull` para atualizar o código localmente.

> **IMPORTANTE:** Ao utilizar o comando `git pull` verificar se não existem arquivos locais que foram alterados e podem ser sobrescritar ou gerar algum conflito.

Para mais detalhes sobre como utilizar o git, é recomendável a leitura da documentação e artigos úteis:
- https://git-scm.com/book/pt-br/v1/Primeiros-passos-No%C3%A7%C3%B5es-B%C3%A1sicas-de-Git
- http://rogerdudler.github.io/git-guide/index.pt_BR.html

## Estrutura de arquivos do tema BaseV1
- [BaseV1/](../../src/protected/application/themes/BaseV1/)
    - [db-updates.php](../../src/protected/application/themes/BaseV1/db-updates.php) _scripts para atualização de banco que afetam todos os temas filhos._
    - [Theme.php](../../src/protected/application/themes/BaseV1/Theme.php) _classe **MapasCulturais\Themes\BaseV1\Theme**. Todos os temas filhos estendem esta classe._
    - [assets/](../../src/protected/application/themes/BaseV1/assets/) _pasta onde ficam os arquivos estáticos_
        - [css/](../../src/protected/application/themes/BaseV1/assets/css/)
            - [sass/](../../src/protected/application/themes/BaseV1/assets/css/sass/) _arquivos fonte .scss ([precisam ser compilados](#compilando-os-arquivos-sass))._
        - [fonts/](../../src/protected/application/themes/BaseV1/assets/fonts/) _as fontes utilizadas no tema BaseV1._
        - [img/](../../src/protected/application/themes/BaseV1/assets/img/) _as imagens utilizadas no tema BaseV1._
            - [agrupador-agente.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-agente.png) _pin para grupo de **agentes** no mapa da busca._
            - [agrupador-combinado-agente-evento.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-combinado-agente-evento.png) _pin para grupo de **agentes e eventos** no mapa da busca._
            - [agrupador-combinado-espaco-agente.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-combinado-espaco-agente.png) _pin para grupo de **agentes e espaços** no mapa da busca._
            - [agrupador-combinado-espaco-evento.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-combinado-espaco-evento.png) _pin para grupo de **espaços e eventos** no mapa da busca._
            - [agrupador-combinado.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-combinado.png) _pin para grupo de **agentes, espaços e eventos** no mapa da busca._
            - [agrupador-espaco.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-espaco.png) _pin para grupo de **espaços** no mapa da busca._
            - [agrupador-evento.png](../../src/protected/application/themes/BaseV1/assets/img/agrupador-evento.png) _pin para grupo de **eventos** no mapa da busca._
            - [avatar--agent.png](../../src/protected/application/themes/BaseV1/assets/img/avatar--agent.png) _avatar padrão do agente - imagem que aparece como avatar do agente quando o usuário não subiu nunhuma imagem._
            - [avatar--event.png](../../src/protected/application/themes/BaseV1/assets/img/avatar--event.png) _avatar padrão do evento - imagem que aparece como avatar do evento quando o usuário não subiu nunhuma imagem._
            - [avatar--project.png](../../src/protected/application/themes/BaseV1/assets/img/avatar--project.png) _avatar padrão do projeto - imagem que aparece como avatar do projeto quando o usuário não subiu nunhuma imagem._
            - [avatar--space.png](../../src/protected/application/themes/BaseV1/assets/img/avatar--space.png) _avatar padrão do espaço - imagem que aparece como avatar do espaço quando o usuário não subiu nunhuma imagem._
            - [favicon-32.ico](../../src/protected/application/themes/BaseV1/assets/img/favicon-32.ico) _favicon de 32px._
            - [favicon.ico](../../src/protected/application/themes/BaseV1/assets/img/favicon.ico) _favicon de 16px._
            - [fundo.png](../../src/protected/application/themes/BaseV1/assets/img/fundo.png)
            - [icon-circulo.png](../../src/protected/application/themes/BaseV1/assets/img/icon-circulo.png) _ícone do botão de selecionar uma área do mapa da busca._
            - [icon-fullscreen.png](../../src/protected/application/themes/BaseV1/assets/img/icon-fullscreen.png) _ícone do botão fullscreen dos mapas._
            - [icon-localizacao.png](../../src/protected/application/themes/BaseV1/assets/img/icon-localizacao.png) _ícone do botão que centraliza o mapa baseado na localização do usuário._
            - [icon-zoom-in.png](../../src/protected/application/themes/BaseV1/assets/img/icon-zoom-in.png) _ícone do botão de zoom + dos mapas._
            - [icon-zoom-out.png](../../src/protected/application/themes/BaseV1/assets/img/icon-zoom-out.png) _ícone do botão de zoom - dos mapas._
            - [instituto-tim-white.png](../../src/protected/application/themes/BaseV1/assets/img/instituto-tim-white.png)
            - [layers.png](../../src/protected/application/themes/BaseV1/assets/img/layers.png) _ícone do botão para selecionar as camadas para serem exibidas no mapa da busca._
            - [marca-da-org.png](../../src/protected/application/themes/BaseV1/assets/img/marca-da-org.png)  _logotipo da marca da organização._
            - [marker-icon.png](../../src/protected/application/themes/BaseV1/assets/img/marker-icon.png) _pin que exibe a localização do usuário no mapa da busca._
            - [pin-agente.png](../../src/protected/application/themes/BaseV1/assets/img/pin-agente.png) _pin do agente (não agrupado) nos mapas._
            - [pin-espaco.png](../../src/protected/application/themes/BaseV1/assets/img/pin-espaco.png) _pin do espaço (não agrupado) nos mapas._
            - [pin-evento.png](../../src/protected/application/themes/BaseV1/assets/img/pin-evento.png) _pin do evento (não agrupado) nos mapas._
            - [pin-sombra.png](../../src/protected/application/themes/BaseV1/assets/img/pin-sombra.png) _sombra dos pins._
            - [setinhas-editable.png](../../src/protected/application/themes/BaseV1/assets/img/setinhas-editable.png) _setinhas utilizadas nas caixas de edição dos campos das entidades._
            - [share.png](../../src/protected/application/themes/BaseV1/assets/img/share.png) _imagem padrão utilizada nos compartilhamentos (facebook, google+, etc.) quando não há outra imagem a ser utilizada, como por exemplo o avatar da entidade._
            - [spinner_192.gif](../../src/protected/application/themes/BaseV1/assets/img/spinner_192.gif) _spinner que é utilizado no carregamento dos vídeos das entidades._
            - [spinner-black.gif](../../src/protected/application/themes/BaseV1/assets/img/spinner-black.gif) _spinner que é utilizado em diversas partes do tema quando o fundo é claro._
            - [spinner.gif](../../src/protected/application/themes/BaseV1/assets/img/spinner.gif) _spinner que é utilizado em diversas partes do tema quando o fundo é escuro._
            - [tim.png](../../src/protected/application/themes/BaseV1/assets/img/tim.png)
            - [unverified-seal.png](../../src/protected/application/themes/BaseV1/assets/img/unverified-seal.png)
            - [verified-icon.png](../../src/protected/application/themes/BaseV1/assets/img/verified-icon.png)
            - [verified-seal.png](../../src/protected/application/themes/BaseV1/assets/img/verified-seal.png)
            - [verified-seal-small.png](../../src/protected/application/themes/BaseV1/assets/img/verified-seal-small.png)
            - [tour/](../../src/protected/application/themes/BaseV1/assets/img/tour/)
        - [js/](../../src/protected/application/themes/BaseV1/assets/js/)
        - [vendor/](../../src/protected/application/themes/BaseV1/assets/vendor/)
    - [layouts/](../../src/protected/application/themes/BaseV1/layouts/)
        - [default.php](../../src/protected/application/themes/BaseV1/layouts/default.php)
        - [search.php](../../src/protected/application/themes/BaseV1/layouts/search.php)
        - [panel.php](../../src/protected/application/themes/BaseV1/layouts/panel.php)
        - [parts/](../../src/protected/application/themes/BaseV1/layouts/parts/)
            - [agenda-content.php](../../src/protected/application/themes/BaseV1/layouts/parts/agenda-content.php)
            - [agenda-header.php](../../src/protected/application/themes/BaseV1/layouts/parts/agenda-header.php)
            - [agenda.php](../../src/protected/application/themes/BaseV1/layouts/parts/agenda.php)
            - [ajax-uploader.php](../../src/protected/application/themes/BaseV1/layouts/parts/ajax-uploader.php)
            - [busca-avancada.php](../../src/protected/application/themes/BaseV1/layouts/parts/busca-avancada.php)
            - [downloads.php](../../src/protected/application/themes/BaseV1/layouts/parts/downloads.php)
            - [editable-entity-logo.php](../../src/protected/application/themes/BaseV1/layouts/parts/editable-entity-logo.php)
            - [editable-entity.php](../../src/protected/application/themes/BaseV1/layouts/parts/editable-entity.php)
            - [entity-parent.php](../../src/protected/application/themes/BaseV1/layouts/parts/entity-parent.php)
            - [entity-status.php](../../src/protected/application/themes/BaseV1/layouts/parts/entity-status.php)
            - [footer.php](../../src/protected/application/themes/BaseV1/layouts/parts/footer.php)
            - [gallery.php](../../src/protected/application/themes/BaseV1/layouts/parts/gallery.php)
            - [header-about-nav.php](../../src/protected/application/themes/BaseV1/layouts/parts/header-about-nav.php)
            - [header-logo.php](../../src/protected/application/themes/BaseV1/layouts/parts/header-logo.php)
            - [header-main-nav.php](../../src/protected/application/themes/BaseV1/layouts/parts/header-main-nav.php)
            - [header.php](../../src/protected/application/themes/BaseV1/layouts/parts/header.php)
            - [link-list.php](../../src/protected/application/themes/BaseV1/layouts/parts/link-list.php)
            - [metalist-form-template.php](../../src/protected/application/themes/BaseV1/layouts/parts/metalist-form-template.php)
            - [owner.php](../../src/protected/application/themes/BaseV1/layouts/parts/owner.php)
            - [panel-agent.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-agent.php)
            - [panel-app.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-app.php)
            - [panel-em-cartaz.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-em-cartaz.php)
            - [panel-event.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-event.php)
            - [panel-nav.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-nav.php)
            - [panel-project.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-project.php)
            - [panel-registration.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-registration.php)
            - [panel-space.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-space.php)
            - [panel-seal.php](../../src/protected/application/themes/BaseV1/layouts/parts/panel-seal.php)
            - [redes-sociais.php](../../src/protected/application/themes/BaseV1/layouts/parts/redes-sociais.php)
            - [related-agents.php](../../src/protected/application/themes/BaseV1/layouts/parts/related-agents.php)
            - [templates.php](../../src/protected/application/themes/BaseV1/layouts/parts/templates.php)
            - [verified.php](../../src/protected/application/themes/BaseV1/layouts/parts/verified.php)
            - [video-gallery.php](../../src/protected/application/themes/BaseV1/layouts/parts/video-gallery.php)
            - [widget-areas.php](../../src/protected/application/themes/BaseV1/layouts/parts/widget-areas.php)
            - [widget-tags.php](../../src/protected/application/themes/BaseV1/layouts/parts/widget-tags.php)
    - [pages/](../../src/protected/application/themes/BaseV1/pages/)
        - [como-usar.md](../../src/protected/application/themes/BaseV1/pages/como-usar.md)
        - [_left.md](../../src/protected/application/themes/BaseV1/pages/_left.md)
        - [_right.md](../../src/protected/application/themes/BaseV1/pages/_right.md)
        - [sobre.md](../../src/protected/application/themes/BaseV1/pages/sobre.md)
    - [views/](../../src/protected/application/themes/BaseV1/views/)
        - [agent/](../../src/protected/application/themes/BaseV1/views/agent/)
            - [create.php](../../src/protected/application/themes/BaseV1/views/agents/create.php) -> single.php
            - [edit.php](../../src/protected/application/themes/BaseV1/views/agents/edit.php) -> single.php
            - [single.php](../../src/protected/application/themes/BaseV1/views/agents/single.php)
        - [app/](../../src/protected/application/themes/BaseV1/views/app/)
            - [create.php](../../src/protected/application/themes/BaseV1/views/app/create.php)
            - [edit.php](../../src/protected/application/themes/BaseV1/views/app/edit.php)
            - [single.php](../../src/protected/application/themes/BaseV1/views/app/single.php) -> edit.php
        - [auth/](../../src/protected/application/themes/BaseV1/views/auth/)
            - [fake-authentication.php](../../src/protected/application/themes/BaseV1/views/auth/fake-authentication.php)
        - [event/](../../src/protected/application/themes/BaseV1/views/event/)
            - [create.php](../../src/protected/application/themes/BaseV1/views/event/create.php) -> single.php
            - [edit.php](../../src/protected/application/themes/BaseV1/views/event/edit.php) -> single.php
            - [single.php](../../src/protected/application/themes/BaseV1/views/event/single.php)
        - [generic/](../../src/protected/application/themes/BaseV1/views/generic/)
            - [edit.php](../../src/protected/application/themes/BaseV1/views/generic/edit.php)
            - [list.php](../../src/protected/application/themes/BaseV1/views/generic/list.php)
        - [panel/](../../src/protected/application/themes/BaseV1/views/panel/)
            - [agents.php](../../src/protected/application/themes/BaseV1/views/panel/agents.php)
            - [apps.php](../../src/protected/application/themes/BaseV1/views/panel/apps.php)
            - [em-cartaz.php](../../src/protected/application/themes/BaseV1/views/em-panel/cartaz.php)
            - [events.php](../../src/protected/application/themes/BaseV1/views/panel/events.php)
            - [generic.php](../../src/protected/application/themes/BaseV1/views/panel/generic.php)
            - [index.php](../../src/protected/application/themes/BaseV1/views/panel/index.php)
            - [projects.php](../../src/protected/application/themes/BaseV1/views/panel/projects.php)
            - [registrations.php](../../src/protected/application/themes/BaseV1/views/panel/registrations.php)
            - [require-authentication.php](../../src/protected/application/themes/BaseV1/views/panel/require-authentication.php)
            - [spaces.php](../../src/protected/application/themes/BaseV1/views/panel/spaces.php)
        - [project/](../../src/protected/application/themes/BaseV1/views/project/)
            - [create.php](../../src/protected/application/themes/BaseV1/views/project/create.php) -> single.php
            - [edit.php](../../src/protected/application/themes/BaseV1/views/project/edit.php) -> single.php
            - [report.php](../../src/protected/application/themes/BaseV1/views/project/report.php)
            - [single.php](../../src/protected/application/themes/BaseV1/views/project/single.php)
        - [registration/](../../src/protected/application/themes/BaseV1/views/registration/)
            - [edit.php](../../src/protected/application/themes/BaseV1/views/registration/edit.php)
            - [single.php](../../src/protected/application/themes/BaseV1/views/registration/single.php)
        - [site/](../../src/protected/application/themes/BaseV1/views/site/)
            - [index.php](../../src/protected/application/themes/BaseV1/views/site/index.php)
            - [page.php](../../src/protected/application/themes/BaseV1/views/site/page.php)
            - [permission-denied.php](../../src/protected/application/themes/BaseV1/views/site/permission-denied.php)
            - [search.php](../../src/protected/application/themes/BaseV1/views/site/search.php)
        - [space/](../../src/protected/application/themes/BaseV1/views/space/)
            - [create.php](../../src/protected/application/themes/BaseV1/views/space/create.php) -> single.php
            - [edit.php](../../src/protected/application/themes/BaseV1/views/space/edit.php) -> single.php
            - [single.php](../../src/protected/application/themes/BaseV1/views/space/single.php)

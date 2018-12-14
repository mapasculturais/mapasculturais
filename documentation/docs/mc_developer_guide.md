# Mapas Culturais > Guia do Desenvolvedor
----

O intuito deste documento é dar uma visão panorâmica da arquitetura e funcionamento do Mapas Culturais para quem quiser colaborar no desenvolvimento da plataforma. Este documento está ainda incompleto e em constante desenvolvimento.

- [Branches e desenvolvimento](#branches-e-desenvolvimento)
- [Requisitos](#requisitos)
    - [Bibliotecas PHP utilizadas](#bibliotecas-php-utilizadas)
    - [Bibliotecas Javascript utilizadas](#bibliotecas-javascript-utilizadas)
- [Arquivo de Configuração](#arquivo-de-configuracao)
- [App](#app)
- [Traits](#traits)
    - [Traits Genéricos](#traits-genéricos)
- [Model](#model)
- [Controller](#controller)
- [View](#view)
    - [Temas](#temas)
      - [theme.php](theme-php)
      - [Estrutura de pastas do tema](#estrutura-de-pastas-do-tema)
    - [Páginas](#páginas)
    - [Layouts](#layouts)
    - [Visões](#visões)
    - [Partes](#partes)
    - [Assets](#assets)
    - [Variáveis Acessíveis](#variáveis-acessíveis)
    - [Verificando se um usuário está logado](#verificando-se-um-usuário-está-logado)
- [Autenticação]()
- [Roles]()
- [Log]()
- [Cache]()
- [Outputs da API]()
- [Exceções]()
- [Busca Por Palavra-chave](mc_developer_keywords.md)

## Branches e desenvolvimento

O desenvolvimento do Mapas Culturais segue o padrão [Git Workflow](https://danielkummer.github.io/git-flow-cheatsheet/), com as seguintes branches principais:

**Branch develop**: 

  - Branch utilizado para desenvolvimento;
  - Tudo que existe de novo está nesse branch;
  - Atenção: para desenvolvimento de novas features, o desenvolvedor deve criar um branch novo a partir desta branch ou da branch master;

**Branch master**:

  - Branch com a versão estável da aplicação;

Para fazer uma nova instalação, utilize o release (tag) mais atual.


## Requisitos

- [PHP >= 5.4](http://php.net)
  - [php5-gd](http://php.net/manual/pt_BR/book.image.php)
  - [php5-cli] (https://packages.debian.org/pt-br/jessie/php5-cli)
  - [php5-json](http://php.net/manual/pt_BR/book.json.php)
  - [php5-curl](http://php.net/manual/pt_BR/book.curl.php)
  - [php5-pgsql](http://php.net/manual/pt_BR/book.pgsql.php)
  - [php-apc](http://php.net/manual/pt_BR/book.apc.php)
- [Composer](https://getcomposer.org/)
- [PostgreSQL >= 9.3](http://www.postgresql.org/)
- [Postgis >= 2.1](http://postgis.net)
  - [PostgreSQL-Postgis-Scripts](http://packages.ubuntu.com/trusty/misc/postgresql-9.3-postgis-2.1)
- [Node.JS >= 0.10](https://nodejs.org/en/)
  - [NPM](https://www.npmjs.com/)
  - [UglifyJS](https://www.npmjs.com/package/uglify-js)
  - [UglifyCSS](https://www.npmjs.com/package/gulp-uglifycss)
- [Ruby] (https://www.ruby-lang.org/pt)
  - [Sass gem] (https://rubygems.org/gems/sass/versions/3.4.22)

### Bibliotecas PHP Utilizadas
Ver arquivo [composer.json](../src/protected/composer.json)
- [Slim](https://packagist.org/packages/slim/slim) - Microframework em cima do qual foi escrita a classe [App](#app) do MapasCulturais.
- [Doctrine/ORM](https://packagist.org/packages/doctrine/orm) - ORM utilizado para o mapeamento das entidades.
- [Opauth/OpenId](https://packagist.org/packages/opauth/openid) - Utilizado para autenticação via OpenId.
- [respect/validation](https://packagist.org/packages/respect/validation) - Utilizado para as validações das propriedades e metadados das entidades.
- [smottt/wideimage](https://packagist.org/packages/smottt/wideimage) - Utilizado para *transformar* imagens (criar thumbnails, por exemplo).
- [phpunit/phpunit](https://packagist.org/packages/phpunit/phpunit) - Utilizado para testes.
- [creof/doctrine2-spatial](https://packagist.org/packages/creof/doctrine2-spatial) - Faz o mapeamento de várias procedures do PostGIS para o doctrine.
- [mustache/mustache](https://packagist.org/packages/mustache/mustache) - Utilizado para renderizar alguns templates.
- [phpoffice/phpword](https://packagist.org/packages/phpoffice/phpword) - Utilizado para criar .docs ou .xls onde necessário.
- [michelf/php-markdown](https://packagist.org/packages/michelf/php-markdown) - Utilizado para renderizar os markdowns das [páginas](#páginas)
- [pomo/pomo](https://packagist.org/packages/pomo/pomo) - Biblioteca Gettext para PHP, usado para internacionalização

### Bibliotecas Javascript Utilizadas
Ver [bibliotecas javascript utilizadas no tema](#bibliotecas-javascript-utilizadas-no-tema).

## Arquivo de Configuração

## App

## Traits
Os [traits](http://php.net/manual/pt_BR/language.oop5.traits.php) ficam no namespace **MapasCulturais\Traits** e seus arquivos na pasta [src/protected/application/lib/MapasCulturais/Traits](../src/protected/application/lib/MapasCulturais/Traits). 

Se houver no nome do trait um prefixo (*Entity, Controller ou Repository*) significa que este trait só deve ser utilizado em classes que estendam a classe com o nome do prefixo dentro do namespace MapasCulturais (ex: o trait *EntityAvatar* só deve ser utilizado em classes que estendem a classe *MapasCulturais\Entity*). Já se não houver um prefixo significa que é um [trait genérico](#traits-genéricos) e que pode ser utilizado em qualquer classe (exemplos: Singleton e MagicGetter).


### Traits Genéricos
Os traits genéricos podem ser usados em qualquer classe do sistema.

#### Singleton
Implementa o design pattern [singleton](http://pt.wikipedia.org/wiki/Singleton). É utilizada nas classes **App**, **GuestUser**, **ApiOutput**, **Controller** entre outras.

#### MagicGetter
#### MagicSetter
#### MagicCallers


## Model
As classes de modelo ficam no namespace **MapasCulturais\Entities** e seus arquivos dentro da pasta [src/protected/application/lib/MapasCulturais/Entities](../src/protected/application/lib/MapasCulturais/Entities). 

Estas classes devem estender a classe abstrata [MapasCulturais\Entity](#classe-entity) e usar os [Docblock Annotations](http://docs.doctrine-project.org/en/latest/reference/annotations-reference.html) do [Doctrine](http://docs.doctrine-project.org/en/latest/index.html) para fazer o [mapeamento](http://docs.doctrine-project.org/en/latest/reference/basic-mapping.html) com a representação desta entidade no banco de dados (geralmente uma tabela). 

Estas podem também usar os [traits criados para entidades](#traits-das-entidades) (os que têm o prefixo **Entity** no nome, como por exemplo o *EntityFiles*, que é para ser usado em entidades que têm arquivos anexos).

### Classe Entity
A classe abstrata [MapasCulturais\Entity](../src/protected/application/lib/MapasCulturais/Entity.php) é a classe que serve de base para todas as entidades do sistema. Implementa uma série de métodos úteis para, entre outros, [verificação de permissões](#verificação-de-permissões), serialização e [validações](#validações).

### Traits das Entidades

- **EntityAgentRelation** - Deve ser usado em entidades que podem ter agentes relacionados. Requer uma entidade auxiliar com o mesmo nome da entidade acrescida do sufixo AgentRelation (exemplo: para a entidade *Event*, uma classe *EventAgentRelation*).
- **EntityFiles** - Deve ser usado em entidades que podem ter arquivos anexados.
- **EntityAvatar** - Deve ser usado em entidades que tenham avatar. Requer o trait *EntityFiles*.
- **EntityGeoLocation** - Deve ser usado em entidades georreferenciadas. Requer as propriedades *location*, do tipo *point*, e *_geoLocation*, do tipo *geography*.
- **EntityMetadata** - Deve ser usado em entidades que tenham metadados. Requer de uma entidade auxiliar. Se existir no mesmo namespace uma classe com o nome da entidade acrescida do sufixo *Meta* (exemplo: para a entidade *Agent*, uma classe *AgentMeta*), esta será usada, senão a entidade *Metadata* será usada como auxiliar.
- **EntityMetaLists** - Deve ser usado em entidades que tenham metadados com múltiplos valores por chave. (exemplo de uso: links).
- **EntityNested** - Deve ser usado em entidades hierárquicas. Requer as [associações autorreferenciadas](http://docs.doctrine-project.org/en/latest/reference/association-mapping.html#one-to-many-self-referencing) *children* e *parent*.
- **EntityOwnerAgent** - Deve ser usado em entidades que tenham a associação [ManyToOne](http://docs.doctrine-project.org/en/latest/reference/association-mapping.html#many-to-one-unidirectional) *owner* apontando para a entidade *MapasCulturais\Entity\Agent*. Requer também um mapeamento do tipo *int* chamado *_ownerId* que representa o id do agente que é dono desta entidade.
- **EntitySoftDelete** - Usado em entidades que necessitem de lixeira. Requer um mapeamento do tipo *int* chamado *status*.
- **EntityTaxonomies** - Deve ser usado em entidades que precisem de taxonomias (tags, área de atuação, etc.).
- **EntityTypes** - Deve ser usado em entidades que tenham tipos. Requer um mapeamento do tipo *int* chamado *_type*. 
- **EntityVerifiable** - Deve ser usado em entidades *verificáveis*, o seja, que podem ser marcadas como *oficiais* pelos admins ou membros da equipe.

### Verificação de Permissões
A verificação das permissões é feita através do método **checkPermission**, passando como parâmetro para este o nome da ação que você deseja checar se o usuário tem ou não permissão para executar. Este método, por sua vez, chama o método [canUser](#método-canuser) que retornará um booleano *true* se o usuário pode executar a ação ou *false* se o usuário não pode executar a ação. 
Caso o usuário não possa executar a ação, o método **checkPermission** lançará uma exceção do tipo [PermissionDenied](#permissiondenied).

#### Método canUser
O método **canUser** recebe como primeiro parâmetro o nome da ação e opcionalmente, como segundo parâmetro, um usuário. Se nenhum usuário for enviado, será usado o usuário logado ou *guest*. O retorno desta função é um booleano indicando se o usuário pode ou não executar a ação.

Este método procurará por um método auxiliar chamado *canUser* acrescido do nome da ação (exemplo: para a ação **remove**, um método chamado **canUserRemove**) e caso não ache será usado o método [genericPermissionVerification](#método-genericpermissionverification).

No exemplo a seguir dizemos que somente admins podem alterar o status da entidade Exemplo.
```PHP
class Exemplo extends MapasCulturais\Entity{
    use MapasCulturais\Traits\MagicSetter
    ....
    ....
    protected $_status = 0;
    
    function setStatus($status){
        $this->checkPermission('modifyStatus');
        $this->_status = $status;
        $this->save();
    }
    
    protected function canUserModifyStatus($user){
        if($user->is("admin"))
            return true;
        else
            return false;
    }
}

```

#### Método genericPermissionVerification
Este método é utilizado sempre que uma checagem de permissão é feita e o método **canUser** não encontra um método auxiliar com o nome da ação. 

O corpo deste método é o seguinte:
```PHP
protected function genericPermissionVerification($user){
    if($user->is('guest'))
        return false;
    
    if($user->is('admin'))
        return true;
    
    if($this->getOwnerUser()->id == $user->id)
        return true;
    
    if($this->usesAgentRelation() && $this->userHasControl($user))
        return true;
    
    return false;
}

```

### Validações das Entidades

## Controller

## View

### Temas
Por enquanto ainda não temos resolvida a estrutura para múltiplos temas. O que temos é um tema único dentro da pasta **src/protected/application/themes/active**, que será modificado para aceitar configurações.

#### Bibliotecas Javascript utilizadas no tema
Por enquanto ainda não utilizamos um gerenciador de pacotes para as bibliotecas Javascript. Estas ficam na [pasta assets/vendor/](#estrutura-de-pastas-do-tema).
 - [AngularJS](https://angularjs.org/)
 - [jQuery](http://jquery.com/)

#### theme.php
Este arquivo fica na pasta raiz do tema (**src/protected/application/themes/active**) e é usado para colocar funções helpers usadas dentro do tema e para estender o sistema utilizando a [API de plugins](mc_config_api.md).

#### Estrutura de pastas do tema
Dentro da pasta raiz do tema
- **assets/** - *aonde deve ficar tudo que é acessível pelo público dentro da url **/public** do site*
  - **css/**
  - **fonts/**
  - **img/**
  - **vendor/**
- **layouts/** - *aonde ficam os layouts do site*
    - **parts/** - *aonde ficam os template parts utilizados pelo tema*
- **views/** - *aonde ficam as visões dos controles*
- **pages/** - *aonde ficam os arquivos de páginas*

### Páginas
As páginas do sistema são arquivos **.md** (Markdown) salvos dentro da pasta **pages/** do tema. Para criar uma nova página basta criar um novo arquivo **.md** dentro desta pasta. Estes arquivos são renderizadas pela biblioteca [PHP Markdown Extra](https://michelf.ca/projects/php-markdown/extra/).

#### Url da página
Para uma página cujo nome de arquivo é **nome-da-pagina.md**, a url de acesso será **http://mapasculturais/page/site/nome-da-pagina/**


#### Título da página
O texto do **primeiro h1** do conteúdo da página será utilizado como título da página (tag **title**). Isto é feito via javascript.


No exemplo a seguir o título da página será **Título da Página**
```Markdown
# Título da Página

Conteúdo da página ....

```

#### Sidebars
O Conteúdo das sidebars estão nos arquivos **_right.md** e **_left.md**

#### Substituindo uma sidebar
Você pode substituir uma sidebar envolvendo o conteúdo que você deseja que substitua o conteúdo padrão com as tags **<%left left%>** para a sidebar da esquerda e **<%right right%>** para a sidebar da direita.

No exemplo a seguir substituímos a sidebar da direita por um menu com três links:
```Markdown
<%right 
- [Primeiro link](#primeiro)
- [Segundo link](#segundo)
- [Terceiro link](#terceiro)
right%>

# Título da Página

Conteúdo da página ....
```

#### Estendendo uma sidebar
Você pode extender uma sidebar, adicionando conteúdo antes ou depois do conteúdo padrão, colocando um **:after** ou **:before** logo depois da tag de abertura.

No exemplo a seguir estendemos a sidebar da esquerda adicionando um menu com 2 links no final da sidebar:
```Markdown
<%left:after
## submenu da página

- [Primeiro Link](#primeiro)
- [Segundo Link](#segundo)
left%>

# Título da Página

Conteúdo da página ....
```


### Layouts
O layout é a "moldura" do conteúdo de uma visão. A estrutura mínima de um layout é a seguinte:

```HTML+PHP
<html>
    <head>
        <title><?php echo isset($entity) ? $this->getTitle($entity) : $this->getTitle() ?></title>
        <?php mapasculturais_head(isset($entity) ? $entity : null); ?>
    </head>
    <body>
        <?php body_header(); ?>
        <?php echo $TEMPLATE_CONTENT; /* aqui entra o conteúdo da view */ ?>
        <?php body_footer(); ?>
    </body>
</html>
```

Por padrão as visões usam o arquivo de layout **default.php**, mas você pode definir qual layout elas usarão colocando a seguinte linha na primeira linha do seu arquivo de visão:
```PHP
$this->layout = 'nome-do-layout'; // não precisa do .php no nome do template
```

### Visões
As visões são chamadas de dentro das [actions do controller](#actions) através do [método render](#método-render), que inclui o [layout](#layouts) definido na view, ou do [método partial](#método-partial), que **não** inclui o layout. 

Quando a visão é chamada pelo método render, o conteúdo renderizado da visão é guardado na variável **$TEMPLATE_CONTENT** e enviado para o layout.

#### Visões das actions single, create e edit
Os arquivos de visão **single.php**, **create.php** e **edit.php** dos controladores **agent**, **space**, **event** e **project** são, na realidade, o mesmo arquivo. O arquivo *real* é o **single.php** e os dois outros são *links simbólicos* para o primeiro.

Para saber, de dentro de um destes arquivos, em qual action você está, você pode usar a propriedade **$this->controller->action**:

```HTML+PHP
<?php if($this->controller->action == 'single'): ?>
    <p>você está visualizando a entidade</p>
<?php elseif($this->controller->action == 'edit'): ?>
    <p>você está editando a entidade</p>
<?php else: ?>
    <p>você está criando uma nova entidade<p>
<?php endif; ?>
```

Se você só deseja saber se está no modo de edição use a função **is_editable()**:
```HTML+PHP
<?php if(is_editable(): ?>
    <p> você está em modo de edição (edit ou create). </p>
<?php else: ?>
    <p> você está somente visualizando a entidade. <p>
<?php endif; ?>
```

### Partes
As partes são blocos de código que podem ser incluídos em diferentes views, layouts ou mesmo dentro de outras partes. Estes blocos de código devem ficar, por padrão, na pasta **layouts/parts/** do tema.

Para usar uma parte cujo nome de arquivo é **uma-parte.php** basta chamar o método **part** da seguinte forma:

```HTML+PHP
<div> A parte será incluida a seguir: </div>
<?php $this->part('uma-parte'); ?>
```

#### Enviando variáveis para dentro das partes
Você pode enviar variáveis para usar dentro das partes. Isto é útil em várias situações, por exemplo quando você quer que uma parte seja usada dentro de um loop e você tem que enviar o item atual do loop para usar dentro da parte.

No exemplo a seguir, passamos uma variável chamada **user_name** com o valor **"Fulano de Tal"** para dentro da parte **uma-parte**.
```PHP
// dentro de algum arquivo de view, layout ou mesmo outra parte
$this->part('uma-parte', ['user_name' => 'Fulano de Tal']);
```

```HTML+PHP
<!-- dentro do arquivo layouts/parts/uma-parte.php -->
<span>Nome de usuário: <?php echo $user_name; ?></span>
```


### Assets
Os assets são arquivos estáticos (.css, .js, imagens, etc.) utilizados pelo tema. 

Para imprimir a url de um asset use a função **$this->asset()**. Já se você deseja adicionar um js ou css use as funções **$app->enqueueScript()** e **$app->enqueueStyle()**.

#### Método Asset
O Método **asset** do objeto de função serve para imprimir ou somente retornar a url de um asset. Este método aceita dois parâmetros: 

O primeiro, **$file**, é o caminho do arquivo deseja dentro da pasta assets do tema, como exemplo a string "img/uma-image.jpg".

O Segundo, **$print**, é opcional e tem como padrão *true*. Se for passado *false* a função somente retornará a url, mas não imprimirá nada.

##### Adicionando uma imagem
O exemplo a seguir usa uma imagem chamada **logo.png** que está na pasta **assets/img/** do tema.
```HTML+PHP
<img src="<?php $this->asset('img/logo.png'); ?>" />
```

##### Criando um link para um asset
O exemplo a seguir cria um link para o arquivo **documento.pdf** que está na pasta **asset/** do tema.
```HTML+PHP
<a href="<?php $this->asset('documento.pdf'); ?>" >Documento</a>
```

#### Método enqueueStyle
Este método é utilizado para adicionar arquivos .css que serão utilizados pela visão, layout ou parte. Este método aceitas 5 parâmetros (**$group**, **$script_name**, **$script_filename**, *array* **$dependences**, **$media**), sendo os dois últimos opcionais.

Há três grupos de estilos no sistema: **vendor**, que são estilos utilizados pelas bibliotecas, **fonts** que são as fontes utilizadas, e **app**, que são os estilos escritos exclusivamente para o tema. 

##### Adicionando um estilo
O exemplo a seguir adiciona um estilo chamado **um-estilo.css** escrito para a aplicação.

```PHP
$app->enqueueStyle('app', 'um-estilo', 'css/um-estilo.css');
```

#### Método enqueueScript
Este método é utilizado para adicionar arquivos .js que serão utilizados pela visão, layout ou parte. Este método aceitas 4 parâmetros (**$group**, **$script_name**, **$script_filename**, *array* **$dependences**), sendo o último opcional.

Há dois grupos de scripts no sistema: **vendor**, que são as bibliotecas utilizadas, e **app**, que são os scripts escritos exclusivamente para o tema. 

##### Adicionando um script
O exemplo a seguir adiciona um script chamado **um-script.js** escrito para a aplicação.

```PHP
$app->enqueueScript('app', 'um-script', 'js/um-script.js');
```

##### Adicionando um script que depende de outro
O exemplo a seguir adiciona uma biblioteca que depende de outra biblioteca.

```PHP
$app->enqueueScript('vendor', 'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.js', array('jquery'));
$app->enqueueScript('vendor', 'jquery', '/vendor/jquery/jquery-2.0.3.min.js');
```

#### Ordem de impressão das tags de estilos e scripts
Os grupos de estilos e scripts serão impressos na seguinte ordem e dentro dos grupos os estilos/scripts serão ordenados conforme suas dependências:
- Estilos do grupo **vendor**
- Estilos do grupo **font**
- Estilos do grupo **app**
- Scripts do grupo **vendor**
- Scripts do grupo **app**

### Variáveis Acessíveis
De dentro dos arquivos das visões (views, layouts e parts) as seguintes variáveis estão acessíveis:
- **$this** - instância da classe *MapasCulturais\View*.
    - **$this->assetUrl** - url dos assets.
    - **$this->baseUrl** - url da raiz do site.
    - **$this->controller** - o controller que mandou renderizar a visão.
    - **$this->controller->action** - a action que mandou renderizar a visão.
- **$app** - instância da classe *MapasCulturais\App*.
- **$app->user** - o usuário que está vendo o site. Este objeto é uma instância da classe *MapasCulturais\Entities\User* (se o usuário estiver logado), ou instância da classe *MapasCulturais\GuestUser*, se o usuário não estiver logado.
- **$app->user->profile** - o agente padrão do usuário. Instância da classe *MapasCulturais\Entities\Agent*. *(somente para usuários logados)*
- **$app->getCurrentSubsite()** - Se estiver utilizando o SaaS, retorna a instância do subsite atual
- **$entity** - é a entidade que está sendo visualizada, editada ou criada. *(somente para as actions single, edit e create dos controladores das entidades agent, space, project e event. Dentro das partes somente se esta foi [enviada](#enviando-variáveis-para-dentro-das-partes))*
- **$app->view** - instância da classe *MapasCulturais\Theme*, que por sua vez herda da classe Slim\View.
   É inicializado logo no bootstrap do `$app`, e podemos utilizá-lo também através do método `$app->getView()`.
   
   Este objeto é bastante útil no fluxo do desenvolvimento, pois podemos utilizar várias de suas propriedades para debugar e nos situarmos melhor no contexto em que estamos da aplicação, como:
    - `$app->getView()->_libVersions` -  Propriedade do tema padrão (BaseV1), mantém um array com os nomes e versões exatas das bibliotecas javascript que o tema adiciona e usa.
    - `$app->getView()->template` -  Retorna uma string identificando o template que está sendo renderizado naquele momento. Em geral padronizada para `"{controller}/{action}"`
    - `$app->getView()->getAssetManager()` - Nos traz uma instância de `MapasCulturais\App\FileSystem` contendo informações detalhadas sobre os scripts JS e estilos CSS que foram carregados naquela view através das propriedades `_enqueuedScripts` e `_enqueuedStyles`, respectivamente - inclusive separadas pelos grupos `app` (do próprio Mapas) e `vendor` (bibliotecas de terceiros).       
       A propriedade `config` ainda nos dá, dentre outras informações, o caminho completo do sistema para a pasta pública dos assets.
    - `$app->getView()->bodyClasses` - Traz informações sobre o controller e action da requisição, e são utilizadas no atributo `class` da tag HTML `body`, possibilitando um maior nível de customização do layout com base na view.
    - `$app->getView()->getTemplatesDirectory()` - Informa o path completo da pasta onde estão os templates carregados.
    - `$app->getView()->_dict` -> Exibe as strings internacionalizadas que foram carregadas para o tema


Outra propriedade bastante útil do objeto do tema é a `jsObject`, por sua vez uma instância de `ArrayObject`    .
Esta propriedade é manipulada diversas vezes ao longo do *lifecycle* da aplicação, de modo que seus dados são dinâmicos de acordo com a entidade em questão, além de manterem também chaves com o mesmo valor ao longo das rotas e requisições.

Por exemplo, as seguintes chaves mantém seus valores independentemente das entidades:
``` 
 $app->getView()->jsObject['baseURL']
 $app->getView()->jsObject['labels'] 
 $app->getView()->jsObject['mapsDefaults'] 
 $app->getView()->jsObject['routes'] 
 ```
 Já as chaves de *jsObject* `gettext`, `isEditable`, `isSearch`, `request`, `userProfile` e `entity` variam de acordo com o controller e entidade, tornando esse objeto ainda mais flexível para o desenvolvedor.
 
 Seguindo ainda com o objeto de view, podemos também fazer uso de informações do controller:
 
 - **$app->getView()->getController()** Retorna o controller da requisição atual, bem como a entidade correspondente ao mesmo (na propriedade *entityClassName*);
 - **$app->getView()->getRequestedEntity()** Traz o registro da entidade correspondente à resposta da requisição.
 
 Ao utilizarmos este método para uma requisição a `${URLBASE}/oportunidade/43` por exemplo, e esta oportunidade for vinculada a uma entidade Projeto, teremos a instância de id 43 de ProjectOpportunity,
 obviamente com todos seus registros salvos, como data de criação e atualização, tags, nome, descrição, metadados e owner (referente à outra instância de um objeto Agente), dentre outros.
 
 - **$app->getView()->getController()->getUrlData()** Retorna os parâmetros passados pela URL. Se foram mapeados pelo hook do $app (neste sentido, um hook do Slim Framework), vêm com o nome mapeado. Caso contrário, os parâmetros são trazidos num array em ordem crescente.
 
 Por exemplo, se mapearmos apenas o $id no hook, utilizando o método acima para a requisição `${URLBASE}/agente/1/outroParam/EmaisOutro/14`, teremos o seguinte retorno:
 ```
 array:4 [▼
   "id" => "1"
   0 => "outroParam"
   1 => "EmaisOutro"
   2 => "14"
 ]
 ```

- **$app->getView()->getController()->getRepository()** - Retorna o objeto repositório da entidade gerenciada pelo Doctrine correspondente àquele controller e view.
A diferença entre utilizar este método ou invocar diretamente `$app->repo(${nome-da-classe-da-entidade})` é que este último retorna o repositório da entidade passada por parâmetro, e não está atrelada ao contexto da requisição, tal qual o primeiro.
    - Além do nome da entidade gerenciada e do próprio entityManager do Doctrine, o objeto repositório traz os metadados da classe, incluindo detalhes como o namespace da entidade, todo o mapeamento que o Doctrine fez de cada atributo, os *callbacks* de lifecycle, nome da tabela correspondente e até mesmo detalhes sobre as constantes, métodos e propriedades. 

### Verificando se um usuário está logado
Para saber se um usuário está logado você pode verificar se o usuário não é *guest*. 
```HTML+PHP
<?php if( ! $app->user->is('guest') ): ?>
    <p>O usuário está logado e o nome do agente padrão dele é <?php echo $app->user->profile->name; ?> ?></p>
<?php else: ?>
    <p>O usuário não está logado</p>
<?php endif; ?>
```

## Exceções

### PermissionDenied

Guia do Desenvolvedor
=====================

- [Arquivo de Configuração](#arquivo-de-configuracao)
- [Model](#model)
- [Controller](#controller)
- [EntityController](#entitycontroller)
- [View](#view)
    - [Temas](#temas)
      - [theme.php](theme-php)
      - [Estrutura de pastas](#estrutura-de-pastas)
    - [Páginas](#Páginas)
    - [Layouts](#Layouts)
    - [Visões](#Visões)
    - [Partes](#Partes)
    - [Assets](#Assets)
    - [Variáveis Acessíveis](#variáveis-acessíveis)
    - [Verificando se um usuário está logado](#verificando-se-um-usuário-está-logado)
- [Autenticação]()
- [Log]()
- [Cache]()
- [Outputs da API]()


## Arquivo de Configuração

## Model


## Controller

### Actions
### Método render
### Método partial
### Retornando um JSON
### Requisitando autenticação
### Checando permissão



## EntityController

## View

### Temas
Por enquanto ainda não temos resolvida a estrutura para múltiplos temas. O que temos é um tema único dentro da pasta **src/protected/application/themes/active**, que será modificado para aceitar configurações.

#### theme.php
Este arquivo fica na pasta raíz do tema (**src/protected/application/themes/active**) e é usado para colocar funções helpers usadas dentro do tema e para estender o sistema utilizando a [API de plugins](api.md).

#### Estrutura de pastas
dentro da pasta raíz do tema
- **assets/** - *onde deve ficar tudo que é acessível pelo público dentro da url **/public** do site*
  - **css/**
  - **fonts/**
  - **img/**
  - **vendor/**
- **layouts/** - *onde ficam os layouts do site*
    - **parts/** - *onde ficam os template parts utilizados pelo tema*
- **views/** - *onde ficam as viões dos controles*
- **pages/** - onde ficam os arquivos de páginas

### Páginas
As páginas do sistema são arquivos **.md** (Markdown) salvos dentro da pasta **pages/** do tema. Para criar uma nova página basta criar um novo arquivo **.md** dentro desta pasta. Estes arquivos são renderizadas pela biblioteca [PHP Markdown Extra](https://michelf.ca/projects/php-markdown/extra/).

#### Url da página
Para uma página cujo nome de arquivo é **nome-da-pagina.md**, a url de acesso será **http://mapasculturais/page/site/nome-da-pagina/**


#### Título da página
O texto do **primeiro h1** do conteúdo da página será utilizado como título da página (tag <title>). Isto é feito via javascript.


No exemplo a seguir o título da página será **Título da Págna**
```Markdown
# Título da Página

Conteúdo da página ....

```

#### Sidebars
O Conteúdo das sidebars estão nos arquivos **_right.md** e **_left.md**

#### Substituindo uma sidebar
Você pode substituir uma sidebar envolvendo o conteúdo que você deseja que substitua o conteúdo padrão com as tags **<%left left%>** para a sidebar da esquerda e **<%right right%>** para a sidebar da direita.

No exemplo a seguir substituimos a sidebar da direita por um menu com três links:
```Markdown
<%right 
- [Primeiro link](#primeiro)
- [Segundo link](#segundo)
- [Terceiro link](#terceiro)
right%>

# Título da Página

Conteúdo da página ....
```

#### Extendendo uma sidebar
Você pode extender uma sidebar, adicionando conteúdo antes ou depois do conteúdo padrão, colocando um **:after** ou **:before** logo depois da tag de abertura.

No exemplo a seguir extendemos a sidebar da esquerda adicionando um menu com 2 links no final da sidebar:
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
Os arquivos de visão **single.php**, **create.php** e **edit.php** dos controladores **agent**, **space**, **event** e **project** são, não relidade, o mesmo arquivo. O arquivo *real* é o **single.php** e os dois outros são *links simbólicos* para o primeiro.

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
As partes são blocos de código que podem ser incluidos em diferentes views, layouts ou mesmo dentro de outras partes. Estes blocos de código devem ficar, por padrão, na pasta **layouts/parts/** do tema.

Para usar uma parte cujo nome de arquivo é **uma-parte.php** basta chamar o método **part** da seguinte forma:

```HTML+PHP
<div> A parte será incluida a seguir: </div>
<?php $this->part('uma-parte'); ?>
```

#### Enviando variáveis para dentro das partes
Você pode enviar variáveis para usar dentro das partes. Isto é útil em várias situações, por exmplo quando você quer que uma parte seja usada dentro de um loop e você tem que enviar o item atual do loop para usar dentro da parte.

No exemplo a seguir, passamos uma variável chamada **user_name**, com o valor **"Fulano de Tal"**, para dentro da parte **uma-parte**.
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
Este método é utilizado para adicionar arquivos .css que serão utilizados pela visão, layout ou parte. Este método aceitas 5 parâmetros (**$group**, **$script_name**, **$script_filename**, *array* **$dependences**, **$media**), sendo os dois último opcional.

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
    - **$this->baseUrl** - url da raíz do site.
    - **$this->controller** - o controller que mandou renderizar a visão.
    - **$this->controller->action** - a action que mandou renderizar a visão.
- **$app** - instância da classe *MapasCulturais\App*.
- **$app->user** - o usuário que estã vendo o site. Este objeto é instância da classe *MapasCulturais\Entities\User*, se o usuário estiver logado, ou instância da classe *MapasCulturais\GuestUser*, se o usuário não estiver logado.
- **$app->user->profile** - o agente padrão do usuário. Instância da classe *MapasCulturais\Entities\Agent*. *(somente para usuários logados)*
- **$entity** - é a entidade que está sendo visualizada, editada ou criada. *(somente para as actions single, edit e create dos controladores das entidades agent, space, project e event. Dentro das partes somente se esta foi [enviada](#enviando-variáveis-para-dentro-das-partes))*

### Verificando se um usuário está logado
Para saber se um usuário está logado você pode verificar se o usuário não é *guest*. 
```HTML+PHP
<?php if( ! $app->user->is('guest') ): ?>
    <p>O usuário está logado e o nome do agente padrão dele é <?php echo $app->user->profile->name; ?> ?></p>
<?php else: ?>
    <p>O usuário não está logado</p>
<?php endif; ?>
```



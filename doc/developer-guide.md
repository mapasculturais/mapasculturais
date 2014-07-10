Guia do Desenvolvedor
=====================

- [Model](#model)
- [Controller](#controller)
- [EntityController](#entitycontroller)
- [View](#view)
    - [Temas](#temas)
      - [theme.php](theme-php)
      - [Estrutura de pastas](#estrutura-de-pastas)
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

Por padrão as visões usam o arquivo de layout **default.php**, mas você você pode definir qual layout elas usarão colocando a seguinte linha na primeira linha do seu arquivo de visão:
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

#### Enviando variáveis para dentro das partes
Você pode enviar variáveis para usar dentro das partes. Isto é útil em várias situações, por exmplo quando você quer que uma parte seja usada dentro de um loop e você tem que enviar o item atual do loop para usar dentro da parte.

No exemplo a seguir, passamos uma variável chamada **user_name**, com o valor **"Fulano de Tal"**, para dentro da parte **uma-parte**.
```PHP
// dentro de algum arquivo de view, layout ou mesmo outra parte
$this->part('uma-parte', ['user_name' => 'Fulano de Tal']);
```

```HTML+PHP
// dentro do arquivo layouts/parts/nome-da-parte.php
<?php echo $user_name; /* será impresso "Fulano de Tal" */?>
```


### Assets

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



# SaaS - Software As A Service

O SaaS é uma formato de distribuição de um serviço que é um software em grandes escalas.
Tem objetivo de otimizar a entrega de um determinado software e facilitar a sua gestão para os solicitantes do serviço/software.
No caso do software Mapas Culturais é disponibilizar uma forma fácil e rápida de criar novas instalações a partir de uma instalação física do software.
A instalação física será a principal que será utilizada para gerenciar todas as sub-instalações criadas via SaaS.

## Configuração
Será necessário alterar o arquivo de configuração da instalação ```config.php``` incluindo as namespaces os temas padrões que serão utilizados na instalação com SaaS:
```
'namespaces' => array(
  'MapasCulturais\Themes' => THEMES_PATH,
  'BaseMinc' => THEMES_PATH . '/mapasculturais-baseminc/', // Tema padrão que será utilizado quando não for acessada nenhuma instalação SaaS
  'Subsite' => THEMES_PATH . '/Subsite/', //Tema do SaaS que utilizará as informações cadastradas via entidade SubSite
  $theme_namespace => $theme_path
  ),
```

## Perfis
A funcionalidade do SaaS demandou a criação de alguns perfis de usuário que obterão acesso as funcionalidades de gestão das instalações de Mapas Culturais no sistema.

Ver documentação de [Perfis de usuário](mc_user_profile.md).

## Aba 'Filtros' da Instalação
Nesta seção são definidos que serão aplicados no Mapa onde são exibidas a geolocalização das informações relacionadas numa instalação. São definidas as tipologias que serão exibidas e os filtros da entidade espaço, eventos e quais selos certificadores serão exibidos no mapa de localização.

## Aba 'Textos' da Instalação
Nesta seção é possível alterar determinados textos que são exibidos em toda a plataforma, sendo possível customizar termos de acordo com o contexto da instalação.
Os seguintes campos de textos do site aceitam sintaxe Markdown:
* Título de Boas Vindas
* Texto de Boas Vindas
* Home de Agentes
* Home de Espaços
* Home de Eventos
* Home de Projetos
* Home de Desenvolvedores
* Página Sobre
* Página Como usar
E aceitam as seguintes tags para formatação do conteúdo:
**Títulos**

```
# Título 1
```
Será exibido como:
# Título 1

Assim como:
```
## Título 2
```
Será exibido como:
## Título 2

```
### Título 3
```
Será exibido como:
### Título 3

**Negrito e Ítalico (Ênfase)**
Para formato um texto no sentido de dar ênfase é possível formatar o texto com o estilo **negrito**, _itálico_ ou os dois:

Para colocar o texto em negrito:
```
Deixando **Negrito** uma palavra do texto
```
Será exibida desta forma:
'Deixando **Negrito** uma palavra do texto'
O mesmo acontece para o itálico, para colocar o texto em negrito:
```
Deixando _itálico_ uma palavra do texto
```
Será exibida desta forma:
'Deixando _itálico_ uma palavra do texto'

**Imagens**
Para incluir imagens, a sintaxe é a seguinte:
```
![img](http://url/imagem.jpg)
```
Será exibida da seguinte forma:
![Minha Imagem](https://raw.githubusercontent.com/hacklabr/mapasculturais/master/src/protected/application/themes/BaseV1/assets/img/agrupador-agente.png)

Para formatar a imagem, é possível adicionar uma classe CSS:
```
![img](http://url/imagem.jpg){#id .classe-da-imagem .segunda-classe-da-imagem}
```

**Links**
Para incluir links, a sintaxe é a seguinte:
```
[Texto do Link](http://meu.link.com "Texto do Alt do Link")
```
Será exibida da seguinte forma:
[Texto do Link](http://meu.link.com "Texto do Alt do Link")

**Textos da Instalação Principal e/ou SaaS**
É possível através do Markdown pegar textos do tema que está sendo utilizado:
Por exemplo, aqui temos o texto dos _Resultados Verificados_:
```
{{dict: search: verified results}}
```
Se o termo estiver usando outra linguagem, termos ou idioma, a sintaxe é a mesma, mesmo se o tema mudar.

**Imagens e Arquivos da Instalação Principal**
Para utilizar arquivos ou imagens que estão localizados nas pastas `assets/` do servidor do tema utiliza a seguinte sintaxe:
```
{{asset:mapasculturais_manual.odp}}
```

**Imagens e Arquivos da Instalação SaaS**
É possível utilizar arquivos e imagens relacionados a uma instalação SaaS através da seção de downloads. O upload dos arquivos deve ser feito e será possível utilizar para exibir imagens ou criar links no Markdown:
Deve utilizar o nome que é dado ao download ao incluí-lo para pposteriormente utilizar no markdown.
```
{{downloads:mapasculturais_manual.odp}}
```


## Aba 'Entidade' da Instalação
Nesta seção da configuração do subsite é onde são definidas as cores que são exibidas em algumas partes do sistema:
### Introdução
Seção exibida na página principal do sistema com narrativa do propósito da plataforma.
### Desenvolvedores
Seção exibida na página principal do sistema com narrativa sobre a colaboração que pode ser feita a plataforma por desenvolvedores na comunidade da aplicação.
### Entidades
É o onde são definidas quais entidades (Agente/Espaço/Evento/Projetos/Selos) estarão disponíveis na instalação Saas, e é possível definir a cor tema que será definida para a entidade em todas as partes do site relacionada a entidade.

## Aba 'Imagens' da Instalação
Nesta seção é onde é possível definir as imagens que são utilizadas na instalação:
### Background
Imagem com fundo transparente que é exibida na página principal do Mapas Culturais.
### Logo
Imagem logomarca da instalação Mapas Culturais, exibida no lado esquerdo superior da página.
### Logo da Instituição
Imagem logomarca da organização envolvida na instalação do Mapas Culturais, exibida no lado direito superior da página.


## Aba 'Mapa' da Instalação
Define-se a posição geografica central da instalação que onde o Mapa será exibido e o perímetro deste ponto central definido que a instalação vai abranger no Mapa.

## Aba 'Login Cidadão' da Instalação
Nesta seção são especificadas o ID e Token para a url da instalação utilizar a autenticação do sistema com a interface do Login Cidadão.

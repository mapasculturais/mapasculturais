#SaaS - Software As A Software

O SaaS é uma formato de distribuição de um serviço que é um software em grandes escalas.
Tem objetivo de otimizar a entrega de um determinado software e facilitar a sua gestão para os solicitantes do serviço/software.
No caso do software Mapas Culturais é disponibilizar uma forma fácil e rápida de criar novas instalações a partir de uma instalação física do software.
As instalação física será a principal que será utilizada para gerenciar todas as sub-instalações criadas via SaaS.

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

### [Super Administrador SaaS](https://github.com/hacklabr/mapasculturais/blob/devel-SaaS/documentation/docs/mc_user_profiles.md#saas-super-administrador)

### [Administrador Saas](https://github.com/hacklabr/mapasculturais/blob/devel-SaaS/documentation/docs/mc_user_profiles.md#saas-administrador)

## Aba 'Filtros' da Instalação
Nesta seção são definidos que serão aplicados no Mapa onde são exibidas a geolocalização das informações relacionadas numa instalação. São definidas as tipologias que serão exibidas e os filtros da entidade espaço, eventos e quais selos certificadores serão exibidos no mapa de localização.

## Aba 'Textos' da Instalação
Nesta seção é possível alterar determinados textos que são exibidos em toda a plataforma, sendo possível customizar termos de acordo com o contexto da instalação.

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

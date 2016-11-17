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
  'BaseMinc' => THEMES_PATH . '/mapasculturais-baseminc/',
  'Subsite' => THEMES_PATH . '/Subsite/',
  $theme_namespace => $theme_path
  ),
```

## Perfis
A funcionalidade do SaaS demandou a criação de alguns perfis de usuário que obterão acesso as funcionalidades de gestão das instalações de Mapas Culturais no sistema.

### Super Administrador SaaS

### Administrador SaaS

## Entidade SubSite

### Aba 'Sobre'

## Permissões

## Imagens da Instalação

## Textos das Instalação

## Mapa

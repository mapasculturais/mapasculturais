# Componente `<main-footer>`

## Descrição
O componente main-footer é um componente Vue.js que exibe o rodapé principal de um aplicativo, contendo várias seções como suporte, links de navegação e ícones de redes sociais.

## Props
- Este componente não aceita props.

## Setup
No método setup, duas variáveis são retornadas:

- Textos utilizados no rodapé, obtidos da função Utils.getTexts.

- globalState: Estado global da aplicação, obtido através da função useGlobalState.

## Hooks de Template
O template é responsável pela estrutura do HTML do rodapé e utiliza várias hooks e condicionais para exibir conteúdo dinâmico.

- main-footer: Usado para adicionar conteúdo antes, no início, e depois do rodapé principal.

- main-footer-logo: Usado para adicionar conteúdo antes e depois da seção do logotipo do rodapé.

- main-footer-links: Usado para adicionar conteúdo antes, no início, e depois dos links do rodapé.

- main-footer-reg: Usado para adicionar conteúdo antes, no início, e depois da seção de registro do rodapé.

## Condicionais de Exibição
- v-if="globalState.visibleFooter": Condicional para exibir o rodapé apenas se visibleFooter no estado global estiver definido como true.

- v-if="global.enabledEntities.*": Condicionais para exibir diferentes seções de links e informações com base em entidades habilitadas no estado global (opportunities, events, agents, spaces, projects).

- v-if="!($app->user->is('guest'))": Condicional para exibir o link de logout apenas se o usuário não for um convidado.

## Dependências
- Utils.getTexts: Função para obter textos específicos para o rodapé.

- useGlobalState: Função para acessar o estado global da aplicação.

- $TEMPLATES: Objeto contendo templates utilizados no componente.

- applyTemplateHook: Função para aplicar hooks de template específicos.

- part: Função para renderizar partes específicas do template.

- mc-icon: Componente para exibir ícones personalizados.

# Importando o componente
```PHP
<?php 
$this->import('main-footer');
?>
```
# Exemplo de Uso 
```HTML
você pode incluir o componente main-footer diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<main-footer><main-footer>
```


# Componente `<mc-breadcrumb>`
O componente mc-breadcrumb exibe um breadcrumb de navegação, ajudando os usuários a entenderem sua localização atual dentro da hierarquia do site.

### List
- list: Uma lista de itens do breadcrumb, extraída da variável global $MAPAS.breadcrumb.
- Tipo: Array
- Exemplo de estrutura: [ { label: 'Home', url: '/' }, { label: 'Seção', url: '/section' }, { label: 'Subseção', url: '/section/subsection' } ]

### Cover
- cover: Um booleano que indica se a entidade solicitada possui um cabeçalho com imagem.
- Tipo: Boolean
- Valor inicial: !!$MAPAS.requestedEntity?.files?.header

## Importando o componente
```PHP
<?php 
$this->import('mc-breadcrumb');
?>
```

# Exemplo de Uso

<!-- utilizaçao básica -->

```HTML
você pode incluir o componente mc-breadcrumb diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<mc-breadcrumb></mc-breadcrumb>
```
### Observações

- O componente utiliza a variável global $MAPAS.breadcrumb para obter a lista de itens do breadcrumb.
- A classe mc-breadcrumb__hasCover é aplicada ao nav se a entidade solicitada possuir uma imagem de cabeçalho.
- Este componente usa vários hooks de template (applyTemplateHook) para permitir personalizações antes e depois da lista de breadcrumb.

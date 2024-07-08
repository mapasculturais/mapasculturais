# Componente `<mc-avatar>`

## Descrição
O componente mc-avatar exibe um avatar de uma entidade, que pode ser uma imagem ou um ícone.

## Props

### entity:

-  entity: (Obrigatório) Um objeto que representa a entidade, contendo as informações necessárias para obter a imagem do avatar.

- Tipo: Entity | Object

- Exemplo de objeto esperado: { avatar: { avatarBig: { url: 'path/to/big/image' }, avatarMedium: { url: 'path/to/medium/image' }, avatarSmall: { url: 'path/to/small/image' } }, files: { avatar: { transformations: { avatarBig: { url: 'path/to/transformed/big/image' }, avatarMedium: { url: 'path/to/transformed/medium/image' }, avatarSmall: { url: 'path/to/transformed/small/image' } } } } }

### size:

- size: (Opcional) O tamanho do avatar a ser exibido.

- Tipo: String
- Valores permitidos: 'big', 'medium', 'small', 'xsmall'
- Padrão: 'medium'
- Validator: Valida se o valor está entre os tamanhos permitidos.

### square:

- square: (Opcional) Define se o avatar terá forma quadrada.
- Tipo: Boolean
- Padrão: false

## Computed Properties:
- classes: Retorna as classes CSS a serem aplicadas ao componente baseando-se no tamanho do avatar, se é um ícone e se é quadrado.

- Exemplo de retorno: ['mc-avatar--medium', { 'mc-avatar--icon': false }, { 'mc-avatar--square': false }]

- image: Retorna a URL da imagem do avatar com base na entidade e nas transformações aplicadas.

- Exemplo de retorno: 'path/to/medium/image'

## Métodos:
- Atualmente, o componente não possui métodos definidos.

## Importando o componente
```PHP
<?php 
$this->import('mc-avatar');
?>
```
# Exemplo de Uso

<!-- utilizaçao básica -->

```HTML
você pode incluir o componente mc-avatar diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<mc-avatar :entity="entityObject" size="big" :square="true"></mc-avatar>
```
## Observações:
- O componente utiliza a prop entity para determinar a URL da imagem do avatar.
- Se a entidade não possuir uma imagem de avatar, um ícone é exibido em seu lugar.
- O tamanho do avatar pode ser personalizado através da prop size, e a forma quadrada pode ser ativada com a prop square.
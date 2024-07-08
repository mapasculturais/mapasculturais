# Componente `<mc-card>`
O componente mc-card é um contêiner flexível que pode ser usado para agrupar diversos tipos de conteúdo, oferecendo slots para personalizar o título e o conteúdo principal.

## Propriedades:

### Tag:
- tag: Define a tag HTML a ser usada como contêiner principal do componente.
- Tipo: String
- Padrão: 'article'

### Classes:
- classes: Permite adicionar classes CSS personalizadas ao componente para estilização.
- Tipo: [String, Array, Object].
- Obrigatório: Não.

## Métodos:

- hasSlot: Verifica se um slot específico foi fornecido.
- Parâmetro: name (tipo String) - O nome do slot a ser verificado.
- Retorno: Boolean - true se o slot estiver presente, false caso contrário.

## Slots:
- title: Slot para o título do cartão. Será renderizado dentro de um ``` <header> ``` com a classe mc-card__title.

- content: Slot para o conteúdo principal do cartão. Será renderizado dentro de um 
 ```<main> ```

## Importando o componente
```PHP
<?php 
$this->import('mc-card');
?>
```
# Exemplo de Uso

<!-- utilizaçao básica -->

```HTML
você pode incluir o componente mc-card diretamente no template do seu aplicativo, onde deseja que ele seja renderizado.

<mc-card></mc-card>
```
## Exemplo:
```HTML
<mc-card :tag="'section'" :classes="['custom-card', 'another-class']">
    <template v-slot:title>
        <h1>Título do Cartão</h1>
    </template>
    <template v-slot:content>
        <p>Este é o conteúdo principal do cartão. Pode incluir texto, imagens, ou qualquer outro conteúdo HTML.</p>
    </template>
</mc-card>
```
## Observações:
- O componente utiliza a propriedade tag para permitir a flexibilidade de escolher qual elemento HTML será usado como contêiner principal.
- As classes CSS podem ser passadas como string, array ou objeto para uma estilização personalizada.
- O uso de slots permite a inserção de conteúdo dinâmico e flexível, tornando o componente mc-card altamente reutilizável e adaptável a diferentes necessidades.



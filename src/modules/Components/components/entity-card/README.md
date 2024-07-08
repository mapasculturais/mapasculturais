# Componente `<entity-card>`
Lista informações sobre uma entidade no card

## Descrição
O componente entity-card exibe informações sobre uma entidade (como usuário, espaço, oportunidade, etc.) em um cartão. Ele pode ser configurado para mostrar diferentes detalhes da entidade e permite personalização através de slots e propriedades.

## Propriedades
- class: Define a(s) classe(s) CSS do componente. Pode ser uma string, objeto ou array. Padrão: ''.
- entity: Instância da entidade a ser exibida. Requerido.
- portrait: Define se o layout é de retrato. Booleano. Padrão: false.
- sliceDescription: Define se a descrição deve ser cortada. Booleano. Padrão: false.
- tag: Define a tag HTML usada para o título. String. Padrão: 'h2'.

## Computed
- classes: Retorna as classes CSS aplicadas ao componente, combinando a classe passada pela prop class e a classe 'portrait' se portrait for true.
- showShortDescription: Retorna uma descrição curta da entidade, truncando-a se for maior que 400 caracteres.
- seals: Retorna os dois primeiros selos da entidade ou false se não houver selos.
- areas: Retorna uma string com as áreas da entidade, separadas por vírgulas, ou false se não houver áreas.
- tags: Retorna uma string com as tags da entidade, separadas por vírgulas, ou false se não houver tags.
- linguagens: Retorna uma string com as linguagens da entidade, separadas por vírgulas, ou false se não houver linguagens.
- openSubscriptions: Retorna true se a entidade for uma oportunidade e as inscrições estiverem abertas.
- useLabels: Retorna true se houver inscrições abertas ou se houver um slot de labels.

## Slots
- avatar: Slot para customizar o avatar da entidade.
- title: Slot para customizar o título da entidade.
- type: Slot para customizar o tipo da entidade.
- labels: Slot para customizar os labels da entidade.

## Métodos
- slice(text, qtdChars): Trunca o texto passado para o número de caracteres especificado, garantindo que a última palavra não seja cortada pela metade. Adiciona '...' ao final do texto truncado.

## Dependências
- Utiliza componentes mc-avatar, mc-icon, e mc-title.
- Os textos são localizados no arquivo texts.php e são recuperados pela função Utils.getTexts.

### Importando componente
```PHP
<?php 
$this->import('entity-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-card :entity="entity"></entity-card>

```
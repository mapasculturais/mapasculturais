# Componente `<mc-title>`
Cria um elemento para o título, adicionando classes modificadoras para 
títulos muito longos ou curtos

## Propriedades
- *String **tag*** = 'h2' - Tag que será usada para o elemento do título (h1, h2, h3 ou h4)
- *String **size*** = 'medium' - Tamanho do título (big, medium ou small)
- *Number **longLength*** = 30 - Comprimento mínimo para ser considerado um título longo
- *Number **shortLength*** = 20 - Comprimento máximo para ser considerado um título curto

## Slots
- **default** : Texto do título

### Importando componente
```PHP
<?php 
$this->import('mc-title');
?>
```
### Exemplos de uso
```HTML
<!-- título curto recebe uma classe mc-title--short -->
<mc-title>Título curto</mc-title>

<!-- titulo usando a propriedade tag -->
<mc-title tag="h2"></mc-title>

<!-- titulo usando a propriedade size -->
<mc-title size="big"></mc-title>

<!-- propriedade que adiciona estilo de acordo com o cumprimento definido -->
<mc-title shortLength="40">Título médio, que </mc-title>

<!-- propriedade que adiciona estilo de acordo com o cumprimento definido -->
<mc-title longLength="100">Título médio, que </mc-title>

<!-- modifica o elemento do título -->
<mc-title tag='h1'>Título curto</mc-title>

<!-- modifica o tamanho do título -->
<mc-title tag='h1' size="big">Título curto</mc-title>
```
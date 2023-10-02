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

<!-- título médio não recebe nenhuma classe modificadora -->
<mc-title>Título médio, que </mc-title>

<!-- título longo recebe uma classe mc-title--long -->
<mc-title>Título muito longo que receberá uma classe modificadora mc-title--long</mc-title>

<!-- modifica o elemento do título -->
<mc-title tag='h1'>Título curto</mc-title>

<!-- modifica o tamanho do título -->
<mc-title tag='h1' size="big">Título curto</mc-title>
```
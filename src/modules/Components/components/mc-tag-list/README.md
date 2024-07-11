# Componente `<mc-tag-list>`
O componente `mc-tag-list` exibe uma lista de tags, permitindo a remoção das mesmas quando em modo editável.
  
## Propriedades
- *Boolean **editable** = false* - Habilita o modo de edição do componente;
- *String **classes*** - Classes a serem aplicadas nos itens listados (`<li>`)
- *Array/Object **tags*** - array/objeto com as tags a serem listadas;
- *Array/Object **labels*** - array/objeto com as labels das tags (opcional em casos de pesquisa por ID)

### Importando componente
```PHP
<?php 
$this->import('mc-tag-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags -->
<mc-tag-list :tags="listaTags"></mc-tag-list>

<!-- utilizaçao com modo de edição -->
<mc-tag-list :tags="listaTags" editable></mc-tag-list>

<!-- utilizaçao com substituição das labels -->
<mc-tag-list :tags="listaTags" :labels="listaTagsLabels"></mc-tag-list>

<!-- utilizaçao com substituição das labels -->
<mc-tag-list :tags="listaTags" classes="item_classe"></mc-tag-list>

```

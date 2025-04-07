# Componente `<mc-tag-list>`
Componente para exibição e edição dos termos das taxonomias de uma entidade. 
  
### Eventos
- **open** :Emitido quando o popover é aberto. Passa o componente do popover como argumento.
- **close** :Emitido quando o popover é fechado. Passa o componente do popover como argumento.
- **selected** :Emitido quando um item é selecionado. Passa a chave do item selecionado como argumento.
- **removed** :Emitido quando um item é removido. Passa a chave do item removido como argumento.

## Propriedades
- *String **title*** - Título da modal/popover;
- *Boolean **editable** = false* - Habilita o modo de edição do componente;
- *Array/Object **items*** - Itens a serem listados;
- *Array **model*** - Array onde serão armazenados os itens selecionados;
- *Boolean **closeOnSelect** = true* - Fecha ao clicar no botão 'confirmar';
- *Boolean **hideFilter** = false* - Esconde campo de filtragem interna;
- *Boolean **hideButton** = false* - Esconde o botão 'confirmar';
- *Number **maxOptions** = true* - Limita a quantidade de opções';

### Importando componente
```PHP
<?php 
$this->import('mc-tag-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao básica para listagem das tags, exemplo na edição do evento.-->
<mc-multiselect :model="arrayModel" :items="items" #default="{popover, setFilter}">
    <input @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os itens: ') ?>">
</mc-multiselect>

<!-- utilizaçao para listagem das tags, exemplo na edição do evento, escondendo filtro e botão-->
<mc-multiselect :model="arrayModel" :items="items" #default="{popover, setFilter}" hide-filter hide-button>
    <input @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os itens: ') ?>">
</mc-multiselect>

```

# Componente `<files-list>`
Mostra os links para download
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **group*** - Grupo de arquivos
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('files-list');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao simples -->
<entity-files-list :entity="entity" group="downloads" title="Arquivos para download"></entity-files-list>

<!-- utilizaçao no modo de edição -->
<entity-files-list :entity="entity" group="downloads" title="Arquivos para download" editable></entity-files-list>

<!-- utilizaçao com classes costumizadas -->
<entity-files-list :entity="entity" group="downloads" title="Arquivos para download" classes="classe-unica"></entity-files-list>

<entity-files-list :entity="entity" group="downloads" title="Arquivos para download" :classes="['classe-um', 'classe-dois']"></entity-files-list>
```

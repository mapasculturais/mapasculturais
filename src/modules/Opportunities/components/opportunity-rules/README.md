# Componente `<opportunity-rules>`
Mostra o regulamento da oportunidade
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **title*** - Título do componente
- *Boolean **editable** = false* - Modo de edição do componente
- *String/Array/Object **classes*** - Classes a serem aplicadas no componente

### Importando componente
```PHP
<?php 
$this->import('opportunity-rules');
?>
```
### Exemplos de uso
```PHP
<!-- utilizaçao simples -->
<opportunity-rules :entity="entity" title="Arquivos para download"></opportunity-rules>

<!-- utilizaçao no modo de edição -->
<opportunity-rules :entity="entity" title="Arquivos para download" editable></opportunity-rules>

<!-- utilizaçao com classes costumizadas -->
<opportunity-rules :entity="entity" title="Arquivos para download" classes="classe-unica"></opportunity-rules>

<opportunity-rules :entity="entity" title="Arquivos para download" :classes="['classe-um', 'classe-dois']"></opportunity-rules>
```

# Componente `<v1-embed-tool>`
Componente para adicionar telas do BaseV1 no BaseV2
  
## Propriedades
- *String **route*** - Rota do embed
- *String **id*** - id da entidade
- *String **hash*** - hash
- *String **max-height*** - altura máxima do iframe
- *String **min-height*** - altura mínima do iframe
- *String **height*** - altura do iframe

### Importando componente
```PHP
<?php 
$this->import('v1-embed-tool');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<v1-embed-tool route="formbuilder" :id="opportunity.id"></v1-embed-tool>
```
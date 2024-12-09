# Componente `<mc-export>`
Componentes para exportação de planilhas nos formatos csv, xlsx e ods

## Propriedades
- *Entity **entity** = null* - Entidade
- *String **endpoint*** - Endpoint para exportação
- *Object **params*** - Parâmetros a serem enviados para o endpoint
- *String **group*** - Grupo de arquivos na entidade
- *Boolean **showExportedFiles** = false* - Exibe os últimos arquivos exportados da entidade no grupo indicado

### Importando componente
```PHP
<?php 
$this->import('mc-export');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-export-spreadsheet :entity="entity" endpoint="entities" :params="{entityType: 'opportunity'}" group="entities-spreadsheets"></mc-export-spreadsheet>

```
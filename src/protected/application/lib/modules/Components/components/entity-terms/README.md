# Componente `<entity-terms>`
Mostra os termos da entidade,
  
## Propriedades
- **entity**: *Entity* - Entidade
- **taxonomy**: *String* - Taxonomia do termo
- **title**: *String* (opcional) - Label do elemento

### Importando componente
```PHP
<?php 
$this->import('entities entity-terms');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica para listagem das tags -->
<entities type="agent" select="id, name, terms" #default="{entities}">
    <div v-for="entity in entities">
        <entity-terms :entity="entity" taxonomy="tag" title="Tags" >
        </entity-terms>        
    </div>
</entities>

<!-- utilizaçao básica para listagem das areas -->
<entities type="agent" select="id, name, terms" #default="{entities}">
    <div v-for="entity in entities">
        <entity-terms :entity="entity" taxonomy="area" title="Áreas de atuação" >
        </entity-terms>        
    </div>
</entities>
```
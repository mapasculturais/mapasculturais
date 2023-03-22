# Componente `<opportunity-phases-config>`
Componente da aba de configuração das fases da oportunidade

### Eventos
- **newPhase** - emitido quando uma nova fase, seja de coleta de dados ou de avaliação é criada
- **newDataCollectionPhase** - emitido quando uma nova fase de coleta de dados é criada 
- **newEvaluationPhase** - emitido quando uma nova fase de avaliação é criada 
  
## Propriedades
- *Entity **entity*** - a oportunidade

### Importando componente
```PHP
<?php 
$this->import('opportunity-phases-config');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<opportunity-phases-config :entity="entity"></opportunity-phases-config>
```
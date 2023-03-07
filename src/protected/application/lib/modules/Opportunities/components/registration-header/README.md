# Componente `<registration-header>`
Header da tela de inscrições
  
## Propriedades
- *Entity **registration*** - Entidade

### Importando componente
```PHP
<?php 
$this->import('registration-header');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<registration-header :registration="registration"></registration-header>

<registration-header :registration="registration">
    <template #button="{ historyBack }">
        <button @click="historyBack"></button>
    </template>
</registration-header>

```
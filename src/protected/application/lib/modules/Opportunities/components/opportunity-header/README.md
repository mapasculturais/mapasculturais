# Componente `<opportunity-header>`
Header da tela de inscrições
  
## Propriedades
- *Entity **registration*** - Entidade

## Slots
- **button** *opctional* - Botão de voltar página

### Importando componente
```PHP
<?php 
$this->import('opportunity-header');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<opportunity-header :registration="registration"></opportunity-header>

<opportunity-header :registration="registration">
    <template #button="{ historyBack }">
        <button @click="historyBack"></button>
    </template>
</opportunity-header>

```
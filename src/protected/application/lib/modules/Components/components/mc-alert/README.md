# Componente `<mc-alert>`
Componente para mensagens de alerta

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *String **type*** - Tipo do alert (success, helper, warning)
- *Boolean **state** = true* - estado do alert (true = mostrar, false = esconder)

## Slots
- **default** - Texto que será exibido no alert

### Importando componente
```PHP
<?php 
$this->import('mc-alert');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-alert type="helper">
    <?= i::__('Texto que será exibido dentro do alert') ?>
</mc-alert>

```
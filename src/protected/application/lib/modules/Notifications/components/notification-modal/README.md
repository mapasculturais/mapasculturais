# Componente `<notification-modal>`
Componente de notificacao de modal
  
## Propriedades
- *String **mediaQuery***
- *String **typeStyle***
### Importando componente
```PHP
<?php 
$this->import('notification-modal');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<notification-modal media-query="<?= $media_query ?>" #default="{modal}">
    <a @click="modal.open"><?= i::__('Notificações') ?></a>
</notification-modal>

```
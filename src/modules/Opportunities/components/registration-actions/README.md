# Componente `<registration-actions>`

Componente de ações em uma inscrição
  
## Propriedades

- *Entity **entity*** - Inscrição
- *Entity[]* **steps** - Lista de etapas
- *Number* **stepIndex** - Índice da etapa atual

## Eventos

- **previousStep** - Disparado quando o usuário solicita a etapa anterior
- **nextStep** - Disparado quando o usuário solicita a próxima etapa

### Importando componente

```php
<?php 
$this->import('registration-actions');
?>
```

### Exemplos de uso

```html
<!-- utilizaçao básica -->
<registration-actions :entity="entity"></registration-actions>

```

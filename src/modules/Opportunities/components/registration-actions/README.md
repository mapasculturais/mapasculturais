# Componente `<registration-actions>`

Componente de ações em uma inscrição
  
## Propriedades

- *Entity **entity*** - Inscrição
- *Entity[]* **steps** - Lista de etapas
- *Number* **stepIndex** - Índice da etapa atual

## Eventos

- **update:stepIndex** - Disparado quando o usuário solicita a mudança de etapa

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

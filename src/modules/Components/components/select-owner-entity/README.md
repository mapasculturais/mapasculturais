# Componente `<select-owner-entity>`

Formulário de seleção da `ownerEntity` de uma entidade (e.g. oportunidade)
  
## Propriedades

- *Entity **entity*** - Entidade
- *String **title*** - Rótulo do campo
- *String[] **types*** - Tipos de entidade selecionáveis (padrão: todos)

### Importando componente

```php
<?php 
$this->import('select-owner-entity');
?>
```

### Exemplos de uso

```php
<select-owner-entity :entity="entity" :types="['agent', 'event', 'project', 'space']" title="Vincule a oportunidade a uma entidade:"></select-owner-entity>
```

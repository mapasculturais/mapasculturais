# Componente `<entity-renew-lock>`

O componente `entity-renew-lock` é utilizado para renovar o bloqueio de edição de uma entidade, enviando uma requisição periódica para manter o bloqueio ativo. Também permite que o usuário desbloqueie ou saia da edição.

## Propriedades

- *Entity **entity*** - Entidade

### Importando componente
```PHP
<?php 
$this->import('entity-renew-lock');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-renew-lock :entity="entity"></entity-renew-lock>
```
# Componente `<entity-lock>`

O componente `entity-lock` é utilizado para exibir um aviso de que uma entidade está sendo editada por outro usuário, mostrando o nome do usuário e a data/hora em que o bloqueio foi iniciado. Se desejado, o usuário pode clicar em um botão para assumir o controle da edição.

## Propriedade
- *Entity **entity*** - Entidade

### Importando componente
```PHP
<?php 
$this->import('entity-lock');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<entity-lock :entity="entity"></entity-lock>
```
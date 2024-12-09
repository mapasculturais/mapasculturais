# Componente `<space-info>`
O componente `space-info` exibe informações detalhadas sobre um espaço cultural, utilizando dados fornecidos pela entidade correspondente.

## Propriedades
- *Entity **entity*** - Entidade
- *Entity **entity*** - Um objeto do tipo Entity que contém informações do espaço cultural a ser exibido.

### Importando componente
```PHP
<?php 
$this->import('space-info');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <space-info :entity="spaceEntity"></space-info>
 ```

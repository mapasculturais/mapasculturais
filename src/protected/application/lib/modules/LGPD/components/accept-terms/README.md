# Componente `<user-accepted-terms>`
A ideia do componente `user-accepted-terms` é listar os termos de uso aceitos pelo usuário, baseados no termo hospedado no Mapa cultural, acessado atraves da variável 'terms'


Para acessar os ter,os, é utilizado o `user`, definido no js.


  
## Propriedades
- *Entity **user*** - Entidade


### Importando componente
```PHP
<?php 
$this->import('user-accepted-terms');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
    <user-accepted-terms :user="entity"></user-accepted-terms>


```
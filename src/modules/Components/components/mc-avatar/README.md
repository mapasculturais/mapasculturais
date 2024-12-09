# Componente<mc-avatar>`
O componente `mc-avatar` é utilizado para exibir avatares de entidades. Ele pode ser configurado para diferentes tamanhos e formatos, e pode exibir uma imagem ou um ícone, dependendo da disponibilidade da imagem de avatar.

## Propriedades
- *entity **Entity*** : A entidade que possui o avatar a ser exibido.
- *size **String*** : O tamanho do avatar. Os valores válidos são `big`, `medium`, `small`, e `xsmall`.
- *square **Boolean*** : Define se o avatar deve ser exibido em formato quadrado.

### Importando componente
```PHP
<?php 
$this->import('mc-avatar');
?>
```
### Exemplos de uso
```HTML
<!-- Utilização básica -->
<mc-avatar :entity="entity" size="medium"></mc-avatar>

<!-- Utilizando o Avatar com Diferentes Tamanhos -->
<mc-avatar :entity="entity" size="big"></mc-avatar>
<mc-avatar :entity="entity" size="medium"></mc-avatar>
<mc-avatar :entity="entity" size="small"></mc-avatar>
<mc-avatar :entity="entity" size="xsmall"></mc-avatar>

<!-- Exibindo Avatar em Formato Quadrado -->
<mc-avatar :entity="entity" size="medium" :square="true"></mc-avatar>
```

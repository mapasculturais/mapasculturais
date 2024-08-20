# Componente `<mc-avatar>`
Componente para exibir avatares de entidades com diferentes tamanhos e formatos. Se a entidade não tiver uma imagem de avatar, será exibido um ícone associado à entidade.

## Propriedades
- *Object **entity*** - Entidade cuja imagem ou ícone será exibido. (Obrigatório)
- *String **size** = 'medium'* - Tamanho do avatar. Valores possíveis: `big`, `medium`, `small`, `xsmall`. (Obrigatório)
- *Boolean **square** = false* - Define se o avatar será quadrado (`true`) ou circular (`false`).

## Computed Properties
- **classes** - Retorna as classes CSS aplicadas ao avatar, dependendo do tamanho, se é um ícone e se é quadrado.
- **image** - Retorna a URL da imagem do avatar baseada nas transformações disponíveis para a entidade.

### Importando o componente
```PHP
<?php 
$this->import('mc-avatar');
?>
```
### Exemplos de uso
<!-- Utilização básica com imagem de avatar -->
<mc-avatar :entity="userEntity" size="big"></mc-avatar>

<!-- Avatar de tamanho pequeno e formato quadrado -->
<mc-avatar :entity="userEntity" size="xsmall" square></mc-avatar>

<!-- Avatar de tamanho pequeno e formato circular -->
<mc-avatar :entity="userEntity" size="xsmall"></mc-avatar>
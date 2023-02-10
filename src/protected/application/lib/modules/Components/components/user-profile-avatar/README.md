# Componente `<user-profile-avatar>`
Imprime uma tag <img > com o avatar do usuário logado, ou no caso de o usuário estar deslogado ou não ter avatar, o ícone de usuáro.

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *String **size** = 'avatarSmall'* - Nome do tamanho da transformação
- *Boolean **original** = false* - Se deve exibir a url da imagem original

### Importando componente
```PHP
<?php 
$this->import('user-profile-avatar');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<user-profile-avatar></user-profile-avatar>

<!-- imprime o img com a url da imagem original do avatar -->
<user-profile-avatar orignal></user-profile-avatar>

<!-- imprime o img com a url do avatarBig -->
<user-profile-avatar size="avatarBig"></user-profile-avatar>
```
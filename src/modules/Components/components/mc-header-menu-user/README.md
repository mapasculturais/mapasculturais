# Componente `<mc-header-menu-user>`
O componente `mc-header-menu-user` é utilizado para renderizar o menu de usuário no cabeçalho de um site, oferecendo suporte para visualização em desktop e dispositivos móveis.

## Slots
Este componente não define slots diretamente, mas utiliza slots de outros componentes como `mc-popover`, `panel--nav`, `theme-logo`, e `user-profile-avatar`.

### Importando componente
```PHP
<?php 
$this->import('mc-header-menu-user');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <mc-header-menu-user>
    <template #default>
        <li><mc-link :entity="profile" icon>Meu Perfil</mc-link></li>
        <li><mc-link route="auth/logout" icon>Sair</mc-link></li>
    </template>
</mc-header-menu-user>
```


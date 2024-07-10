# Componente `<mc-header-menu>`
O componente `mc-header-menu` é utilizado para renderizar o menu do cabeçalho de um site. Ele lida com a alternância do menu em dispositivos móveis e permite a inclusão de slots para o logotipo e itens do menu.

## Slots
- **default**: Slot padrão para a inserção dos itens do menu.
- **logo**: Slot para a inserção do logotipo.

### Importando componente
```PHP
<?php 
$this->import('mc-header-menu');
?>
```

### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <mc-header-menu>
    <template #logo>
        <img src="path/to/logo.png" alt="Logo">
    </template>
    <template #default>
        <li><a href="/home">Home</a></li>
        <li><a href="/about">About</a></li>
        <li><a href="/contact">Contact</a></li>
    </template>
</mc-header-menu>
```
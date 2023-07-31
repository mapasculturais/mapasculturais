# Componente `<mc-icon>`
Wrapper do `<iconify>` com os ícones utilizados nos componentes

## Propriedades
- *Entity **entity*** - Entidade
- *String **name*** - Ícone do quê?

### Importando componente
```PHP
<?php 
$this->import('mc-icon');
?>
```
### Exemplos de uso
```HTML
<!-- ícone de edição -->
<mc-icon name="edit"></mc-icon>

<!-- ícone da entidade - varia de acordo com a entidade e tipo -->
<mc-icon :entity="entity"></mc-icon>

<!-- ícone de agente -->
<mc-icon name="agent"></mc-icon>

<!-- ícone de agente coletivo-->
<mc-icon name="agent-2"></mc-icon>

<!-- ícone de epaço-->
<mc-icon name="space"></mc-icon>

```

### Substituindo iconset via hook
Os ícones podem ser escolhidos em https://icon-sets.iconify.design/

```PHP
$app->hook('component(mc-icon).iconset', function(&$iconset) {
    // modifica o ícone do agente individual
    $iconset['agent-1'] = 'ant-design:user-outlined';

    // modifica o ícone padrão de agente quando utilizado <mc-icon name="agent">
    $iconset['agent'] = 'ant-design:user-outlined';
});
```
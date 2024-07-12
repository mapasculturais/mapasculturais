# Componente `<mc-collapse>`
O componente `mc-collapse `permite exibir e ocultar conteúdo adicional de forma expansiva. Ele emite eventos quando o estado de expansão é alterado.

### Eventos
- **toggle**: Emitido quando o método `toggle` é chamado. O evento inclui o novo estado de expansão (`true `para expandido, `false` para contraído).
- **close**: Emitido quando o método `close` é chamado. Não inclui dados adicionais.

## Slots
- **header**: Parte inicial do componente (sempre exibido)
- **content**: Corpo principal do componente (colapsavel)

### Importando componente
```PHP
<?php 
$this->import('mc-collapse');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-collapse>
    <template #header>
        Texto a ser exibido
    </template>

    <template #content>
        Texto ocultado pelo componente, que pode ser expandido
    </template>
</mc-collapse>

```
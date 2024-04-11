# Componente `<mc-collapse>`
Componente feito para mostrar e esconder conteúdo

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
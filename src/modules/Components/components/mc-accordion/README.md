# Componente `<mc-accordion>`
Componente para criação de accordion, permitindo mostrar e esconder conteúdo de forma interativa.

### Eventos
- **toggle** - Disparado sempre que o estado do accordion (aberto/fechado) é alterado.

## Propriedades
- *Boolean **active** = false* - Define o estado inicial do accordion (false = fechado, true = aberto).

## Slots
- **title** - Conteúdo que será exibido como título do accordion.
- **content** - Conteúdo que será exibido dentro do accordion quando ele estiver aberto.

### Importando o componente
```PHP
<?php 
$this->import('mc-accordion');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-accordion>
    <template #title>
        <?= i::__('Título do accordion') ?>
    </template>
    <template #content>
        <?= i::__('Conteúdo que será exibido quando o accordion estiver aberto') ?>
    </template>
</mc-accordion>

```
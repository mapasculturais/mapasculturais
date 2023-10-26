# Componente `<mc-relation-card>`
Card para listagem dos detalhes dos agentes relacionados
  
## Propriedades
- *Object **relation*** - Relação do agente

## Slots
- **default** `{open, close, toggle}`: Botão para abrir o card (icone do usuário)

### Importando componente
```PHP
<?php 
$this->import('mc-relation-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<mc-relation-card :entity="entity" name="Fulano">
    <a @click="$event.preventDefault(); toggle()">
        <mc-icon name="agent"></mc-icon>
    </a>
</mc-relation-card>

```
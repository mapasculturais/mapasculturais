# Componente `<evaluation-card>`
Cartão de avaliação

### Eventos
- **namesDefined** - disparado quando o método `defineNames` é chamado, após a definição do `name` e `nomeCompleto`
  
## Propriedades
- *Entity **entity*** - Entidade
- *String **name*** - Nome
- *String **lastname** = ''* - Sobrenome

## Slots
- **default** `{fullname, displayName, compareDisplayName, compareFullname}`: informações dos nomes e comparação com o nome gerado

### Importando componente
```PHP
<?php 
$this->import('evaluation-card');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<evaluation-card :entity="entity" name="Fulano"></evaluation-card>

```
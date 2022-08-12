# Componente `<search-header>`
A ideia do componente `search-header` é servir de modelo para gerar páginas de pesquisas das entidades.
  
## Propriedades
- *String **type*** - Tipo da Entidade

## Slots
- **create** slot para modal de criação da entidade

### Importando componente
```PHP
<?php 
$this->import('search-header');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
<search-header :entity="entity" name="Fulano"></search-header>
```
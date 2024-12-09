# Componente `<mc-entity>`
O componente `mc-entity` é utilizado para buscar e exibir dados de uma entidade específica da API do Mapas Culturais. Ele lida com o carregamento de dados de uma entidade com base no ID e tipo fornecidos como propriedades.

## Propriedades
- *Id **number***: ID da entidade a ser buscada.
- *Type **string***: Tipo da entidade a ser buscada.
- *Select **string*** (default: '*'): Campos da entidade a serem selecionados.
- *Scope **String***: Escopo da API a ser utilizada. Se não for fornecido, o padrão é 'default'.

## Slots
- **default**`{entity}`: Slot padrão que fornece a entidade carregada quando o estado de carregamento (loading) é false.

### Importando componente
```PHP
<?php 
$this->import('mc-entity');
?>
```
### Exemplos de uso
```HTML
<!-- utilizaçao básica -->
 <mc-entity :id="123" type="event" v-slot="{ entity }">
    <div>{{ entity.name }}</div>
</mc-entity>

<!-- Utilizando Propriedades e Slot -->
 <mc-entity :id="456" type="agent" select="name,description" v-slot="{ entity }">
    <h1>{{ entity.name }}</h1>
    <p>{{ entity.description }}</p>
</mc-entity>
```
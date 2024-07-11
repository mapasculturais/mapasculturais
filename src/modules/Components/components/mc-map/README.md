# Componente `<mc-map>`
O componente `mc-map` é utilizado para exibir um mapa interativo com clusters de marcadores. Ele é construído utilizando a biblioteca VueLeaflet e pode ser configurado para diferentes tipos de entidades, como agentes, espaços e eventos.

### Eventos
- **ready** - Disparado quando o mapa está pronto para uso. Passa o objeto Leaflet como argumento.
- **openPopup** - Disparado ao abrir um popup em um marcador. Passa um objeto contendo o marcador, o objeto Leaflet, a promessa da entidade e a entidade.
- **closePopup** - Disparado ao fechar um popup em um marcador. Passa um objeto contendo o marcador, o objeto Leaflet e a entidade.

## Propriedades
- *Object **center*** - Centro do mapa. Deve ser um object {lat, lng}
- *Entities **Array*** - Lista de entidades a serem exibidas no mapa. Cada entidade deve conter informações como localização e tipo.

## Slots
- **default**: Slot para adicionar os markers `<mc-map-marker>`
- **popup**: Slot para personalizar o conteúdo do popup exibido ao clicar em um marcador. Recebe a entidade como argumento.

### Importando componente
```PHP
<?php 
$this->import('mc-map');
?>
```
### Exemplos de uso
```HTML

<!-- utilizaçao básica -->
<mc-map>
    <mc-map-marker :entity="entity"></mc-map-marker>
</mc-map>

<!-- Com Popups Personalizados -->
 <mc-map :center="{ lat: -23.55052, lng: -46.633308 }" :entities="entities" @openPopup="handleOpenPopup" @closePopup="handleClosePopup">
    <template v-slot:popup="{ entity }">
        <div>
            <h3>{{ entity.name }}</h3>
            <p>{{ entity.description }}</p>
        </div>
    </template>
</mc-map>
```
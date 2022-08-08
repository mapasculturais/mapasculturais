<?php
use MapasCulturais\i;

$this->import('mc-map-marker mc-map entities');
?>

<div class="home-map">
    <div class="home-map__content">
        <label class="home-map__content--title"><?php i::_e('Visualize também no mapa') ?></label>
        <p class="home-map__content--description">Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla. </p>
            
            <div v-for="entity in allEntities">
                    {{entity.id}}
            </div>
        <!-- <mc-map>
            
            <div v-for="(entity, index) in allEntities">
                <mc-map-marker :entity="entity"></mc-map-marker>
            
            </div>

        </mc-map> -->
    </div>
</div>
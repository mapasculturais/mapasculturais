<?php
use MapasCulturais\i;
$this->import('mc-map entities mc-map-card');
?>
<div class="home-map">
    <div class="home-map__content">
        <label class="home-map__content--title"><?php i::_e('Visualize também no mapa') ?></label>
        <p v-if="text" class="home-map__content--description">{{text}}</p>
        <mc-map :entities="entities">
            <template #popup="{entity}">
                <mc-map-card :entity="entity"></mc-map-card>
            </template>
        </mc-map>
    </div>
</div>
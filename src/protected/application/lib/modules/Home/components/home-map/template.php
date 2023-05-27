<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;
$this->import('
    mc-map 
    mc-map-card
');
?>
<div class="home-map">
    <div class="home-map__header">
        <label class="title"><?php i::_e('Visualize também no mapa') ?></label>
        <label class="description"><?= i::_e('Os agentes, espaços e eventos cadastrados contam com a geo localização de seus endereços, encontre-os aqui:') ?></label>
    </div>

    <div class="home-map__content">
        <mc-map :entities="entities">
            <template #popup="{entity}">
                <mc-map-card :entity="entity"></mc-map-card>
            </template>
        </mc-map>
    </div>
</div>
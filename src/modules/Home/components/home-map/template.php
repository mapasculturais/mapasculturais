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
<div v-if="global.enabledEntities.spaces || global.enabledEntities.agents" class="home-map">
    <div class="home-map__header">
        <label class="title"><?= $this->text('title', i::__('Visualize também no mapa')) ?></label>
        <label class="description"><?= $this->text('description', i::__('Os agentes, espaços e eventos cadastrados contam com a geo localização de seus endereços, encontre-os aqui:')) ?></label>
    </div>

    <div class="home-map__content">
        <mc-map :entities="entities">
            <template #popup="{entity}">
                <mc-map-card :entity="entity"></mc-map-card>
            </template>
        </mc-map>
    </div>
</div>
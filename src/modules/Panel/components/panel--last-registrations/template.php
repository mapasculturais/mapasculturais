<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-card
');
?>
<div v-if="entities.length > 0 && global.enabledEntities.opportunities" class="panel--last-registrations">
    <div class="panel--last-registrations__content">
        <div class="panel--last-registrations__content-title">
            <label> <?= $this->text('recent_registrations', i::__('Inscrições recentes'))?> </label>
        </div>
        <div class="panel--last-registrations__content-cards">
            <carousel :settings="settings" :breakpoints="breakpoints">
                <slide v-for="entity in entities" :key="entity.id">
                    <registration-card :entity="entity" :list="entities">
                        <template #button>
                            <a class="button button--large button--primary button--icon" target="__blank" :href="entity.singleUrl"> <?= i::_e('Acessar e acompanhar') ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                        </template>
                    </registration-card>
                </slide>
                <template #addons>
                    <div class="actions">
                        <navigation />
                    </div>
                </template>
            </carousel>            
        </div>
    </div>
</div>
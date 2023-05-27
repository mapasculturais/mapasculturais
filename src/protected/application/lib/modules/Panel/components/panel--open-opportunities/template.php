<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-card 
    mc-icon
');
?>
<div v-if="entities.length > 0" class="panel--open-opportunities">
    <div class="panel--open-opportunities__content">
        <div class="panel--open-opportunities__content--title">
            <label> <?php i::_e('Oportunidades abertas')?> </label>
        </div>
        <div class="panel--open-opportunities__content--cards">
            <carousel v-if="entities.length > 0" :settings="settings" :breakpoints="breakpoints">
                <slide v-for="entity in entities" :key="entity.__objectId">
                    <div class="card">
                        <div class="card__content">
                            <label class="card__content--title"> <img :src="entity.files.avatar.transformations.avatarMedium.url"/> {{entity.name}} </label>              
                            <div class="card__content--description">
                                {{entity.shortDescription}}
                            </div>    
                        </div>
                        <div class="card__action">
                            <a class="button button--primary button--icon" target="__blank" :href="entity.singleUrl"><mc-icon name="settings"></mc-icon> <?= i::_e('Configurar e gerir') ?></a>
                        </div>
                    </div>
                </slide>
                <template #addons>
                    <div class="actions">
                        <navigation :slideWidth="472" />
                    </div>
                </template>
            </carousel>
        </div>
    </div>
</div>
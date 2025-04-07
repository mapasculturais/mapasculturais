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
                    <div class="card">
                        <div class="card__content">
                            <a target="__blank" :href="entity.singleUrl">
                                <label class="card__content--title"> {{entity.opportunity.name}} </label>  
                            </a>            
                            <div class="card__content--description date">
                                <label><?= $this->text('registration_date', i::__('Data de inscrição')) ?></label>
                                <strong>{{entity.opportunity.registrationFrom?.format()}} <?= i::_e('às') ?> {{entity.opportunity.registrationFrom?.hour()}}h</strong>
                            </div>    
                        </div>
                        <div class="card__action">
                            <a class="button button--md button--large button--primary button--icon" target="__blank" :href="entity.singleUrl"> <?= i::_e('Acessar e acompanhar') ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                        </div>
                    </div>
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
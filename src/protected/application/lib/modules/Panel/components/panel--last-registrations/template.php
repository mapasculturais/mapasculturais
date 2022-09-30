<?php
use MapasCulturais\i;

$this->import('
    entity-card
');
?>

<div class="panel--last-registrations">

    <div class="panel--last-registrations__content">

        <div class="panel--last-registrations__content-title">
            <label> <?php i::_e('Editados recentemente')?> </label>
        </div>

        <div class="panel--last-registrations__content-cards">

            <carousel :settings="settings" :breakpoints="breakpoints">
                <slide v-for="entity in entities" :key="entity.id">

                <!-- {{entity}} -->

                <div class="card">
                    <div class="card__content">
                        <label class="card__content--title"> Nome da oportunidade </label>              
                        <div class="card__content--description">
                            Data de inscrição
                        </div>    
                    </div>
                    <div class="card__action">
                        <a class="button button--md button--primary button--icon-right" target="__blank" :href="entity.singleUrl"> <?= i::_e('Acessar e acompanhar') ?> <mc-icon name="arrowPoint-right"></mc-icon> </a>
                    </div>
                </div>

                    <!-- <panel--entity-card :key="entity.id" :entity="entity" class="card">

                        <template #title="{entity}">
                            <slot name="card-title" :entity="entity"></slot>
                        </template>

                        <template #header-actions="{entity}">
                            <div :class="[entity.__objectType+'__background', 'card-actions--tag']">
                                <mc-icon :name="entity.__objectType"></mc-icon>    
                                <span v-if="entity.__objectType=='agent'"> <?= i::_e('Agente') ?> </span>
                                <span v-if="entity.__objectType=='space'"> <?= i::_e('Espaço') ?> </span>
                                <span v-if="entity.__objectType=='event'"> <?= i::_e('Evento') ?> </span>
                                <span v-if="entity.__objectType=='project'"> <?= i::_e('Projeto') ?> </span>
                                <span v-if="entity.__objectType=='opportunity'"> <?= i::_e('Oportunidade') ?> </span>
                            </div>
                        </template>

                        <template #default="{entity}">
                            <span v-if="entity.shortDescription">
                               {{entity.shortDescription}}
                            </span>
                        </template>
                        
                        <template #entity-actions-left="{entity}">
                            &nbsp;
                        </template>
                        <template #entity-actions-center="{entity}">
                            &nbsp;
                        </template>

                    </panel--entity-card> -->

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
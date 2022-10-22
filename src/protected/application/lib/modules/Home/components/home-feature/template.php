<?php
use MapasCulturais\i;

$this->import('
    entities 
    entity-card 
    loading
    tabs
');
?>

<div class="home-feature">
    <div class="home-feature__content">
        <div class="home-feature__content--title">
            <label> <?php i::_e('Em destaque')?> </label>
        </div>

        <div class="home-feature__content--description">
        <label><?php i::_e('Saiba tudo que acontece no Mapas Culturais, acesse:')?></label>
        </div>

        <div class="home-feature__content--cards">
            <loading :condition="!entities.length"></loading>
            <tabs v-if="entities.length > 0">
                <tab label="Todos" slug="all">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.__objectId">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template v-if="entities.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>

                <tab label="Agentes" slug="agents">
                    <carousel v-if="agents.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in agents" :key="entity.__objectId">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template v-if="agents.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>

                <tab label="Espaços" slug="spaces">
                    <carousel v-if="spaces.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in spaces" :key="entity.__objectId">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template v-if="spaces.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>
            </tabs>
            
        </div>
    </div>
</div>
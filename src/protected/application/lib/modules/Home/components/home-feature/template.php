<?php
use MapasCulturais\i;

$this->import('entities entity-card tabs');
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

            <tabs>
                <tab label="Todos" slug="all">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.id">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>

                <tab label="Agentes" slug="agents">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in agents" :key="entity.id">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>

                <tab label="Espaços" slug="spaces">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in spaces" :key="entity.id">
                            <entity-card :entity="entity"></entity-card> 
                        </slide>                        

                        <template #addons>
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
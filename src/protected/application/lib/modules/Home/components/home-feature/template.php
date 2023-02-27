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
    <div class="home-feature__header">
        <div class="home-feature__header title">
            <label> <?php i::_e('Em destaque')?> </label>
        </div>
        <div class="home-feature__header description">
            <label><?php i::_e('Confira os últimos destaques de cada uma das entidades')?></label>
        </div>
    </div>
    <div class="home-feature__content">
        <div class="home-feature__content cards">
            <loading :condition="!entities.length"></loading>
            <tabs v-if="entities.length > 0">
                <tab label="Todos" slug="all">
                    <carousel :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.__objectId">
                            <entity-card :entity="entity" portrait>
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        {{entityTypeReturn(entity.__objectType)}}
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="entities.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>
                <tab label="Agentes" slug="agents" v-if="agents.length > 0">
                    <carousel v-if="agents.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in agents" :key="entity.__objectId">
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        {{entityTypeReturn(entity.__objectType)}}
                                    </div>
                                </template>
                            </entity-card>
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
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        {{entityTypeReturn(entity.__objectType)}}
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="spaces.length > 1" #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </tab>
                <tab label="Projetos" slug="projects">
                    <carousel v-if="projects.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in projects" :key="entity.__objectId">
                            <entity-card :entity="entity">
                                <template #labels>
                                    <div :class="['entityType',  entity.__objectType+'__background']">
                                        <mc-icon :entity="entity"></mc-icon>
                                        {{entityTypeReturn(entity.__objectType)}}
                                    </div>
                                </template>
                            </entity-card>
                        </slide>

                        <template v-if="projects.length > 1" #addons>
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
<?php
use MapasCulturais\i;

$this->import('entities entity-card mc-icon');
?>

<div class="panel--open-opportunities">
    <div class="panel--open-opportunities__content">
        <div class="panel--open-opportunities__content--title">
            <label> <?php i::_e('Oportunidades abertas')?> </label>
        </div>
        <div class="panel--open-opportunities__content--cards">
            <entities type="opportunity" :query="getQuery">
                <template #default="{entities}">                    
                    <carousel v-if="entities.length > 0" :settings="settings" :breakpoints="breakpoints">
                        <slide v-for="entity in entities" :key="entity.id">
                            <div class="card">
                                <div class="card__content">
                                    <label class="card__content--title"> <img :src="entity.files.avatar.transformations.avatarMedium.url"/> {{entity.name}} </label>              
                                    <div class="card__content--description">
                                        {{entity.shortDescription}}
                                    </div>    
                                </div>
                                <div class="card__action">
                                    <a class="button button--primary button--icon" target="__blank" :href="entity.singleUrl"><mc-icon name="settings"></mc-icon> <?= i::_e('Acessar e acompanhar') ?></a>
                                </div>
                            </div>
                        </slide>
                        <template #addons>
                            <div class="actions">
                                <navigation :slideWidth="368" />
                            </div>
                        </template>
                    </carousel>
                </template>
            </entities>
        </div>
    </div>
</div>
<?php

use MapasCulturais\i;

$this->import('
    entities
    entity-card
    mapas-card
    mc-icon
');
?>

<div class="grid-12 search-list">
    <entities :type="type" :select="select" :query="query" order="createTimestamp DESC" :limit="limit" watch-query>
        <template #header="{entities}">
            <div class="col-3 search-list__filter">
                <div class="search-list__filter--filter">
                    <slot name="filter"></slot>
                </div>
            </div>
        </template>

        <template #default="{entities}">
            <div class="col-9">
                <div class="grid-12">
                    
                    <div class="col-12 search-list__order">
                        <div class="field">
                            <label> <? i::_e('Ordenar por')?>  </label>
                            <select v-model="entities.query['@order']">
                                <option value="name ASC"> <?php i::_e('Ordem alfabetica crescente') ?> </option>
                                <option value="name DESC"> <?php i::_e('Ordem alfabetica decrescente') ?> </option>
                                <option value="createTimestamp ASC"> <?php i::_e('Data de criação crescente') ?> </option>
                                <option value="createTimestamp DESC"> <?php i::_e('Data de criação decrescente') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom ASC"> <?php i::_e('Início das inscrições crescente') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom DESC"> <?php i::_e('Início das inscrições decrescente') ?> </option>
                            </select>
                        </div>
                        <div class="foundResults">
                            {{entities.metadata.count}} {{entityType}} <?= i::__('encontrados') ?> 
                        </div>
                    </div>

                    <entity-card :entity="entity" v-for="entity in entities" :key="entity.__objectId" class="col-12">
                        <template #type> <span>{{typeText}} <span :class="['upper', entity.__objectType+'__color']">{{entity.type.name}}</span></span></template>
                    </entity-card>
                </div>
            </div>
        </template>
    </entities>
</div>
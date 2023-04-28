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
                                <option value="name ASC"> <?php i::_e('Ordenar por nome (A - Z)') ?> </option>
                                <option value="name DESC"> <?php i::_e('Ordenar por nome (Z - A)') ?> </option>
                                <option value="createTimestamp ASC"> <?php i::_e('Tempo de criação (recentes-antigas)') ?> </option>
                                <option value="createTimestamp DESC"> <?php i::_e('Tempo de criação (antigas-recentes)') ?> </option>
                                <option value="updateTimestamp ASC"> <?php i::_e('Ordenar por última edição (recentes-antigas)') ?> </option>
                                <option value="updateTimestamp DESC"> <?php i::_e('Ordenar por última edição (antigas-recentes)') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom ASC"> <?php i::_e('Início das inscrições (recentes-antigas)') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom DESC"> <?php i::_e('Início das inscrições (antigas-recentes)') ?> </option>
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
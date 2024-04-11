<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-card
    mc-avatar
    mc-entities
');
?>
<div class="grid-12 search-list">
    <mc-entities :type="type" :select="select" :query="query" :order="order" :limit="limit" watch-query>
        <template #load-more="{entities, loadMore}">
            <div class="col-9 load-more">
                <mc-loading :condition="entities.loadingMore"></mc-loading>
                <button class="button--large button button--primary-outline" v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
            </div>
        </template>
        <template #header="{entities}">
            <div class="col-3 search-list__filter">
                <div class="search-list__filter--filter">
                    <slot name="filter"></slot>
                </div>
            </div>
            <div v-if="entities.loading" class="col-9">
                <div class="grid-12">
                    <div class="col-12 search-list__order">
                        <div class="field">
                            <select v-model="order">
                                <option value="name ASC"> <?php i::_e('Ordem alfabética (A - Z)') ?> </option>
                                <option value="name DESC"> <?php i::_e('Ordem alfabética (Z - A)') ?> </option>
                                <option value="createTimestamp ASC"> <?php i::_e('Mais antigas primeiro') ?> </option>
                                <option value="createTimestamp DESC"> <?php i::_e('Mais recentes primeiro') ?> </option>
                                <option value="updateTimestamp DESC"> <?php i::_e('Modificadas recentemente') ?> </option>
                                <option value="updateTimestamp ASC"> <?php i::_e('Modificadas há mais tempo') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom ASC"> <?php i::_e('Início das inscrições (recentes-antigas)') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom DESC"> <?php i::_e('Início das inscrições (antigas-recentes)') ?> </option>
                            </select>
                        </div>
                        <div class="foundResults">
                            {{entities.metadata.count}} {{entityType}} <?= i::__('encontrados') ?> 
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template #default="{entities}">
            <div class="col-9">
                <div class="grid-12">
                    <div class="col-12 search-list__order">
                        <div class="field">
                            <select v-model="order">
                                <option value="name ASC"> <?php i::_e('Ordem alfabética (A - Z)') ?> </option>
                                <option value="name DESC"> <?php i::_e('Ordem alfabética (Z - A)') ?> </option>
                                <option value="createTimestamp ASC"> <?php i::_e('Mais antigas primeiro') ?> </option>
                                <option value="createTimestamp DESC"> <?php i::_e('Mais recentes primeiro') ?> </option>
                                <option value="updateTimestamp DESC"> <?php i::_e('Modificadas recentemente') ?> </option>
                                <option value="updateTimestamp ASC"> <?php i::_e('Modificadas há mais tempo') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom ASC"> <?php i::_e('Início das inscrições (recentes-antigas)') ?> </option>
                                <option v-if="type == 'opportunity'" value="registrationFrom DESC"> <?php i::_e('Início das inscrições (antigas-recentes)') ?> </option>
                            </select>
                        </div>
                        <div v-if="entityType=='Oportunidades'" class="foundResults">
                            {{entities.metadata.count}} {{entityType}} <?= i::__('encontradas') ?> 
                        </div>
                        <div v-if="entityType!='Oportunidades'" class="foundResults">
                            {{entities.metadata.count}} {{entityType}} <?= i::__('encontrados') ?> 
                        </div>
                    </div>

                    <entity-card :entity="entity" v-for="entity in entities" :key="entity.__objectId" class="col-12">
                        <template #avatar>
                            <mc-avatar :entity="entity" size="medium"></mc-avatar>
                        </template>
                        <template #type> <span>{{typeText}} <span :class="['upper', entity.__objectType+'__color']">{{entity.type.name}}</span></span></template>
                    </entity-card>
                </div>
            </div>
        </template>
    </mc-entities>
</div>
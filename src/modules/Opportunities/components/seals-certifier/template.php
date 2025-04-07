<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
   select-entity
   mc-avatar
   mc-confirm-button
   mc-icon
');

?>

<div v-if="entity.seals.length > 0 || editable" class="seals-certifier col-12">
    <h4 class="seals-certifier__title bold"> {{title}} <?php i::_e(' para proponentes') ?> </h4>
    <div class="seals-certifier__proponents">
        <div v-for="(seals, proponentType) in proponentSeals" :key="proponentType" class="seals-certifier__proponent field">
            <label v-if="proponentType !== 'default'"><?php i::_e('Selecione o(s) selo(s) para {{ proponentType }}:') ?></label>
            <label v-else><?php i::_e('Selecione o(s) selo(s) para todos proponentes:') ?></label>
            <div class="seals-certifier__proponent--seals">
                <div v-for="seal in seals" :key="seal.id" class="seals-certifier__proponent--seal">
                    <div class="seal-icon">
                        <a :href="getSealDetails(seal).singleUrl" class="link">
                            <div v-if="getSealDetails(seal).files?.avatar" class="image">
                                <mc-avatar :entity="getSealDetails(seal)" size="small" square></mc-avatar>
                            </div>
                            <div v-if="!(getSealDetails(seal).files?.avatar)">
                                <mc-icon name="seal"></mc-icon>
                            </div>
                        </a>
                        <div v-if="editable" class="icon">
                            <mc-confirm-button @confirm="removeSeal(proponentType, seal, 'proponent')">
                                <template #button="modal">
                                    <mc-icon @click="modal.open()" name="delete"></mc-icon>
                                </template>
                                <template #message="message">
                                    <?php i::_e('Remover selo?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                    <span class="seal-label" v-if="showName">{{getSealDetails(seal).name}}</span>
                </div>
                <select-entity
                    :type="'seal'"
                    @select="addSeal(proponentType, $event, 'proponent')"
                    :query="getSealQuery(proponentType, 'proponent')"
                    openside="down-right"
                    >
                    <template #button="{ toggle }">
                        <div class="seals-certifier__proponent--addSeal" @click="toggle()">
                            <mc-icon name="add"></mc-icon>
                        </div>
                    </template>
                </select-entity>
            </div>
        </div>
    </div>
    
    <h4 class="seals-certifier__title bold"> {{title}} <?php i::_e(' para categorias') ?> </h4>
    <div class="seals-certifier__categories">
        <div v-for="(seals, category) in categorySeals" :key="category" class="seals-certifier__category field">
            <label v-if="category !== 'default'"><?php i::_e('Selecione o(s) selo(s) para {{ category }}:') ?></label>
            <label v-else><?php i::_e('Selecione o(s) selo(s) para todas categorias:') ?></label>
            <div class="seals-certifier__category--seals">
                <div v-for="seal in seals" :key="seal.id" class="seals-certifier__category--seal">
                    <div class="seal-icon">
                        <a :href="getSealDetails(seal).singleUrl" class="link">
                            <div v-if="getSealDetails(seal).files?.avatar" class="image">
                                <mc-avatar :entity="getSealDetails(seal)" size="small" square></mc-avatar>
                            </div>
                            <div v-if="!(getSealDetails(seal).files?.avatar)">
                                <mc-icon name="seal"></mc-icon>
                            </div>
                        </a>
                        <div v-if="editable" class="icon">
                            <mc-confirm-button @confirm="removeSeal(category, seal, 'category')">
                                <template #button="modal">
                                    <mc-icon @click="modal.open()" name="delete"></mc-icon>
                                </template>
                                <template #message="message">
                                    <?php i::_e('Remover selo?') ?>
                                </template>
                            </mc-confirm-button>
                        </div>
                    </div>
                    <span class="seal-label" v-if="showName">{{getSealDetails(seal).name}}</span>
                </div>
                <select-entity
                    :type="'seal'"
                    @select="addSeal(category, $event, 'category')"
                    :query="getSealQuery(category, 'category')"
                    openside="down-right">
                    <template #button="{ toggle }">
                        <div class="seals-certifier__category--addSeal" @click="toggle()">
                            <mc-icon name="add"></mc-icon>
                        </div>
                    </template>
                </select-entity>
            </div>
        </div>
    </div>
</div>

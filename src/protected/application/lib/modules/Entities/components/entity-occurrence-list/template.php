<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    create-occurrence
    entity-map
    mc-confirm-button
    mc-entities
    mc-icon
');
?>
<div class="entity-occurrence-list" :class="classes">
    <div v-if="editable && !createEvent" class="entity-occurrence-list__editable">
        <label class="entity-occurrence-list__editable--title">
            <?= i::_e('Data, hora e local do evento') ?>
        </label>
        <p class="entity-occurrence-list__editable--description">
            <?= i::_e('Adicione data, hora e local da ocorrência do seu evento. Você pode criar várias ocorrências com informações diferentes.') ?>
        </p>
        <create-occurrence :entity="entity" @create="addToOccurrenceList($event)"></create-occurrence>
    </div>
    <div v-if="editable && createEvent" class="entity-occurrence-list__editable">
        <label class="entity-occurrence-list__editable--title">
            <?= i::_e('Deseja inserir uma ocorrência para o seu Evento?') ?>
        </label>
        <p class="entity-occurrence-list__editable--description">
            <?= i::_e('Você pode inserir agora uma ocorrência do seu evento ou a ocorrência única.') ?>
        </p>
        <create-occurrence :entity="entity" @create="addToOccurrenceList($event)"></create-occurrence>
    </div>
    <div class="entity-occurrence-list__occurrences">
        <mc-entities name="occurrenceList" type="eventoccurrence" endpoint="find" :query="{event: `EQ(${entity.id})`}" select="*,space.{name,endereco,files.avatar,location}">
            <template #default="{entities}">
                <div v-for="occurrence in entities" class="occurrence" :class="{'edit': editable}" :key="occurrence._reccurrence_string">
                    <div class="occurrence__card">
                        <div class="header">
                            <div class="header__title">
                                <mc-icon name="pin"></mc-icon>
                                <span class="title">
                                    <mc-link :entity="occurrence.space">{{occurrence.space?.name}}</mc-link>
                                </span>
                            </div>
                            <span v-if="occurrence.space?.endereco" @click="toggleMap($event)" class="header__link button--icon">
                                <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                            </span>
                        </div>
                        <div class="address">
                            <p>{{occurrence.space?.endereco}}</p>
                        </div>
                        <div class="content">
                            <div class="content__ticket">
                                <mc-icon name="date"></mc-icon>
                                <span class="ticket">
                                    {{occurrence.description}}
                                </span>
                            </div>
                            <div class="content__price">
                                <div class="content__price--value">
                                    <mc-icon name="ticket"></mc-icon>
                                    <span class="value">
                                        {{formatPrice(occurrence.price)}}
                                    </span>
                                </div>
                                <div v-if="occurrence.priceInfo" class="content__price--info">
                                    <mc-icon name="info"></mc-icon>
                                    <span class="info">
                                        {{occurrence.priceInfo}}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="occurrence.space?.endereco" class="occurrence__map" :class="{showMap }">
                        <entity-map :entity="occurrence.space"></entity-map>
                    </div>
                    <div v-if="editable" class="occurrence__actions">

                        <!-- Sem edição no momento -->
                        <!-- <create-occurrence occurrence="ocorrência" editable>
                            <a class="occurrence__actions--edit">
                                <mc-icon name="edit"></mc-icon><?= i::_e('Editar') ?>
                            </a>
                        </create-occurrence> -->

                        <mc-confirm-button @confirm="occurrence.delete(true)">
                            <template #button="modal">
                                <a class="occurrence__actions--delete" @click="modal.open();">
                                    <mc-icon name="trash"></mc-icon><?= i::_e('Excluir') ?>
                                </a>
                            </template>
                            <template #message="message">
                                <?php i::_e('Deseja remover essa ocorrência?') ?>
                            </template>
                        </mc-confirm-button>
                    </div>
                </div>
            </template>
            <template #empty>
                <p></p>
            </template>
            <template #loading>
                <div>
                    <mc-icon name="loading"></mc-icon>
                </div>
            </template>
        </mc-entities>
    </div>
</div>
<?php

use MapasCulturais\i;

$this->import('
    create-occurrence
    entities
    mc-icon
');
?>

<div class="entity-occurrence-list">
    <div v-if="editable" class="entity-occurrence-list__editable">
        <label class="entity-occurrence-list__editable--title">
            <?= i::_e('Data, hora e local do evento') ?>
        </label>
        <p class="entity-occurrence-list__editable--description">
            <?= i::_e('Adicione data, hora e local da ocorrência do seu evento. Você pode criar várias ocorrências com informações diferentes.') ?>
        </p>
        <create-occurrence @create="addToOccurrenceList($event)"></create-occurrence>
    </div>
    <div class="entity-occurrence-list__occurrences">
        <label class="occurrence__title">Agenda</label>
        <entities type="eventOccurrence" endpoint="find" :query="{event: `EQ(${entity.id})`}" select="*,space.{name,endereco,files.avatar}" loading>
            <template #default="{entities}">
                <div v-for="occurrence in entities" class="occurrence" :class="{'edit': editable}" :key="occurrence._reccurrence_string">
                    <div class="occurrence__header">
                        <div class="occurrence__header--title">
                            <mc-icon name="pin"></mc-icon> {{occurrence.space.name}}
                        </div>
                        <a class="occurrence__header--link button--icon">
                            <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                        </a>
                    </div>
                    <div class="occurrence__address">
                        <p>{{occurrence.space.endereco}}</p>
                    </div>
                    <div class="occurrence__content">
                        <div class="occurrence__content--ticket">
                            <mc-icon name="date"></mc-icon>
                            <span class="ticket">
                                {{occurrence.rule.startsOn}}
                            </span>
                        </div>
                        <div class="occurrence__content--price">
                            <mc-icon name="ticket"></mc-icon>
                            <span class="price">
                                {{formatPrice(occurrence.rule.price)}}
                            </span>
                        </div>
                    </div>
                    <div v-if="editable" class="occurrence__actions">
                        <a class="occurrence__actions--edit">
                            <mc-icon name="edit"></mc-icon><?= i::_e('Editar') ?>
                        </a>
                        <a class="occurrence__actions--delete">
                            <mc-icon name="trash"></mc-icon><?= i::_e('Excluir') ?>
                        </a>
                    </div>
                </div>
            </template>
            <template #loading>
                <div>
                    <mc-icon name="loading"></mc-icon>
                </div>
            </template>
        </entities>
    </div>
</div>
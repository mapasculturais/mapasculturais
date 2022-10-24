<?php

use MapasCulturais\i;

$this->import('
    modal
    entity-field
    create-occurrence
    entities
    mc-icon
');
?>

<div class="entity-occurrence-list">

    <div class="entity-occurrence-list__editable">
        <label class="entity-occurrence-list__editable--title">
            <?= i::_e('Data, hora e local do evento') ?>
        </label>
        <p class="entity-occurrence-list__editable--description">
            <?= i::_e('Adicione data, hora e local da ocorrência do seu evento. Você pode criar várias ocorrências com informações diferentes.') ?>
        </p>
        <create-occurrence v-if="editable" @create="addToOccurrenceList($event)"></create-occurrence>
    </div>

    <div class="entity-occurrence-list__occurrences">

        <entities type="event" endpoint="occurrences" :raw-processor="occurrenceRawProcessor" :query="spaceQuery" watch-query>

            <template #default="{entities}">
                <div v-for="occurrence in entities" class="occurrence" :key="occurrence._reccurrence_string">
                    <div class="occurrence__header">
                        <div class="occurrence__header--title">
                            <mc-icon name="pin"></mc-icon> {{occurrence.space.name}}
                        </div>
                        <a class="occurrence__header--link button--icon">
                            <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                        </a>
                    </div>
                    <div class="occurrence__content">
                        <div class="occurrence__content--ticket">
                            <mc-icon name="date"></mc-icon> {{occurrence.starts._date.toLocaleString('pt-BR').substr(0, 10)}}
                        </div>
                        <div class="occurrence__content--price">
                            <mc-icon name="ticket"></mc-icon> <?= i::_e('Detalhes da entrada') ?>
                        </div>
                    </div>
                    <div class="occurrence__actions">
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
                <div> <mc-icon name="loading"></mc-icon> </div>
            </template>

        </entities>

    </div>
</div>
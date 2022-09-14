<?php
use MapasCulturais\i;

$this->import('modal entity-field create-occurrence'); 
?>

<div class="entity-occurrence-list">

    <div class="entity-occurrence-list__editable">
        <label class="entity-occurrence-list__editable--title">
            <?= i::_e('Data, hora e local do evento') ?>
        </label>
        <p class="entity-occurrence-list__editable--description">
            <?= i::_e('Adicione data, hora e local da ocorrência do seu evento. Você pode criar várias ocorrências com informações diferentes.') ?>
        </p>
        <create-occurrence></create-occurrence>
    </div>

    <div class="entity-occurrence-list__occurrences">

        <div class="occurrence">
            <div class="occurrence__header">
                <div class="occurrence__header--title">
                    <mc-icon name="pin"></mc-icon> <?= i::_e('Local da ocorrência') ?>
                </div>
                <a class="occurrence__header--link button--icon">
                    <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                </a>
            </div>            
            <div class="occurrence__content">
                <div class="occurrence__content--ticket">
                    <mc-icon name="date"></mc-icon> <?= i::_e('Data da ocorrência') ?>
                </div>
                <div class="occurrence__content--price">
                    <mc-icon name="ticket"></mc-icon> <?= i::_e('Detalhes da entrada') ?>
                </div>
            </div>
            <div class="occurrence__actions">
                <a class="occurrence__actions--edit"> <mc-icon name="edit"></mc-icon><?= i::_e('Editar') ?> </a>
                <a class="occurrence__actions--delete"> <mc-icon name="trash"></mc-icon><?= i::_e('Excluir') ?> </a>
            </div>
        </div>

        <div class="occurrence">
            <div class="occurrence__header">
                <div class="occurrence__header--title">
                    <mc-icon name="pin"></mc-icon> <?= i::_e('Local da ocorrência') ?>
                </div>
                <a class="occurrence__header--link button--icon">
                    <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                </a>
            </div>            
            <div class="occurrence__content">
                <div class="occurrence__content--ticket">
                    <mc-icon name="date"></mc-icon> <?= i::_e('Data da ocorrência') ?>
                </div>
                <div class="occurrence__content--price">
                    <mc-icon name="ticket"></mc-icon> <?= i::_e('Detalhes da entrada') ?>
                </div>
            </div>
            <div class="occurrence__actions">
                <a class="occurrence__actions--edit"> <mc-icon name="edit"></mc-icon><?= i::_e('Editar') ?> </a>
                <a class="occurrence__actions--delete"> <mc-icon name="trash"></mc-icon><?= i::_e('Excluir') ?> </a>
            </div>
        </div>

        <div class="occurrence">
            <div class="occurrence__header">
                <div class="occurrence__header--title">
                    <mc-icon name="pin"></mc-icon> <?= i::_e('Local da ocorrência') ?>
                </div>
                <a class="occurrence__header--link button--icon">
                    <mc-icon name="map"></mc-icon> <?= i::_e('Ver mapa') ?>
                </a>
            </div>            
            <div class="occurrence__content">
                <div class="occurrence__content--ticket">
                    <mc-icon name="date"></mc-icon> <?= i::_e('Data da ocorrência') ?>
                </div>
                <div class="occurrence__content--price">
                    <mc-icon name="ticket"></mc-icon> <?= i::_e('Detalhes da entrada') ?>
                </div>
            </div>
            <div class="occurrence__actions">
                <a class="occurrence__actions--edit"> <mc-icon name="edit"></mc-icon><?= i::_e('Editar') ?> </a>
                <a class="occurrence__actions--delete"> <mc-icon name="trash"></mc-icon><?= i::_e('Excluir') ?> </a>
            </div>
        </div>

    </div>
</div>

<!-- <div class="create-occurrence">
    <div class="create-occurrence__header">
        <div class="create-occurrence__header--title">
            <div class="create-occurrence__title--header-left">
                <mc-icon name="pin"></mc-icon>
            </div>
            <div class="create-occurrence__title--header-name">
                <strong>{{entity.name}}</strong>
            </div>
        </div>

        <div class="create-occurrence__header--right">
            <div class="create-occurrence__header--right-icon">
                <mc-icon name="map"></mc-icon>
            </div>
            <div class="create-occurrence__header--right-label">
                <label>Ver Mapa</label>
            </div>
        </div>

    </div>
    <div class="create-occurrence__content">
        <div class="create-occurrence__content--ticket">

            <mc-icon name="date"></mc-icon> <label>Data do Evento</label>
        </div>
        <div class="create-occurrence__content--price">
            <mc-icon name="ticket"></mc-icon> <label>Ingresso</label>

        </div>
    </div>

</div>

<div class="occurrence-footer">
    <div class="occurrence-footer__content">
        <div class="occurrence-footer__content--edit">
            <div class="occurrence-footer__content--edit-icon">
                <mc-icon name="edit"></mc-icon>
            </div>
            <div class="occurrence-footer__content--edit-label">
                <label class="occurrence-footer__content--edit-label">Editar</label>
            </div>
        </div>
        <div class="occurrence-footer__content--trash">
            <div class="occurrence-footer__content--trash-icon">
                <mc-icon name="trash"></mc-icon>
            </div>
            <div class="occurrence-footer__content--trash-label">
                <label class="occurrence-footer__content--edit-label">Excluir</label>
            </div>
        </div>
    </div>
</div> -->
<?php

use MapasCulturais\i;

$this->import('entities mc-icon mc-link');
?>
<div class="create-occurrence">
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
</div>
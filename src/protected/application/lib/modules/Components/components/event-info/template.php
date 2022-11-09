<?php

use MapasCulturais\i;

$this->layout = 'entity';
$this->import('mapas-container mc-tag-list entity-location');
?>

<div class="event_info">

    <div class="acessibility">
        <label class="acessibility__label"><?php i::_e("Acessibilidade"); ?></label>
        <div v-if="entity.descricaoSonora" class="acessibility__audio">
            <span><?php i::_e("Libras:"); ?></span>{{entity.descricaoSonora}}
        </div>
        <div v-if="entity.traducaoLibras" class="acessibility__libras">
            <span><?php i::_e("Áudio de Descrição:"); ?></span> {{entity.traducaoLibras}}
        </div>
    </div>

    <div v-if="entity.event_attendance || entity.telefonePublico || entity.registrationInfo" class="event_info__infos">
        <span class="event_info--label acessibility__label"><?php i::_e("Informações adicionais"); ?></span>
        <div v-if="entity.event_attendance" class="event_info__infos--audio">
            <span><?php i::_e("TOTAL DE PÚBLICO:"); ?></span> {{entity.event_attendance}}
        </div>
        <div v-if="entity.telefonePublico" class="event_info__infos--libras">
            <span><?php i::_e("TELEFONE:"); ?></span> {{entity.telefonePublico}}
        </div>
        <div v-if="entity.registrationInfo" class="event_info__infos--libras">
            <span><?php i::_e("INFORMAÇÕES SOBRE AS INSCRIÇÕES:"); ?></span> {{entity.registrationInfo}}
        </div>

    </div>


</div>
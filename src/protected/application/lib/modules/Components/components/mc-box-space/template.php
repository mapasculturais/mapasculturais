<?php
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('mapas-container mc-tag-list entity-location');
?>

<div class="mc-box-space">
    <div v-if="entity.horario" class="mc-box-space__hour">
        <span class="mc-box-space--label"><?= i::_e("Horário de funcionamento"); ?></span>
        <div class="mc-box-space__hour--content">
            {{entity.horario}}
        </div>
    </div>

    <div class="mc-box-space__location">
        <entity-location :entity="entity"></entity-location>
    </div>

    <div v-if="entity.acessibilidade" class="mc-box-space__accessibility">
        <span class="mc-box-space--label"><?= i::_e("Recursos de acessibilidade"); ?></span>

        <mc-tag-list entity-type="space" :editable="editable" :tags="accessibilityResources()"></mc-tag-list>
    </div>

    <div v-if="entity.capacidade || entity.telefonePublico" class="mc-box-space__infos">
        <span class="mc-box-space--label"><?php i::_e("Informações adicionais"); ?></span>
        <div v-if="entity.capacidade" class="mc-box-space__infos--audio">
            <span><?php i::_e("Capacidade do espaço:"); ?></span> {{entity.capacidade}}
        </div>
        <div v-if="entity.telefonePublico" class="mc-box-space__infos--libras">
            <span><?php i::_e("Telefone:"); ?></span> {{entity.telefonePublico}}
        </div>
    </div>

    
</div>
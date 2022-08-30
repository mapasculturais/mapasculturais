<?php
use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('mapas-container mc-tag-list entity-location');
?>

<div class="space-info">
    <div v-if="entity.horario" class="space-info__hour">
        <span class="space-info--label"><?= i::_e("Horário de funcionamento"); ?></span>
        <div class="space-info__hour--content">
            {{entity.horario}}
        </div>
    </div>

    <div class="space-info__location">
        <entity-location :entity="entity"></entity-location>
    </div>

    <div v-if="entity.acessibilidade" class="space-info__accessibility">
        <span class="space-info--label"><?= i::_e("Recursos de acessibilidade"); ?></span>

        <mc-tag-list entity-type="space" :editable="editable" :tags="accessibilityResources()"></mc-tag-list>
    </div>

    <div v-if="entity.capacidade || entity.telefonePublico" class="space-info__infos">
        <span class="space-info--label"><?php i::_e("Informações adicionais"); ?></span>
        <div v-if="entity.capacidade" class="space-info__infos--audio">
            <span><?php i::_e("Capacidade do espaço:"); ?></span> {{entity.capacidade}}
        </div>
        <div v-if="entity.telefonePublico" class="space-info__infos--libras">
            <span><?php i::_e("Telefone:"); ?></span> {{entity.telefonePublico}}
        </div>
    </div>

    
</div>
<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity'; 

$this->import('
    mc-tag-list 
    entity-location
');
?>
<div class="space-info grid-12">
    <div v-if="entity.horario" class="space-info__hour col-12">
        <span class="space-info--label"><?= i::_e("Horário de funcionamento"); ?></span>
        <div class="space-info__hour--content">
            {{entity.horario}}
        </div>
    </div>

    <div class="space-info__location col-12">
        <entity-location :entity="entity"></entity-location>
    </div>

    <div v-if="entity.acessibilidade" class="space-info__accessibility col-12">
        <span class="space-info--label"><?= i::_e("Recursos de acessibilidade"); ?></span>

        <mc-tag-list entity-type="space" :editable="editable" :tags="accessibilityResources()"></mc-tag-list>
    </div>

    <div v-if="entity.capacidade || entity.telefonePublico" class="space-info__infos col-12">
        <span class="space-info--label"><?php i::_e("Informações adicionais"); ?></span>

        <div v-if="entity.capacidade" class="space-info__infos--capacidade">
            <?php i::_e("Capacidade do espaço:"); ?> <span>{{entity.capacidade}}</span>
        </div>

        <div v-if="entity.telefonePublico" class="space-info__infos--telefone">
            <?php i::_e("Telefone:"); ?> <span>{{entity.telefonePublico}}</span>
        </div>

        <div v-if="entity.emailPublico" class="space-info__infos--email">
            <?php i::_e("Email:"); ?> <span>{{entity.emailPublico}}</span>
        </div>

    </div>

    
</div>
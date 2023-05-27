<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-location
    mapas-container 
    mc-tag-list 
');
?>
<div class="event_info">
    <div class="event_info__single" v-if="!editable">
        <div v-if="entity.descricaoSonora || entity.traducaoLibras" class="acessibility">
            <label class="acessibility__label"><?php i::_e("Acessibilidade"); ?></label>
            <div v-if="entity.descricaoSonora" class="acessibility__audio">
                <span><?php i::_e("Libras:"); ?></span> {{entity.traducaoLibras}}
            </div>
            <div v-if="entity.traducaoLibras" class="acessibility__libras">
                <span><?php i::_e("Áudio de Descrição:"); ?></span> {{entity.descricaoSonora}}
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
    <div class="event-info" v-if="editable">
        <div class="event-info-edit">
            <label class="edit-label"><?php i::_e("Acessibilidade"); ?></label>
            <div class="event-info-edit__fields">
                <span class="event-info-edit__fields-label"><?php i::_e("Libras:"); ?></span>
                <div class="event-info-edit__fields--fields">
                    <label class="options"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" value="Sim" /> <?= i::_e('Sim') ?> </label>
                    <label class="options"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" value="Não" /> <?= i::_e('Não') ?> </label>
                    <label class="options"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" :checked="!entity.traducaoLibras"/> <?= i::_e('Não Informado') ?> </label>
                </div>
            </div>

            <div class="event-info-edit__fields">
                <span class="event-info-edit__fields-label"><?php i::_e("Áudio Descrição:"); ?></span>
                <div class="event-info-edit__fields--fields">
                    <label class="options"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" value="Sim" /> <?= i::_e('Sim') ?> </label>
                    <label class="options"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" value="Não" /> <?= i::_e('Não') ?> </label>
                    <label class="options"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" :checked="!entity.descricaoSonora" /> <?= i::_e('Não Informado') ?> </label>
                </div>
            </div>
        </div>
    </div>
</div>
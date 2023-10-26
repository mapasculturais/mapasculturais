<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';
?>

<div class="event-info" :class="classes">
    <div v-if="(entity.descricaoSonora || entity.traducaoLibras) && !editable" class="event-info__accessibility">
        <h4 class="event-info__title bold"><?= i::__("Acessibilidade"); ?></h4>
        <p v-if="entity.descricaoSonora" class="event-info__item">
            <span class="semibold uppercase"><?= i::__("Libras:"); ?></span> 
            <span class="event-info__value">{{entity.descricaoSonora}}</span>
        </p>
        <p v-if="entity.traducaoLibras" class="event-info__item">
            <span class="semibold uppercase"><?= i::__("Áudio de Descrição:"); ?></span>
            <span class="event-info__value">{{entity.traducaoLibras}}</span>
        </p>
    </div>
    <div v-if="(entity.event_attendance || entity.telefonePublico || entity.registrationInfo) && !editable" class="event-info__accessibility">
        <h4 class="bold"><?= i::__("Informações adicionais"); ?></h4>
        <p v-if="entity.event_attendance" class="event-info__item">
            <span class="semibold uppercase"><?= i::__("Total de público:"); ?></span>
            <span class="event-info__value">{{entity.event_attendance}}</span>
        </p>
        <p v-if="entity.telefonePublico" class="event-info__item">
            <span class="semibold uppercase"><?= i::__("telefone:"); ?></span> 
            <span class="event-info__value">{{entity.telefonePublico}}</span>
        </p>
        <p v-if="entity.registrationInfo" class="event-info__item">
            <span class="semibold uppercase"><?= i::__("Informações sobre a inscrição:"); ?></span>
            <span class="event-info__value">{{entity.registrationInfo}}</span>
        </p>
    </div>

    <div class="event-info__editable" v-if="editable">
        <h3 class="bold"><?php i::_e("Acessibilidade"); ?></h3>
        <div class="event-info__fields">
            <h6 class="semibold"><?php i::_e("Libras:"); ?></h6>
            <div class="event-info__group">
                <label class="event-info__field"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" value="Sim" /> <h6><?= i::_e('Sim') ?></h6> </label>
                <label class="event-info__field"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" value="Não" /> <h6><?= i::_e('Não') ?></h6> </label>
                <label class="event-info__field"> <input v-model="entity.traducaoLibras" type="radio" name="traducaoLibras" :checked="!entity.traducaoLibras"/> <h6><?= i::_e('Não Informado') ?></h6> </label>
            </div>
        </div>

        <div class="event-info__fields">
            <h6 class="semibold"><?php i::_e("Áudio Descrição:"); ?></h6>
            <div class="event-info__group">
                <label class="event-info__field"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" value="Sim" /> <h6><?= i::_e('Sim') ?></h6> </label>
                <label class="event-info__field"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" value="Não" /> <h6><?= i::_e('Não') ?></h6> </label>
                <label class="event-info__field"> <input v-model="entity.descricaoSonora" type="radio" name="descricaoSonora" :checked="!entity.descricaoSonora" /> <h6><?= i::_e('Não Informado') ?></h6> </label>
            </div>
        </div>
    </div>
</div>
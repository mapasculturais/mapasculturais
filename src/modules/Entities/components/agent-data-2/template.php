<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
     entity-data
');
?>
<div class="col-12 agent-data">
    <template v-if="entity.currentUserPermissions.viewPrivateData && verifyEntity()">
        <div v-if="entity.name" class="agent-data__fields">
            <entity-data v-if="entity.name" class="agent-data__fields--field" :entity="entity" prop="name" label="<?php i::_e("Razão social")?>"></entity-data>
            <entity-data v-if="entity.cpf" class="agent-data__fields--field" :entity="entity" prop="cpf" label="<?php i::_e("CPF")?>"></entity-data>
            <entity-data v-if="entity.cnpj" class="agent-data__fields--field" :entity="entity" prop="cnpj" label="<?php i::_e("CNPJ")?>"></entity-data>
            <entity-data v-if="entity.telefonePublico" class="agent-data__fields--field" :entity="entity" prop="telefonePublico" label="<?php i::_e("Telefone Público")?>"></entity-data>
            <entity-data v-if="entity.telefone1" class="agent-data__fields--field" :entity="entity" prop="telefone1" label="<?php i::_e("Telefone Privado 1")?>"></entity-data>
            <entity-data v-if="entity.telefone2" class="agent-data__fields--field" :entity="entity" prop="telefone2" label="<?php i::_e("Telefone Privado 2")?>"></entity-data>
            <entity-data v-if="entity.emailPrivado" class="agent-data__fields--field" :entity="entity" prop="emailPrivado" label="<?php i::_e("E-mail privado")?>"></entity-data>
            <entity-data v-if="entity.emailPublico" class="agent-data__fields--field" :entity="entity" prop="emailPublico" label="<?php i::_e("E-mail Público")?>"></entity-data>
        </div>
    </template>
</div>
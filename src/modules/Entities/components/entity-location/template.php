<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    entity-map
');
?>

<?php $this->applyTemplateHook('entity-location','before'); ?>
<div :class="classes" class="entity-location grid-12">
    <?php $this->applyTemplateHook('entity-location','begin'); ?>
    <div v-if="!hideLabel" class="entity-location__title col-12">
        <label v-if="verifiedAdress()"><?= i::__('Endereço')?></label>
    </div>
    <div class="col-12" v-if="editable">
        <div class="grid-12">
            <entity-field @change="address(); pesquisacep(entity.En_CEP);" classes="col-4 sm:col-12" :entity="entity" prop="En_CEP"></entity-field>
            <entity-field @change="address()" classes="col-8 sm:col-12" :entity="entity" prop="En_Nome_Logradouro"></entity-field>
            <entity-field @change="address()" classes="col-2 sm:col-4" :entity="entity" prop="En_Num"></entity-field>
            <entity-field @change="address()" classes="col-10 sm:col-8" :entity="entity" prop="En_Bairro"></entity-field>
            <entity-field @change="address()" classes="col-12" :entity="entity" prop="En_Complemento" label="<?php i::_e('Complemento ou ponto de referência')?>"></entity-field>
            <entity-field @change="address()" classes="col-12" :entity="entity" prop="En_Pais" label="<?php i::_e('País')?>"></entity-field>
        </div>
    </div>

    <div class="col-12" v-if="editable && !statesAndCitiesEnable">
        <div class="grid-12" v-if="!entity.En_Pais || entity.En_Pais == statesAndCitiesCountryCode">
            <entity-field @change="address()" classes="col-6 sm:col-12" :entity="entity" prop="En_Estado" label="<?php i::_e('Estado')?>"></entity-field>
            <entity-field @change="address()" classes="col-6 sm:col-12" :entity="entity" prop="En_Municipio" label="<?php i::_e('Município')?>"></entity-field>
        </div>            
    </div>
 
    <div class="col-12" v-if="editable && statesAndCitiesEnable">
        <div class="grid-12" v-if="!entity.En_Pais || entity.En_Pais == statesAndCitiesCountryCode">
            <div class="field col-6">
                <label class="field__title">
                    <?php i::_e('Estado')?>
                    <span v-if="isRequired('En_Estado')" class="required">*<?php i::_e('obrigatório') ?></span>
                </label>
                <select @change="citiesList(); address()" v-model="entity.En_Estado">
                    <option v-for="state in states" :value="state.value">{{state.label}}</option>
                </select>
            </div>
            <div class="field col-6">
                <label class="field__title">
                    <?php i::_e('Município')?>
                    <span v-if="isRequired('En_Municipio')" class="required">*<?php i::_e('obrigatório') ?></span>
                </label>
                <select @change="address()" v-model="entity.En_Municipio">
                    <option v-for="city in cities" :value="city">{{city}}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="grid-12" v-if="entity.En_Pais && entity.En_Pais != statesAndCitiesCountryCode">
            <div class="field col-6">
                    <label class="field__title">
                    <?php i::_e('Estado')?>
                    <span v-if="isRequired('En_Estado')" class="required">*<?php i::_e('obrigatório') ?></span>
                </label>
                <input :id="propId('En_Estado')" v-model="entity.En_Estado" type="text" @change="address()" autocomplete="off">
            </div>

            <div class="field col-6">
                <label class="field__title">
                    <?php i::_e('Município')?>
                    <span v-if="isRequired('En_Municipio')" class="required">*<?php i::_e('obrigatório') ?></span>
                </label>
                <input v-model="entity.En_Municipio" :id="propId('En_Municipio')"  type="text" @change="address()" autocomplete="off">
            </div>
        </div>
    </div>

    <div class="col-12" v-if="editable && hasPublicLocation">
        <div class="col-6 sm:col-12 public-location">
            <entity-field  @change="address()" type="checkbox" classes="public-location__field col-6" :entity="entity" prop="publicLocation" label="<?php i::esc_attr_e('Localização pública')?>"></entity-field>
            <label class="public-location__label col-12"><?php i::_e('Marque o campo acima para tornar o endereço público ou deixe desmarcado para manter o endereço privado.')?></label>
        </div>
    </div>
    <div v-if="verifiedAdress()" class="col-12">
        <p class="entity-location__address">
            <span v-if="entity.endereco">{{entity.endereco}}</span>
            <span v-if="!entity.endereco"><?= i::_e("Sem Endereço"); ?></span>
        </p>
        <entity-map  :entity="entity" :editable="editable"></entity-map>
    </div>
    <?php $this->applyTemplateHook('entity-location','end'); ?>
</div>
<?php $this->applyTemplateHook('entity-location','after'); ?>

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

<div class="international-address-form a">
    <?php $this->applyTemplateHook('international-address-form','before'); ?>
    <div class="grid-12">
        <?php $this->applyTemplateHook('international-address-form','begin'); ?>

        <entity-field class="col-6 sm:col-12" :entity="entity" prop="address_postalCode"></entity-field>
    
        <template v-for="i in [0, 1, 2, 3, 4, 5]">
            <div v-if="getLevel(i) && showSubLevelSelect(getLevel(i), i)" class="field col-6 sm:col-12">
                <label class="field__title">{{ fieldLabel(String(i+1)) }}</label>
                <select v-model="selectedLevels[i+1]" @change="clearSubLevels(i+1); address();">
                    <option v-for="(level, index) in getLevel(i).subLevels" :value="index">{{ level.label }}</option>
                </select>
            </div>
        </template>
    
        <template v-if="!levelHierarchy" v-for="(level, index) in activeLevels" :key="index">
            <entity-field v-if="!getLevel(index)" :class="getColumnClass(index, Object.keys(activeLevels))" class="sm:col-12" :entity="entity" :prop="`address_level${index}`" @change="address()"></entity-field>
        </template>
    
        <entity-field class="col-12" :entity="entity" prop="address_line1"></entity-field>
        <entity-field class="col-12" :entity="entity" prop="address_line2"></entity-field>

        <div class="col-12" v-if="hasPublicLocation">
            <div class="col-6 sm:col-12 field public-location">
                <entity-field  @change="address()" type="checkbox" classes="public-location__field col-6" :entity="entity" prop="publicLocation" label="<?php i::esc_attr_e('Localização pública')?>">
                    <?php if($this->isEditable()): ?>
                        <template #info>
                            <?php $this->info('cadastro -> configuracoes-entidades -> localizacao-publica') ?>
                        </template>
                    <?php endif; ?>
                </entity-field>

                <small class="field__description">
                    <?php i::_e('Marque o campo acima para tornar o endereço público ou deixe desmarcado para manter o endereço privado.')?>
                </small>
            </div>
        </div>
        
        <div class="col-12">
            <p class="international-address-form__address">
                <span v-if="showAddress()">{{showAddress()}}</span>
                <span v-else><?= i::_e("Sem Endereço"); ?></span>
            </p>
            <entity-map :entity="entity" editable></entity-map>
        </div>

        <?php $this->applyTemplateHook('international-address-form','end'); ?>
    </div>
    <?php $this->applyTemplateHook('international-address-form','after'); ?>
</div>

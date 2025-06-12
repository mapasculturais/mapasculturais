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

<?php $this->applyTemplateHook('international-address-form','before'); ?>
<div :class="classes" class="international-address-form grid-12">
    <?php $this->applyTemplateHook('international-address-form','begin'); ?>
    <div class="col-12">
        <entity-field :entity="entity" prop="address_postalCode"></entity-field>
    
        <div v-for="i in [0, 1, 2, 3, 4, 5]">
            <div v-if="getLevel(i) && showSubLevelSelect(getLevel(i), i)" class="field col-6">
                <label class="field__title">{{ fieldLabel(String(i+1)) }}</label>
                <select v-model="selectedLevels[i+1]" @change="clearSubLevels(i+1); address();">
                    <option v-for="(level, index) in getLevel(i).subLevels" :value="index">{{ level.label }}</option>
                </select>
            </div>
        </div>
    
    
        <div v-for="(level, index) in activeLevels" :key="index">
            <div v-if="!levelHierarchy && !getLevel(index)" class="field col-6">
                <entity-field :entity="entity" :prop="`address_level${index}`" @change="address()"></entity-field>
            </div>
        </div>
        
    
       <entity-field :entity="entity" prop="address_line1"></entity-field>
       <entity-field :entity="entity" prop="address_line2"></entity-field>
    </div>

    <div class="col-12">
        <div class="col-6 sm:col-12 public-location">
            <entity-field  @change="address()" type="checkbox" classes="public-location__field col-6" :entity="entity" prop="publicLocation" label="<?php i::esc_attr_e('Localização pública')?>">
                <template #info>
                    <?php $this->info('cadastro -> configuracoes-entidades -> localizacao-publica') ?>
                </template>
            </entity-field>
            <label class="public-location__label col-12"><?php i::_e('Marque o campo acima para tornar o endereço público ou deixe desmarcado para manter o endereço privado.')?></label>
        </div>
    </div>
    
    <div class="col-12">
        <p class="international-address-form__address">
            <span v-if="entity.address">{{entity.address}}</span>
            <span v-if="!entity.address"><?= i::_e("Sem Endereço"); ?></span>
        </p>
        <entity-map :entity="entity"></entity-map>
    </div>

    <?php $this->applyTemplateHook('international-address-form','end'); ?>
</div>
<?php $this->applyTemplateHook('international-address-form','after'); ?>

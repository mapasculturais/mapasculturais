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

<?php $this->applyTemplateHook('brasil-address-view','before'); ?>
<div :class="classes" class="brasil-address-view grid-12">
    <?php $this->applyTemplateHook('brasil-address-view','begin'); ?>
    <div v-if="!hideLabel" class="brasil-address-view__title col-12">
        <label v-if="verifiedAdress()"><?= i::__('Endereço')?></label>
        <?php if($this->isEditable()): ?>
            <?php $this->info('cadastro -> configuracoes-entidades -> endereco') ?>
        <?php endif; ?>
    </div>

    <div v-if="verifiedAdress()" class="col-12">
        <p class="brasil-address-view__address">
            <span v-if="entity.endereco">{{entity.endereco}}</span>
            <span v-if="!entity.endereco"><?= i::_e("Sem Endereço"); ?></span>
        </p>
        <entity-map  :entity="entity" :editable="editable"></entity-map>
    </div>
    <?php $this->applyTemplateHook('brasil-address-view','end'); ?>
</div>
<?php $this->applyTemplateHook('brasil-address-view','after'); ?>

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

<?php $this->applyTemplateHook('international-address-view','before'); ?>
<div :class="classes" class="international-address-view grid-12">
    <?php $this->applyTemplateHook('international-address-view','begin'); ?>
    <div v-if="!hideLabel" class="international-address-view__title col-12">
        <label v-if="verifiedAdress()"><?= i::__('Endereço')?></label>
        <?php if($this->isEditable()): ?>
            <?php $this->info('cadastro -> configuracoes-entidades -> endereco') ?>
        <?php endif; ?>
    </div>

    <div v-if="verifiedAdress()" class="col-12">
        <p class="international-address-view__address">
            <span v-if="entity.address">{{entity.address}}</span>
            <span v-if="!entity.address"><?= i::_e("Sem Endereço"); ?></span>
        </p>
        <entity-map :entity="entity" :editable="editable"></entity-map>
    </div>
    <?php $this->applyTemplateHook('international-address-view','end'); ?>
</div>
<?php $this->applyTemplateHook('international-address-view','after'); ?>

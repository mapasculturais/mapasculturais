<?php

use PhpOffice\PhpSpreadsheet\Calculation\Financial\CashFlow\Constant\Periodic\Payments;

$types = $app->getRegisteredEntityTypes($entity_classname);
if (!$types) {
    return;
}

usort($types, function($a, $b ){
    return $a->name > $b->name;
});

$isAgentTypes = ("agente" == strtolower($entity_classname::getEntityTypeLabel()));

$_title = empty($definition['label']) ? \MapasCulturais\i::esc_attr__("Tipo") : $definition['label'];
?>
<?php $this->part("modal/title", ['title' => $_title]); ?>
<select name='type'>
    <?php foreach ($types as $_type) {

        if($isAgentTypes && $_type->id == 1){
            continue;
        }

        if (is_object($_type)) { ?>
            <option value="<?php echo $_type->id; ?>"> <?php echo $_type->name; ?> </option>
            <?php
        }
    } ?>
</select>

<?php

if ($isAgentTypes) {
    $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['modal_id' => $modal_id]);
}

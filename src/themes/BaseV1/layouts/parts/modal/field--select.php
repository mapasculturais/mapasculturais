<?php 
use MapasCulturais\i;

$types = $app->getRegisteredEntityTypes($entity_classname);
if (!$types) {
    return;
}

$_title = empty($definition['label']) ? i::__("Tipo de avaliação") : $definition['label'];
?>

<?php $this->part("modal/title", ['title' => $_title]); ?>

<select name='evaluationMethod'>
    <option value=""><?= i::__("Selecione"); ?></option>

    <?php foreach ($evaluation_methods as $method) {?>

            <option value="<?=$method->slug ?>"> <?php echo $method->name; ?> </option>
    <?php } ?>
</select>

<?php


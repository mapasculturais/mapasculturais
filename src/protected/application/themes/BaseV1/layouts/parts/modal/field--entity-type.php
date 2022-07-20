<?php 
$types = $app->getRegisteredEntityTypes($entity_classname);
if (!$types) {
    return;
}

usort($types, function($a, $b ){
    return $a->name > $b->name;
});

$_title = empty($definition['label']) ? \MapasCulturais\i::esc_attr__("Tipo") : $definition['label'];
?>
<?php $this->part("modal/title", ['title' => $_title]); ?>
<select name='type'>
    <?php foreach ($types as $_type) {
        if (is_object($_type)) { ?>
            <option value="<?php echo $_type->id; ?>"> <?php echo $_type->name; ?> </option>
            <?php
        }
    } ?>
</select>

<?php
if (("agente" == strtolower($entity_classname::getEntityTypeLabel()))) {
    $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['modal_id' => $modal_id]);
}

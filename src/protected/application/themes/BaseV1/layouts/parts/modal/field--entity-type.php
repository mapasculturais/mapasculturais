<?php 
$types = $app->getRegisteredEntityTypes($new_entity);
if (!$types) {
    return;
}

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
if (("agente" == strtolower($new_entity->getEntityTypeLabel()))) {
    $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['modal_id' => $modal_id]);
}

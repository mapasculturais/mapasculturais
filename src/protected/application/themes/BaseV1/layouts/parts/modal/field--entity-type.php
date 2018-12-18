<?php 
$types = $app->getRegisteredEntityTypes($new_entity);
if (!$types) {
    return;
}
?>
<?php $this->part("modal/title", ['title' => $definition['label']]); ?>
<select name='type'>
    <?php foreach ($types as $typo) {
        if (is_object($typo)) { ?>
            <option value="<?php echo $typo->id; ?>"> <?php echo $typo->name; ?> </option>
            <?php
        }
    } ?>
</select>

<?php
if (("agente" == strtolower($new_entity->getEntityTypeLabel()))) {
    $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['modal_id' => $modal_id]);
}

<select name='type'>
    <?php foreach ($types as $tipo) {
        if (is_object($tipo)) { ?>
            <option value="<?php echo $tipo->id; ?>"> <?php echo $tipo->name; ?> </option>
            <?php
        }
    } ?>
</select>
<?php
if ("agente" == strtolower($entity->getEntityTypeLabel())) {
    $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['entity'=> $entity, 'modal_id' => $modal_id]);
}

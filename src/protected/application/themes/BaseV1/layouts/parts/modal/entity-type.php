<select name='type'>
    <?php foreach ($types as $tipo) {
        if (is_object($tipo)) { ?>
            <option value="<?php echo $tipo->id; ?>"> <?php echo $tipo->name; ?> </option>
            <?php
        }
    } ?>
</select>

<?php $app->applyHook('mapasculturais.add_entity_modal.tipologias_agentes', ['modal_id' => $modal_id]); ?>
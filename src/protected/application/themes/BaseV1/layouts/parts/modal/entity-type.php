<select name='type'>
    <?php foreach ($types as $tipo) {
        if (is_object($tipo)) { ?>
            <option value="<?php echo $tipo->id; ?>"> <?php echo $tipo->name; ?> </option>
            <?php
        }
    } ?>
</select>

<?php $this->applyHook('entity-type','after'); ?>
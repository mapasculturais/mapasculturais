<?php
if (isset($meta) && is_object($meta)) {
    $_title = $meta->label;
    $_key = $meta->key;
    $tipo = $meta->type;
    $options = $meta->config['options'];
    ?>

    <div class="<?php echo $class; ?>">
        <?php
        $this->part("modal/title", ['title' => $_title]);

        if ($tipo === "select" && is_array($meta->config)) {
            $this->part("modal/entity-dropdown", ['attr' => $_key, 'options' => $options]);
        } else if ($tipo === "string" || $tipo === "int") { ?>
            <input type='text' name='<?php echo $_key; ?>' placeholder='<?php \MapasCulturais\i::esc_attr_e('Campo obrigatório')?>' required />
            <?php
        } else if ($tipo === "text") { ?>
            <textarea name='<?php echo $_key; ?>' maxlength='400'></textarea> <br>
            <?php
        } else if ($tipo === "multiselect" && is_array($meta->config)) {
            foreach ($options as $option) { ?>
                <label for='<?php echo $_key; ?>'> <?php echo $option; ?> </label>
                <input type='checkbox' name='<?php echo $_key; ?>' value='<?php echo $option; ?>'> <br>
                <?php
            }
        }
        ?>
    </div>
<?php
}
<?php if (!empty($attr) && is_array($options) && !empty($options)) { ?>
    <select name="<?php echo $attr; ?>">
        <?php foreach ($options as $option) { ?>
            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
        <?php } ?>
    </select>
<?php
}

<?php
if (!empty($title) && !empty($attr) && is_array($options) && !empty($options)) {
    $this->part("modal/title", ['title' => $title]); ?>
    <select name="<?php echo $attr; ?>" id="">
        <?php foreach ($options as $option) { ?>
            <option value="<?php echo $option; ?>"><?php echo $option; ?></option>
        <?php } ?>
    </select>
<?php
}

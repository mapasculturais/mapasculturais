<?php 
$class = $class ?? '';

if ($active ?? false) {
    $class .= $class ? ' active' : 'active';
}

$props = [];
foreach($properties ?? [] as $key => $val) {
    $val = htmlentities($val);
    $props[] = "{$key}=\"$val\"";
}
?>
<?php $this->applyTemplateHook("tab-{$id}", 'before') ?>
<li class="<?=$class?>" <?=implode(' ', $props)?>><a href="#<?=$id?>" rel='noopener noreferrer'><?=$label?></a></li>
<?php $this->applyTemplateHook("tab-{$id}", 'after') ?>
<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$areas = array_values($app->getRegisteredTaxonomy($entityClass, 'area')->restrictedTerms);
sort($areas);
?>
<div class="widget">
    <h3><?php $this->dict('taxonomies:area: name') ?></h3>
    <?php if($this->isEditable()): ?>
        <span id="term-area" class="js-editable-taxonomy" data-original-title="<?php $this->dict('taxonomies:area: name') ?>" data-emptytext="<?php $this->dict('taxonomies:area: select at least one') ?>" data-restrict="true" data-taxonomy="area"><?php echo implode('; ', $entity->terms['area'])?></span>
    <?php else: ?>
        <?php
        foreach($areas as $i => $t): if(in_array($t, $entity->terms['area'])): ?>
            <a class="tag tag-<?php echo $this->controller->id ?>" href="<?php echo $app->createUrl('site', 'search') ?>##(<?php echo $entityName ?>:(areas:!(<?php echo $i ?>)),global:(enabled:(<?php echo $entityName ?>:!t),filterEntity:<?php echo $entityName ?>))">
                <?php echo $t ?>
            </a>
        <?php endif; endforeach; ?>
    <?php endif;?>
</div>
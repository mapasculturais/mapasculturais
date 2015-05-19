<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';
$tags = $entity->terms['tag'];
?>
<?php if($this->isEditable() || !empty($tags)): ?>
    <div class="widget">
        <h3>Tags</h3>
        <?php if($this->isEditable()): ?>
            <span id="term-area" class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode('; ', $entity->terms['tag'])?></span>
        <?php else: ?>
            <?php
            foreach($tags as $i => $t): ?>
                <a class="tag tag-<?php echo $this->controller->id ?>" href="<?php echo $app->createUrl('site', 'search') ?>##(<?php echo $entityName ?>:(keyword:'<?php echo $t ?>'),global:(enabled:(<?php echo $entityName ?>:!t),filterEntity:<?php echo $entityName ?><?php echo $viewModeString; ?>))">
                    <?php echo $t ?>
                </a>
            <?php endforeach; ?>
        <?php endif;?>
    </div>
<?php endif; ?>
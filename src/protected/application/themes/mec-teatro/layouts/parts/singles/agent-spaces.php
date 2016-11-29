<?php if(count($entity->spaces) > 0): ?>
<div class="widget">
    <h3><?php $this->dict('entities: Spaces of the agent') ?></h3>
    <ul class="widget-list js-slimScroll">
        <?php foreach($entity->spaces as $space): ?>
        <?php if (is_object($space->type) && $space->type->id >= 30 && $space->type->id <= 39): ?>
            <li class="widget-list-item"><a href="<?php echo $app->createUrl('space', 'single', array('id' => $space->id)) ?>"><span><?php echo $space->name; ?></span></a></li>
        <?php endif; ?> 
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

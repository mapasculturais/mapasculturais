<?php if($this->controller->action !== 'create'): ?>
    <div class="widget">
        <?php if($entity->children && $entity->children->count()): ?>
        <h3><?php $this->dict('entities: Children spaces') ?></h3>
        <ul class="js-slimScroll widget-list">
            <?php foreach($entity->children as $space): ?>
            <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php if($entity->id && $entity->canUser('createChild')): ?>
        <a class="btn btn-default add" href="<?php echo $app->createUrl('space','create', array('parentId' => $entity->id)) ?>"><?php $this->dict('entities: Add child space') ?></a>
        <?php endif; ?>
    </div>
<?php endif; ?>
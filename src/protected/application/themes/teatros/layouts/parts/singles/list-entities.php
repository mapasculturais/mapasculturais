
    <?php if(count($entities) > 0): ?>
    <div class="widget">
        <h3><?php $this->dict($title); ?></h3>
        <ul class="widget-list js-slimScroll">
            <?php foreach($entities as $entity): ?>
                <?php if ( ( is_object($entity->type) && $entity->type->id >= 30 && $entity->type->id <= 39 ) || get_class($entity) != 'MapasCulturais\Entities\Space'): ?>
                    <li class="widget-list-item"><a href="<?php echo $entity->singleUrl; ?>"><span><?php echo $entity->name; ?></span></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

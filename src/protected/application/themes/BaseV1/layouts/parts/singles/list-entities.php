
    <?php if(is_array($entities) && count($entities) > 0): ?>
    <div class="widget">
        <h3>Projetos</h3>
        <h3><?php $this->dict($title); ?></h3>
        <ul class="widget-list js-slimScroll">
            <?php foreach($entities as $entity): ?>
            <li class="widget-list-item"><a href="<?php echo $entity->singleUrl; ?>"><span><?php echo $entity->name; ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

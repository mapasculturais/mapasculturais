<?php if(is_editable() || !empty($entity->terms['tag'])): ?>
    <div class="widget">
        <h3>Tags</h3>
        <?php if(is_editable()): ?>
            <span class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode('; ', $entity->terms['tag'])?></span>
        <?php else: ?>
            <?php foreach($entity->terms['tag'] as $i => $term): ?>
                <a class="tag tag-<?php echo $this->controller->id ?>" href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[tags][]=<?php echo $term ?>"><?php echo $term ?></a>
            <?php endforeach; ?>
        <?php endif;?>
    </div>
<?php endif; ?>
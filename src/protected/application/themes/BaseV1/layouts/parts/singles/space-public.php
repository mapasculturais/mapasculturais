<div class="widget">
    <h3><?php \MapasCulturais\i::_e("Status");?></h3>
    <?php if($this->isEditable()): ?>
        <div id="editable-space-status" class="js-editable" data-edit="public" data-type="select" data-value="<?php echo $entity->public ? '1' : '0' ?>"  data-source="[{value: 0, text: 'Publicação restrita - requer autorização para criar eventos'},{value: 1, text:'Publicação livre - qualquer pessoa pode criar eventos'}]">
            <?php if ($entity->public) : ?>
                <div class="venue-status"><div class="icon icon-publication-status-open"></div><?php \MapasCulturais\i::_e("Publicação livre");?></div>
                <p class="venue-status-definition"><?php \MapasCulturais\i::_e("Qualquer pessoa pode criar eventos.");?></p>
            <?php else: ?>
                <div class="venue-status"><div class="icon icon-publication-status-locked"></div><?php \MapasCulturais\i::_e("Publicação restrita");?></div>
                <p class="venue-status-definition"><?php \MapasCulturais\i::_e("Requer autorização para criar eventos.");?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php if ($entity->public) : ?>
            <div class="venue-status"><div class="icon icon-publication-status-open"></div><?php \MapasCulturais\i::_e("Publicação livre");?></div>
            <p class="venue-status-definition"><?php \MapasCulturais\i::_e("Qualquer pessoa pode criar eventos.");?></p>
        <?php else: ?>
            <div class="venue-status"><div class="icon icon-publication-status-locked"></div><?php \MapasCulturais\i::_e("Publicação restrita");?></div>
            <p class="venue-status-definition"><?php \MapasCulturais\i::_e("Requer autorização para criar eventos.");?></p>
        <?php endif; ?>
    <?php endif; ?>
</div>
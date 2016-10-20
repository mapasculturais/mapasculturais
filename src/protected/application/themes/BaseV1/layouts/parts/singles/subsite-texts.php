<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit'; ?>
<div id="texts" class="aba-content">
    <?php foreach(\MapasCulturais\Themes\BaseV1\Theme::_dict() as $key => $def):  $skey = str_replace(' ', '+', $key); ?>
        <p>
            <span class="label"><?php echo $def['name'] ?>: </span>
            <span class="js-editable" data-edit="<?php echo "dict:" . $skey ?>" data-original-title="<?php echo htmlentities($def['name']) ?>" data-emptytext="<?php echo htmlentities($def['description'] ? $def['description'] : $def['name']) ?>"><?php echo isset($entity->dict[$key]) ? $entity->dict[$key] : ''; ?></span>
        </p>
    <?php endforeach; ?>
    
</div>

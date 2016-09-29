<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit'; ?>
<div id="sobre" class="aba-content">
    <?php if($this->isEditable() || $entity->url_parent): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"url_parent") && $editEntity? 'required': '');?>">URL pai: </span>
            <span class="js-editable" data-edit="url_parent" data-original-title="URL pai" data-emptytext="Ex: http://mapas.cultura.gov.br"><?php echo $entity->url_parent; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"titulo") && $editEntity? 'required': '');?>">Título: </span>
            <span class="js-editable" data-edit="titulo" data-original-title="Título" data-emptytext="Escreva título da página inicial"><?php echo $entity->titulo; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->texto_boasvindas): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"texto_boasvindas") && $editEntity? 'required': '');?>">Texto de boas vindas: </span>
            <span class="js-editable" data-edit="texto_boasvindas" data-original-title="Text de boas vindas" data-emptytext="Escreva um texto de boas vindas de até x caracteres..."><?php echo $entity->texto_boasvindas; ?></span>
        </p>
    <?php endif; ?>

    <?php /*$this->part('widget-tags', array('entity'=>$entity)); */ ?>

    <?php if($this->isEditable() || $entity->texto_sobre): ?>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"texto_sobre") && $editEntity? 'required': '');?>">Texto "sobre": </span>
            <span class="js-editable" data-edit="texto_sobre" data-original-title="Texto sobre" data-emptytext="Escreva um texto e descrição de até x caracteres..."><?php echo $entity->texto_sobre; ?></span>
        </p>
    <?php endif; ?>
</div>

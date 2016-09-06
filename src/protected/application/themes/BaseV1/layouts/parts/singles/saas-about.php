<div id="sobre" class="aba-content">
    <?php if($this->isEditable() || $entity->texto_boasvindas): ?>
        <p>
            <span class="label">Texto de boas vindas: </span>
            <span class="js-editable" data-edit="texto_boasvindas" data-original-title="Text de boas vindas" data-emptytext="Escreva um texto de boas vindas de até x caracteres..."><?php echo $entity->texto_boasvindas; ?></span>
        </p>
    <?php endif; ?>

    <?php /*$this->part('widget-tags', array('entity'=>$entity)); */ ?>

    <?php if($this->isEditable() || $entity->texto_sobre): ?>
        <p>
            <span class="label">Texto "sobre": </span>
            <span class="js-editable" data-edit="texto_sobre" data-original-title="Text sobre" data-emptytext="Escreva um texto e descrição de até x caracteres..."><?php echo $entity->texto_sobre; ?></span>
        </p>
    <?php endif; ?>
</div>

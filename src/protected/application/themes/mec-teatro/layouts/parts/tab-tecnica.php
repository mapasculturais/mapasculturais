<div id="tab-tecnica" class="aba-content">
    <div class="tecnica">

        <?php if($this->isEditable() || $entity->teatros_aforo): ?>
        <p>
            <span class="label">Aforo:</span>
            <span class="js-editable" data-edit="teatros_aforo" data-original-title="Aforo" data-emptytext="Aforo">
                <?php echo $entity->teatros_aforo; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_boca_escenario): ?>
        <p>
            <span class="label">Boca de escenario (en metros):</span>
            <span class="js-editable" data-edit="teatros_boca_escenario" data-original-title="Boca de escenario (en metros)" data-emptytext="Boca de escenario (en metros)">
                <?php echo $entity->teatros_boca_escenario; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_profundidad): ?>
        <p>
            <span class="label">Profundidad (en metros):</span>
            <span class="js-editable" data-edit="teatros_profundidad" data-original-title="Profundidad (en metros)" data-emptytext="Profundidad (en metros)">
                <?php echo $entity->teatros_profundidad; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_aforo): ?>
        <p>
            <span class="label">Aforo:</span>
            <span class="js-editable" data-edit="teatros_aforo" data-original-title="Aforo" data-emptytext="Aforo">
                <?php echo $entity->teatros_aforo; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_altura): ?>
        <p>
            <span class="label">Altura (en metros):</span>
            <span class="js-editable" data-edit="teatros_altura" data-original-title="Altura (en metros)" data-emptytext="Altura (en metros)">
                <?php echo $entity->teatros_altura; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_aforo): ?>
        <p>
            <span class="label">Aforo:</span>
            <span class="js-editable" data-edit="teatros_aforo" data-original-title="Aforo" data-emptytext="Aforo">
                <?php echo $entity->teatros_aforo; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_piso): ?>
        <p>
            <span class="label">Piso:</span>
            <span class="js-editable" data-edit="teatros_piso" data-original-title="Piso" data-emptytext="Piso">
                <?php echo $entity->teatros_piso; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_equipamento_lumnico): ?>
        <p>
            <span class="label">Equipamento Lumnico:</span>
            <span class="js-editable" data-edit="teatros_equipamento_lumnico" data-original-title="Equipamento Lumnico" data-emptytext="Equipamento Lumnico">
                <?php echo $entity->teatros_equipamento_lumnico; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_equipamento_sonido): ?>
        <p>
            <span class="label">Equipamento de Sonido:</span>
            <span class="js-editable" data-edit="teatros_equipamento_sonido" data-original-title="Equipamento de Sonido" data-emptytext="Equipamento de Sonido">
                <?php echo $entity->teatros_equipamento_sonido; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if($this->isEditable() || $entity->teatros_equipamento_audiovisual): ?>
        <p>
            <span class="label">Equipamento Audiovisual:</span>
            <span class="js-editable" data-edit="teatros_equipamento_audiovisual" data-original-title="Equipamento Audiovisual" data-emptytext="Equipamento Audiovisual">
                <?php echo $entity->teatros_equipamento_audiovisual; ?>
            </span>
        </p>
        <?php endif; ?>


    </div>
</div>

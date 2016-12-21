<div id="tab-tecnica" class="aba-content">
    <div class="tecnica servico">

        <?php if($this->isEditable() || $entity->teatros_aforo): ?>
        <p>
            <span class="label">Aforo:</span>
            <span class="js-editable" data-edit="teatros_aforo" data-original-title="Aforo" data-emptytext="Aforo">
                <?php echo $entity->teatros_aforo; ?>
            </span>
        </p>
        <?php endif; ?>
        
        <?php if($this->isEditable() || $entity->teatros_aforo_detalles): ?>
        <p>
            <span class="label">Detalles del Aforo:</span>
            <p class="js-editable" data-edit="teatros_aforo_detalles" data-original-title="Detalles del Aforo" data-emptytext="Detalles del Aforo">
                <?php echo $this->isEditable() ? $entity->teatros_aforo_detalles : nl2br($entity->teatros_aforo_detalles); ?>
            </p>
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

        <?php if($this->isEditable() || $entity->teatros_altura): ?>
        <p>
            <span class="label">Altura (en metros):</span>
            <span class="js-editable" data-edit="teatros_altura" data-original-title="Altura (en metros)" data-emptytext="Altura (en metros)">
                <?php echo $entity->teatros_altura; ?>
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
        <hr />
        <?php if($this->isEditable() || $entity->teatros_equipamento_lumnico): ?>
        <p>
            <span class="label">Equipamiento Lumínico:</span>
            <p class="js-editable" data-edit="teatros_equipamento_lumnico" data-original-title="Equipamento Lumnico" data-emptytext="Equipamento Lumnico">
                <?php echo $this->isEditable() ? $entity->teatros_equipamento_lumnico : nl2br($entity->teatros_equipamento_lumnico); ?>
            </p>
        </p>
        <?php endif; ?>
        <hr />
        <?php if($this->isEditable() || $entity->teatros_equipamento_sonido): ?>
        <p>
            <span class="label">Equipamiento de Sonido:</span>
            <p class="js-editable" data-edit="teatros_equipamento_sonido" data-original-title="Equipamento de Sonido" data-emptytext="Equipamento de Sonido">
                <?php echo $this->isEditable() ? $entity->teatros_equipamento_sonido : nl2br($entity->teatros_equipamento_sonido); ?>
            </p>
        </p>
        <?php endif; ?>
        <hr />
        <?php if($this->isEditable() || $entity->teatros_equipamento_audiovisual): ?>
        <p>
            <span class="label">Equipamiento Audiovisual:</span>
            <p class="js-editable" data-edit="teatros_equipamento_audiovisual" data-original-title="Equipamento Audiovisual" data-emptytext="Equipamento Audiovisual">
                <?php echo $this->isEditable() ? $entity->teatros_equipamento_audiovisual : nl2br($entity->teatros_equipamento_audiovisual); ?>
            </p>
        </p>
        <?php endif; ?>
        <hr />
        <?php if($this->isEditable() || $entity->teatros_contactos_adicionales): ?>
        <p>
            <span class="label">Informaciones de contactos adicionales:</span>
            <p class="js-editable" data-edit="teatros_contactos_adicionales" data-original-title="Informaciones de contactos adicionales" data-emptytext="Informaciones de contactos adicionales">
                <?php echo $this->isEditable() ? $entity->teatros_contactos_adicionales : nl2br($entity->teatros_contactos_adicionales); ?>
            </p>
        </p>
        <?php endif; ?>


    </div>
</div>

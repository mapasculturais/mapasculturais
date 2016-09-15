<div id="entidades" class="aba-content">
    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
        <p>
            <span class="label">Entidades Habilitadas: </span>
            <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="false" box-title="Entidades habilitadas:"></editable-multiselect>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo_projetos): ?>
        <p>
            <span class="label">Título: Projetos </span>
            <span class="js-editable" data-edit="titulo_projetos" data-original-title="Título" data-emptytext="Título da entidade: Projetos"><?php echo $entity->titulo_projetos; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo_eventos): ?>
        <p>
            <span class="label">Título: Eventos </span>
            <span class="js-editable" data-edit="titulo_eventos" data-original-title="Título" data-emptytext="Título da entidade: eventos"><?php echo $entity->titulo_eventos; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo_agentes): ?>
        <p>
            <span class="label">Título: Agentes </span>
            <span class="js-editable" data-edit="titulo_agentes" data-original-title="Título" data-emptytext="Título da entidade: Agentes"><?php echo $entity->titulo_agentes; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo_espacos): ?>
        <p>
            <span class="label">Título: Espaços </span>
            <span class="js-editable" data-edit="titulo_espacos" data-original-title="Título" data-emptytext="Título da entidade: Espacos"><?php echo $entity->titulo_espacos; ?></span>
        </p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->titulo_selos): ?>
        <p>
            <span class="label">Título: Selos </span>
            <span class="js-editable" data-edit="titulo_selos" data-original-title="Título" data-emptytext="Título da entidade: Selos"><?php echo $entity->titulo_selos; ?></span>
        </p>
    <?php endif; ?>

    <p>
        <span class="label">Cores: </span>
        <span class="js-editable inline" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Agentes"><?php echo $entity->cor_agentes; ?></span>
        <span class="js-editable inline" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Espaços"><?php echo $entity->cor_espacos; ?></span>
        <span class="js-editable inline" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Projetos"><?php echo $entity->cor_projetos; ?></span>
        <span class="js-editable inline" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Eventos"><?php echo $entity->cor_eventos; ?></span>
        <span class="js-editable inline" data-edit="cor_selos" data-original-title="Selos" data-emptytext="Selos"><?php echo $entity->cor_selos; ?></span>
    </p>
</div>

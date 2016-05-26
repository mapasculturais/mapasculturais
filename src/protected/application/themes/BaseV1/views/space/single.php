<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('area');

    $this->addTaxonoyTermsToJs('tag');
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content space">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
        
        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
        
        <!--.header-image-->
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
            
            <?php $this->part('singles/type', ['entity' => $entity]) ?>
            
            <?php $this->part('entity-parent', ['entity' => $entity, 'child_entity_request' => $child_entity_request]) ?>

            <?php $this->part('singles/name', ['entity' => $entity]) ?>
            
            <?php $this->applyTemplateHook('header-content','end'); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>
    
    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>
    
    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning">el límite de caracteres de la descripción corta disminuyó a 400, pero su texto actual posee <?php echo strlen($entity->shortDescription) ?> caracteres. Debe cambiar su texto o este será recortado al salvar.</div>
                <?php endif; ?>

                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descripción Corta" data-emptytext="Ingrese una descripción corta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
                    
                    <?php if($this->isEditable()): ?>
                        <p style="display:none" class="privado"><span class="icon icon-private-info"></span>Virtual o Físico? (se fuera virtual la localización no es obligatoria)</p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->acessibilidade): ?>
                    <p><span class="label">Accesibilidad: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="Accesibilidad"><?php echo $entity->acessibilidade; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->acessibilidade_fisica): ?>
                    <p>
                        <span class="label">Accesibilidad física: </span>
                        <editable-multiselect entity-property="acessibilidade_fisica" empty-label="Seleccione" allow-other="true" box-title="Accesibilidad física:"></editable-multiselect>
                    </p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('acessibilidade','after'); ?>

                    <?php if($this->isEditable() || $entity->capacidade): ?>
                    <p><span class="label">Capacidad: </span><span class="js-editable" data-edit="capacidade" data-original-title="Capacidad" data-emptytext="Especifique la capacidad <?php $this->dict('entities: of the space') ?>"><?php echo $entity->capacidade; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->horario): ?>
                    <p><span class="label">Horario de funcionamiento: </span><span class="js-editable" data-edit="horario" data-original-title="Horario de Funcionamiento" data-emptytext="Ingrese el horario de apertura y cierre"><?php echo $entity->horario; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->site): ?>
                        <p><span class="label">Sitio web:</span>
                        <?php if($this->isEditable()): ?>
                            <span class="js-editable" data-edit="site" data-original-title="Sitio" data-emptytext="Ingrese la url de su sitio"><?php echo $entity->site; ?></span></p>
                        <?php else: ?>
                            <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->emailPublico): ?>
                    <p><span class="label">Email Público:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="Email Público" data-emptytext="Ingrese un email que será exhibido públicamente"><?php echo $entity->emailPublico; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable()):?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Email Privado:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Ingrese un email que no será exhibido públicamente"><?php echo $entity->emailPrivado; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->telefonePublico): ?>
                    <p><span class="label">Teléfono Público:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Teléfono Público" data-emptytext="Ingrese un teléfono que será exhibido públicamente"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable()):?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Teléfono Privado 1:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Teléfono Privado" data-emptytext="Ingrese un Teléfono que no será exhibido públicamente"><?php echo $entity->telefone1; ?></span></p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Teléfono Privado 2:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Teléfono Privado" data-emptytext="Ingrese un Teléfono que no será exhibido públicamente"><?php echo $entity->telefone2; ?></span></p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => false]); ?>
            </div>

            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descripción</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descripción <?php $this->dict('entities: of the Space') ?>" data-emptytext="Ingrese una Descripción <?php $this->dict('entities: of the space') ?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>

            <?php if ( $this->isEditable() || $entity->criterios ): ?>
                <h3>Criterios de uso <?php $this->dict('entities: of the space') ?></h3>
                <div class="descricao js-editable" data-edit="criterios" data-original-title="Criterios de uso <?php $this->dict('entities: of the space') ?>" data-emptytext="Ingrese los criterios de uso <?php $this->dict('entities: of the space') ?>" data-placeholder="Ingrese los criterios de uso <?php $this->dict('entities: of the space') ?>" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
            <?php endif; ?>

            <!-- Video Gallery BEGIN -->
            <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
            <!-- Video Gallery END -->

            <!-- Image Gallery BEGIN -->
            <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
            <!-- Image Gallery END -->
        </div>
        <!-- #sobre -->
        <div id="agenda" class="aba-content">
            <?php $this->part('agenda', array('entity' => $entity)); ?>
        </div>
        <!-- #agenda -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="sidebar-left sidebar space">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <div class="widget">
        <h3>Estado (Status)</h3>
        <?php if($this->isEditable()): ?>
            <div id="editable-space-status" class="js-editable" data-edit="public" data-type="select" data-value="<?php echo $entity->public ? '1' : '0' ?>"  data-source="[{value: 0, text: 'Publicación restringida - requiere autorización para crear eventos'},{value: 1, text:'Publicación libre - cualquier persona puede crear eventos'}]">
                <?php if ($entity->public) : ?>
                    <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicación libre</div>
                    <p class="venue-status-definition">Cualquier persona puede crear eventos.</p>
                <?php else: ?>
                    <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicación restringida</div>
                    <p class="venue-status-definition">Requiere autorización para crear eventos.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php if ($entity->public) : ?>
                <div class="venue-status"><div class="icon icon-publication-status-open"></div>Publicación libre</div>
                <p class="venue-status-definition">Cualquier persona puede crear eventos.</p>
            <?php else: ?>
                <div class="venue-status"><div class="icon icon-publication-status-locked"></div>Publicación restringida</div>
                <p class="venue-status-definition">Requiere autorización para crear eventos.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar space sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para agregar archivos para descargar o links, primero es preciso salvar el espacio.<span class="close"></span></p>
        </div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="widget">
            <?php if($entity->children && $entity->children->count()): ?>
            <h3>Sub-espacios</h3>
            <ul class="js-slimScroll widget-list">
                <?php foreach($entity->children as $space): ?>
                <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entity->id && $entity->canUser('createChild')): ?>
            <a class="btn btn-default add" href="<?php echo $app->createUrl('space','create', array('parentId' => $entity->id)) ?>">Agregar sub-espacio</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <!-- Downloads BEGIN -->
    <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>

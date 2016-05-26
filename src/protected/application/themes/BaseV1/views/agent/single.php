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

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content agent">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
        
        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
        
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--agent.png']); ?>
            
            <?php $this->part('singles/type', ['entity' => $entity]) ?>
            
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
                    <div class="alert warning">El límite de caracteres de la Descripción corta se disminuyó a 400, pero su texto actual posee <?php echo strlen($entity->shortDescription) ?> caracteres. Debe cambiar su texto o este será recortado al salvar.</div>
                <?php endif; ?>

                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descripción corta" data-emptytext="Agregue una Descripción corta" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

                    <?php if($this->isEditable() || $entity->site): ?>
                        <p><span class="label">Sitio web:</span>
                        <?php if($this->isEditable()): ?>
                            <span class="js-editable" data-edit="site" data-original-title="Sitio web" data-emptytext="Agregue la url de su sitio web"><?php echo $entity->site; ?></span></p>
                        <?php else: ?>
                            <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($this->isEditable()): ?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Nombre:</span> <span class="js-editable" data-edit="nomeCompleto" data-original-title="Nombre Completo o Razón Social" data-emptytext="Agregue su Nombre Completo o Razón Social"><?php echo $entity->nomeCompleto; ?></span></p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">CI/RUT:</span> <span class="js-editable" data-edit="documento" data-original-title="CI/RUT" data-emptytext="Agregue CI ou RUT con puntos, guiones y barras"><?php echo $entity->documento; ?></span></p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Fecha de nacimiento/fundación:</span>
                            <span class="js-editable" data-type="date" data-edit="dataDeNascimento" data-viewformat="dd/mm/yyyy" data-showbuttons="false" data-original-title="Fecha de nacimiento/fundación" data-emptytext="Agregue la feche de nacimento o fundación del agente">
                                <?php $dtN = (new DateTime)->createFromFormat('Y-m-d', $entity->dataDeNascimento); echo $dtN ? $dtN->format('d/m/Y') : ''; ?>
                            </span>
                        </p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Género:</span> <span class="js-editable" data-edit="genero" data-original-title="Género" data-emptytext="Seleccione el género si fuera persona física"><?php echo $entity->genero; ?></span></p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Raza/Color:</span> <span class="js-editable" data-edit="raca" data-original-title="Raza/Color" data-emptytext="Seleccione Raza/Color si fuera persona física"><?php echo $entity->raca; ?></span></p>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label">Email Privado:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Agregue un email que no será exhibido públicamente"><?php echo $entity->emailPrivado; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->emailPublico): ?>
                    <p><span class="label">Email:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="Email Público" data-emptytext="Agregue um email que será exibido publicamente"><?php echo $entity->emailPublico; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable() || $entity->telefonePublico): ?>
                    <p><span class="label">Teléfono Público:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Teléfono Público" data-emptytext="Agregue un teléfono que será exhibido públicamente"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if($this->isEditable()): ?>
                    <p class="privado"><span class="icon icon-private-info"></span><span class="label">Teléfono 1:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Teléfono Privado" data-emptytext="Agregue un teléfono que no será exhibido públicamente"><?php echo $entity->telefone1; ?></span></p>
                    <p class="privado"><span class="icon icon-private-info"></span><span class="label">Teléfono 2:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Teléfono Privado" data-emptytext="Agregue un teléfono que no será exhibido públicamente"><?php echo $entity->telefone2; ?></span></p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?>

            </div>
            <!--.ficha-spcultura-->

            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descripción</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descripción del Agente" data-emptytext="Agregue una descripción del agente" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->
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
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?>
</article>
<div class="sidebar-left sidebar agent">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar agent sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para Agregar archivos para descargar o links, primero es preciso salvar el agente.<span class="close"></span></p>
        </div>
    <?php endif; ?>

    <!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <?php if(count($entity->spaces) > 0): ?>
    <div class="widget">
        <h3><?php $this->dict('entities: Spaces of the agent') ?></h3>
        <ul class="widget-list js-slimScroll">
            <?php foreach($entity->spaces as $space): ?>
            <li class="widget-list-item"><a href="<?php echo $app->createUrl('space', 'single', array('id' => $space->id)) ?>"><span><?php echo $space->name; ?></span></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    <!--
    <div class="widget">
        <h3>Proyectos del agente</h3>
        <ul>
            <li><a href="#">Proyecto 1</a></li>
            <li><a href="#">Proyecto 2</a></li>
            <li><a href="#">Proyecto 3</a></li>
        </ul>
    </div>
    -->

    <!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>

<?php
 use MapasCulturais\i;
$url = $app->createUrl($entity_name);
$classes = $this->getModalClasses($use_modal);
$name = mb_strtolower($entity_classname::getEntityTypeLabel());

$title = sprintf(i::__("Crie um %s com informações básicas"), $name);
$app->applyHook('mapasculturais.add_entity_modal.title', [&$title]);
?>

<div id="<?php echo $modal_id; ?>" class="entity-modal has-step <?php echo $classes['classes']; ?>"  style="display: none">
    
    <!-- Header -->  
    <header>
        <div class="event-title-create ">
            <h2><?=$title?></h2>
        </div>
    </header> <!-- Fim Header --> 

    <!-- Body --> 
    <div class="modal-body">
        <!-- Retornos/spinner -->
        <div>
            <span class="message"></span>            
            <img src="<?php $this->asset('img/spinner_192.gif') ?>" class="spinner hidden" alt="Enviando..." style="width:5%"/>
            <?php $this->part('modal/feedback-event', ['entity_name' => $entity_name, 'label' => $name, 'modal_id' => $modal_id]); ?>
        </div><!-- Fim retornos/spinner -->

        <!-- Criação de ventos -->
        <div class="create-event">
            <?php $this->applyTemplateHook('event-modal-form', 'before' )?>
            <form method="POST" class="create-entity <?php echo ($use_modal) ? "" : "is-attached"; ?>" action="<?php echo $url; ?>"
                data-entity="<?php echo $url; ?>" data-formid="<?php echo $modal_id; ?>" id="form-for-<?php echo $modal_id; ?>">                        
                <?php $this->applyTemplateHook('event-modal-form', 'begin' )?>
                <?php $this->part('modal/before-form'); ?>
                    <?php $this->renderModalFields($entity_classname, $entity_name, $modal_id); ?>
                    <?php $this->renderModalRequiredMetadata($entity_classname, $entity_name); ?>
                    <?php $this->renderModalTaxonomies($entity_classname, $entity_name); ?>

                    <input type="hidden" name="parent_id" value="<?php echo $app->user->profile->id; ?>">
                    <?php $this->part('modal/footer', ['entity' => $entity_name]); ?>            
                    <?php $app->applyHook('mapasculturais.add_entity_modal.form:after'); ?>
                    <?php $this->applyTemplateHook('event-modal-form', 'end' )?>
            </form>  <!-- Fim Criação deventos -->
            <?php $this->applyTemplateHook('event-modal-form', 'after' )?>
        </div>        
    </div><!-- Fim body --> 
    <?php $this->part('modal/event-occurrence-form.php', ['entity' => $entity_name]); ?>   
    <!-- Footer --> 

    <div class="event-occurrence-list hidden">
        <h2><?php \MapasCulturais\i::_e("Espaços vinculados a esse evento");?></h2>
        <ul class="js-event-occurrence"></ul>
    </div>
    

    <footer>
        <?php $this->part('modal/actions-event', ['entity_name' => $entity_name, 'classes' => $classes, 'name' => $name, 'modal_id' => $modal_id]); ?>
    </footer><!-- Fim footer --> 

    <script type="text/html" id="event-occurrence-item" class="js-mustache-template">
            <li>
                <span class="pendin-space-{{space.id}} hidden warning pending"></span><br><br>
                <?php \MapasCulturais\i::_e("Local:");?> <a href="{{space.singleUrl}}" rel='noopener noreferrer'>{{space.name}}</a><br>
                <?php \MapasCulturais\i::_e("Data:");?> <strong>{{rule.description}}</strong><br>
                <?php \MapasCulturais\i::_e("Valor:");?> {{rule.price}}<br>
            </li>       
    </script>
</div>


<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "Entity";

$this->includeAngularEntityAssets($entity);

$project = $entity->project;
// @TODO adicionar ao javascript as categorias para a inscrição

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<div class="sidebar-left sidebar registration">
    <div class="setinha"></div>
</div>
<article class="main-content registration">
    <header class="main-content-header">
        <div<?php if($header = $project->getFile('header')): ?> style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem" <?php endif; ?>>
        </div>
        <!--.imagem-do-header-->
        <div class="content-do-header">
            <?php if($avatar = $project->avatar): ?>
                <div class="avatar com-imagem">
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                </div>
            <?php else: ?>
                <div class="avatar">
                    <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
                </div>
            <?php endif; ?>
            <!--.avatar-->
            <div class="entity-type registration-type">
                <div class="icone icon_document_alt"></div>
                <a href="#" data-original-title="C" data-emptytext="Selecione um tipo" data-entity='registration' data-value='<?php echo $entity->type ?>'>
                    <?php echo $project->type->name; ?>
                </a>
            </div>
            <!--.entity-type-->
            <h2><a href="<?php echo $project->singleUrl ?>"><?php echo $project->name; ?></a></h2>
            <h3>Inscrição <?php if($action !== 'create'): ?>nº <?php echo $entity->registrationNumber ?><?php endif; ?></h3>
        </div>
    </header>
    
    <div class="ficha-spcultura">
        <!-- selecionar categoria --> 
        <!-- agente responsável --> 
        <!-- instituição responsável --> 
        <!-- coletivo --> 
        
        <!-- anexos --> 
    </div>
    <!--.ficha-spcultura-->
    
</article>
<div class="sidebar registration sidebar-right">
    <div class="setinha"></div>
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">Para adicionar arquivos para download ou links, primeiro é preciso salvar a inscrição.</div>
    <?php endif; ?>

    <!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>

<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "Entity";

$project = $entity->project;

$this->addProjectRegistrationConfigurationToJs($project);

// @TODO adicionar ao javascript as categorias para a inscrição

$this->addRegistrationDataToJs($entity);

$this->includeAngularEntityAssets($entity);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<div class="sidebar-left sidebar registration">
    <div class="setinha"></div>
</div>
<article class="main-content registration" ng-controller="ProjectController">
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
                <a><?php echo $project->type->name; ?></a>
            </div>
            <!--.entity-type-->
            <h2><a href="<?php echo $project->singleUrl ?>"><?php echo $project->name; ?></a></h2>
        </div>
    </header>
    <h3 class="registration-header">Formulário de Inscrição</h3>
    <p class="registration-help">Itens com asterisco são obrigatórios.</p>
    <div class="registration-fieldset">
        <h4>Número da Inscrição</h4>
        <div class="registration-id">
            <?php if($action !== 'create'): ?><?php echo $entity->registrationNumber ?><?php endif; ?>
        </div>
    </div>
    <div class="registration-fieldset">
        <!-- selecionar categoria -->
        <h4><?php echo $project->registrationCategoriesName ?></h4>
        <p class="registration-help">Categoria xyz.</p>
        <p>
            <span class='js-editable-registrationCategory' data-original-title="Opção" data-emptytext="Selecione uma opção" data-value="<?php echo htmlentities($entity->category) ?>"><?php echo $entity->category ?></span>
        </p>
    </div>
    <div class="registration-fieldset">
    <h4>Agentes</h4>
        <!-- agente responsável -->
        <ul class="registration-list">
            <input type="hidden" name="ownerId" value="<?php echo $entity->registrationOwner->id ?>" class="js-editable" data-edit="ownerId"/>
            <?php $this->part('registration-agent', array('name' => 'owner', 'agent' => $entity->registrationOwner, 'status' => $entity->registrationOwnerStatus, 'required' => true, 'type' => 1, 'label' => 'Agente Responsável', 'description' => 'Agente individual com CPF cadastrado' )); ?>
            <!-- outros agentes -->
            <?php foreach($app->getRegisteredRegistrationAgentRelations() as $def):
                $required = $project->{$def->metadataName} === 'required';
                $relation = $entity->getRelatedAgents($def->agentRelationGroupName, true, true);

                $relation = $relation ? $relation[0] : null;

                $agent = $relation ? $relation->agent : null;
                $status = $relation ? $relation->status : null;
                ?>
                <?php $this->part('registration-agent', array(
                    'name' => $def->agentRelationGroupName,
                    'agent' => $agent,
                    'status' => $status,
                    'required' => $required,
                    'type' => $def->type,
                    'label' => $def->label,
                    'description' => $def->description )); ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <!-- anexos -->
</article>
<div class="sidebar registration sidebar-right">
    <div class="setinha"></div>
    
</div>

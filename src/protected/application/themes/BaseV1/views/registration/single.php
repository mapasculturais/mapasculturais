<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "Entity";

$project = $entity->project;

$this->addRegistrationToJs($entity);

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
    <div class="registration-fieldset">
        <h4>Número da Inscrição</h4>
        <div class="registration-id">
            <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
        </div>
    </div>
    <?php if($project->registrationCategories): ?>
        <div class="registration-fieldset">
            <!-- selecionar categoria -->
            <h4><?php echo $project->registrationCategTitle ?></h4>
            <!-- <p class="registration-help"><?php echo $project->registrationCategDescription ?></p> -->
            <p>
                <span class='js-editable-registrationCategory' data-original-title="Opção" data-emptytext="Selecione uma opção" data-value="<?php echo htmlentities($entity->category) ?>"><?php echo $entity->category ?></span>
            </p>
        </div>
    <?php endif; ?>
    <div class="registration-fieldset">
        <h4>Agentes</h4>
        <!-- agentes relacionados a inscricao -->
        <ul class="registration-list">
            <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <li ng-repeat="def in data.entity.registrationAgents" class="registration-list-item">
                <div class="registration-label">{{def.label}}</div>
                <div class="registration-description">{{def.description}}</div>

                <div id="registration-agent-{{def.agentRelationGroupName}}" class="js-registration-agent registration-agent" ng-class="{pending: def.relationStatus < 0}">
                    <p ng-if="def.relationStatus < 0" class="alert warning">Aguardando confirmação</p>
                    <div class="clearfix">
                        <img ng-src="{{def.agent.avatarUrl || data.assets.avatarAgent}}" class="registration-agent-avatar" />
                        <div>
                            <a ng-if="def.agent" href="{{def.agent.singleUrl}}">{{def.agent.name}}</a>
                            <span ng-if="!def.agent">Não informado</span>
                        </div>
                    </div>
                    <span ng-repeat="prop in data.propLabels" ng-if="def.agent[prop.name]">
                        <span class="label">{{prop.label}}</span>: {{def.agent[prop.name]}}
                        <br>
                    </span>
                </div>

                <edit-box id="editbox-select-registration-{{def.agentRelationGroupName}}" position="left" title="Selecionar {{def.label}}" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <p ng-if='def.agentRelationGroupName != "owner"'><label><input type="checkbox"> Permitir que este agente também edite essa inscrição.</label></p>
                    <find-entity id='find-entity-registration-{{def.agentRelationGroupName}}' name='{{def.agentRelationGroupName}}' api-query="data.relationApiQuery[def.agentRelationGroupName]" entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" spinner-condition="data.registrationSpinner"></find-entity>
                </edit-box>
            </li>
        </ul>
    </div>
    <!-- anexos -->
    <div id="registration-attachments" class="registration-fieldset">
        <h4>Anexos</h4>
        <ul class="attachment-list" ng-controller="RegistrationFilesController">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                <div class="label"> {{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}</div>
                <div class="attachment-description">
                    {{fileConfiguration.description}}
                    <span ng-if="fileConfiguration.template">
                        (<a class="attachment-template" target="_blank" href="{{fileConfiguration.template.url}}">baixar modelo</a>)
                    </span>
                </div>
                <a ng-if="fileConfiguration.file" class="attachment-title" href="{{fileConfiguration.file.url}}" target="_blank">{{fileConfiguration.file.name}}</a>
            </li>
        </ul>
    </div>
</article>
<div class="sidebar registration sidebar-right">
    <div class="setinha"></div>
</div>

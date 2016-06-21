<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.project';

$project = $entity->project;

$this->addEntityToJs($entity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="ProjectController">
    <header class="main-content-header">
        <div
            <?php if($header = $project->getFile('header')): ?>
                class="header-image"
                style="background-image: url(<?php echo $header->transform('header')->url; ?>);"
            <?php endif; ?>
        >
        </div>
        <!--.header-image-->
        <div class="header-content">
        <?php if($avatar = $project->avatar): ?>
            <div class="avatar com-imagem">
                <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
        <?php else: ?>
            <div class="avatar">
                <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
        <?php endif; ?>
            <!-- pro responsivo!!! -->
            <?php if($project->isVerified): ?>
                <a class="verified-seal hltip active" title="Este projeto é verificado." href="#"></a>
            <?php endif; ?>
            </div>
            <!--.avatar-->
            <div class="entity-type registration-type">
                <div class="icon icon-project"></div>
                <a><?php echo $project->type->name; ?></a>
            </div>
            <!--.entity-type-->
            <h2><a href="<?php echo $project->singleUrl ?>"><?php echo $project->name; ?></a></h2>
        </div>
    </header>
    <div class="alert success">
        Inscrição enviada no dia
        <?php echo $entity->sentTimestamp->format('d/m/Y à\s H:i:s'); ?>
    </div>

    <h3 class="registration-header">Formulário de Inscrição</h3>

    <div class="registration-fieldset clearfix">
        <h4>Número da Inscrição</h4>
        <div class="registration-id alignleft">
            <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
        </div>
        <div class="alignright">
            <?php if($project->publishedRegistrations): ?>
                <span class="status status-{{getStatusSlug(<?php echo $entity->status ?>)}}">{{getStatusNameById(<?php echo $entity->status ?>)}}</span>
            <?php elseif($project->canUser('@control')): ?>
                <mc-select class="{{getStatusSlug(data.registration.status)}}" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
            <?php endif; ?>
        </div>
    </div>
    <?php if($project->registrationCategories): ?>
        <div class="registration-fieldset">
            <!-- selecionar categoria -->
            <h4><?php echo $project->registrationCategTitle ?></h4>
            <!-- <p class="registration-help"><?php echo $project->registrationCategDescription ?></p> -->
            <div>
                <?php echo $entity->category ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="registration-fieldset">
        <h4>Agentes (proponentes)</h4>
        <!-- agentes relacionados a inscricao -->
        <ul class="registration-list">
            <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <li ng-repeat="def in data.entity.registrationAgents" class="registration-list-item" ng-if="def.use !== 'dontUse'">
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
                    <div class="registration-agent-details">
                        <div ng-repeat="prop in data.propLabels" ng-if="def.agent[prop.name]"><span class="label">{{prop.label}}</span>: {{prop.name === 'location' ? getReadableLocation(def.agent[prop.name]) : def.agent[prop.name]}}</div>
                    </div>
                </div>

                <edit-box id="editbox-select-registration-{{def.agentRelationGroupName}}" position="left" title="Selecionar {{def.label}}" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <!-- <p ng-if='def.agentRelationGroupName != "owner"'><label><input type="checkbox"> Permitir que este agente também edite essa inscrição.</label></p> -->
                    <find-entity id='find-entity-registration-{{def.agentRelationGroupName}}' name='{{def.agentRelationGroupName}}' api-query="data.relationApiQuery[def.agentRelationGroupName]" entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationAgent" spinner-condition="data.registrationSpinner"></find-entity>
                </edit-box>
            </li>
        </ul>
    </div>
    
    <!-- BEGIN Seals -->
    <div class="registration-fieldset">
        <h4>Selos Certificadores</h4>
        <p class="registration-help">Relacione os selos que serão atribuídos as entidades relacionadas a inscrição quando o inscrito for aprovado.</p>
        
    </div>
    <! END Seals -->
    
    <!-- anexos -->
    <div ng-if="data.entity.registrationFileConfigurations.length > 0" id="registration-attachments" class="registration-fieldset">
        <h4>Anexos (documentos necessários)</h4>
        <ul class="attachment-list" ng-controller="RegistrationFilesController">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                <div class="label"> {{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}</div>
                <a ng-if="fileConfiguration.file" class="attachment-title" href="{{fileConfiguration.file.url}}" target="_blank">{{fileConfiguration.file.name}}</a>
                <span ng-if="!fileConfiguration.file">Arquivo não enviado.</span>
            </li>
        </ul>
    </div>
</article>
<div class="sidebar-left sidebar registration">
</div>
<div class="sidebar registration sidebar-right">
</div>

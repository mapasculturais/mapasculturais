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
    <p class="registration-help">Itens com asterisco são obrigatórios.</p>
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
            <p class="registration-help"><?php echo $project->registrationCategDescription ?></p>
            <p>
                <span class='js-editable-registrationCategory' data-original-title="Opção" data-emptytext="Selecione uma opção" data-value="<?php echo htmlentities($entity->category) ?>"><?php echo $entity->category ?></span>
            </p>
        </div>
    <?php endif; ?>
    <div class="registration-fieldset">
        <h4>Agentes</h4>
        <p class="registration-help">Relacione os agentes a esta Inscrição</p>
        <!-- agentes relacionados a inscricao -->
        <ul class="registration-list">
            <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <li ng-repeat="def in data.entity.registrationAgents" class="registration-list-item">
                <div class="registration-label">{{def.label}} <span ng-if="def.required" class="required">*</span></div>
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
                </div>

                <div ng-if="data.isEditable" class="btn-group">
                    <span ng-if="def.agent">
                        <a class="botao editar hltip" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Editar {{def.label}}">editar</a>
                        <a ng-if="def.agentRelationGroupName != 'owner' && def.use != 'required'" ng-click="unsetRegistrationAgent(def.agent.id, def.agentRelationGroupName)" class="botao excluir hltip" title="Excluir {{def.label}}">excluir</a>
                    </span>
                    <a ng-if="!def.agent" class="botao adicionar hltip" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Adicionar {{def.label}}">adicionar</a>
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
        <p class="registration-help">Anexator descrivinhator helpior.</p>
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
                <?php if($this->isEditable()): ?>
                    <div class="btn-group">
                        <!-- se já subiu o arquivo-->
                        <!-- se não subiu ainda -->
                        <a class="botao hltip" ng-class="{'enviar':!fileConfiguration.file,'editar':fileConfiguration.file}" ng-click="openFileEditBox(fileConfiguration.id, $index, $event)" title="{{!fileConfiguration.file ? 'enviar' : 'editar'}} anexo">{{!fileConfiguration.file ? 'enviar' : 'editar'}}</a>
                        <a ng-if="!fileConfiguration.required && fileConfiguration.file" ng-click="removeFile(fileConfiguration.id, $index)" class="botao excluir hltip" title="excluir anexo">excluir</a>
                    </div>
                    <edit-box id="editbox-file-{{fileConfiguration.id}}" position="bottom" title="{{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendFile" submit-label="Enviar anexo" index="{{$index}}" spinner-condition="data.uploadSpinner">
                        <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{fileConfiguration.groupName}}"  enctype="multipart/form-data">
                            <div class="alert danger escondido"></div>
                            <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
                            <input type="file" name="{{fileConfiguration.groupName}}" />

                            <div class="js-ajax-upload-progress">
                                <div class="progress">
                                    <div class="bar"></div>
                                    <div class="percent">0%</div>
                                </div>
                            </div>
                        </form>
                    </edit-box>
                <?php endif;?>
            </li>
        </ul>
    </div>
    <div class="registration-fieldset">
        <p class="registration-help">Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição. Depois de enviada, não será mais possível editá-la.</p>
        <a class="botao principal" ng-click="sendRegistration()">enviar inscrição</a>
    </div>
</article>
<div class="sidebar registration sidebar-right">
    <div class="setinha"></div>
</div>

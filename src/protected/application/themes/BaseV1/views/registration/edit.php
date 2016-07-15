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
        <h4>Agentes (proponentes)</h4>
        <p class="registration-help">Relacione os agentes responsáveis pela inscrição.</p>
        <!-- agentes relacionados a inscricao -->
        <ul class="registration-list">
            <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <li ng-repeat="def in data.entity.registrationAgents" ng-if="def.use != 'dontUse'" class="registration-list-item registration-edit-mode">
                <div class="registration-label">{{def.label}} <span ng-if="def.use === 'required'" class="required">*</span></div>
                <div class="registration-description">{{def.description}}</div>

                <div id="registration-agent-{{def.agentRelationGroupName}}" class="js-registration-agent registration-agent" ng-class="{pending: def.relationStatus < 0}">
                    <p ng-if="def.relationStatus < 0" class="alert warning" style="display:block !important /* está oculto no scss */" >Aguardando confirmação</p>
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
                        <a class="btn btn-default edit hltip" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Editar {{def.label}}">Trocar agente</a>
                        <a class="btn btn-default delete hltip" ng-if="def.agentRelationGroupName != 'owner' && def.use != 'required'" ng-click="unsetRegistrationAgent(def.agent.id, def.agentRelationGroupName)" title="Excluir {{def.label}}">Excluir</a>
                    </span>
                    <a class="btn btn-default add hltip" ng-if="!def.agent" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Adicionar {{def.label}}">Adicionar</a>
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
		<ul class="registration-list">
            <li  class="registration-list-item registration-edit-mode">
				<div class="registration-label">Agentes</div>
				<div class="clearfix">
					<span>
						<img src="" />
					</span>
					<div>
						<a href="">Teste</a>
					</div>
				</div>
			</li>
			<li  class="registration-list-item registration-edit-mode">
				<div class="registration-label">Espaços </div>
				<div class="clearfix">
					<img class="registration-agent-avatar" />
					<div>
						<a href="">Teste</a>
					</div>
				</div>
			</li>
			<li  class="registration-list-item registration-edit-mode">
				<div class="registration-label">Projetos </div>
				<div class="clearfix">
					<img class="registration-agent-avatar" />
					<div>
						<a href="">Teste</a>
					</div>
				</div>
			</li>
			<li  class="registration-list-item registration-edit-mode">
				<div class="registration-label">Eventos </div>
				<div class="clearfix">
					<img class="registration-agent-avatar" />
					<div>
						<a href="">Teste</a>
					</div>
				</div>
			</li>
        </ul>
	</div>
    <!-- anexos -->
    <div ng-if="data.entity.registrationFileConfigurations.length > 0" id="registration-attachments" class="registration-fieldset">
        <h4>Anexos (documentos necessários)</h4>
        <p class="registration-help">Para efetuar sua inscrição, faça upload dos documentos abaixo.</p>
        <ul class="attachment-list" ng-controller="RegistrationFilesController">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item registration-edit-mode">
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
                        <a class="btn btn-default hltip" ng-class="{'send':!fileConfiguration.file,'edit':fileConfiguration.file}" ng-click="openFileEditBox(fileConfiguration.id, $index, $event)" title="{{!fileConfiguration.file ? 'enviar' : 'editar'}} anexo">{{!fileConfiguration.file ? 'Enviar' : 'Editar'}}</a>
                        <a class="btn btn-default delete hltip" ng-if="!fileConfiguration.required && fileConfiguration.file" ng-click="removeFile(fileConfiguration.id, $index)" title="excluir anexo">Excluir</a>
                    </div>
                    <edit-box id="editbox-file-{{fileConfiguration.id}}" position="bottom" title="{{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendFile" submit-label="Enviar anexo" index="{{$index}}" spinner-condition="data.uploadSpinner">
                        <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{fileConfiguration.groupName}}"  enctype="multipart/form-data">
                            <div class="alert danger hidden"></div>
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
                
        <?php if($entity->project->isRegistrationOpen()): ?>
            <p class="registration-help">Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição. <strong>Depois de enviada, não será mais possível editá-la.</strong></p>
            <a class="btn btn-primary" ng-click="sendRegistration()">Enviar inscrição</a>
        <?php else: ?>
            <p class="registration-help">
                <strong>
                    <?php // gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642
                    $date = strftime("%d de {%B} de %G às %H:%M", $entity->project->registrationTo->getTimestamp());
                    $full_date = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
                        return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
                    }, $date);
                    ?>
                    As inscrições encerraram-se em <?php echo $full_date; ?>.
                </strong>
            </p>
        <?php endif; ?>
            
        <?php if(!$entity->project->isRegistrationOpen() && $app->user->is('superAdmin')): ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="Somente super admins podem usar este botão e somente deve ser usado para enviar inscrições que não foram enviadas por problema do sistema." data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>">enviar esta inscrição</a>
        <?php endif ?>
    </div>
</article>
<div class="sidebar-left sidebar registration">
</div>
<div class="sidebar registration sidebar-right">
</div>

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
                <a class="verified-seal hltip active" title="Este proyecto está verificado." href="#"></a>
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
    <h3 class="registration-header">Formulario de Inscripción</h3>
    <p class="registration-help">Items con asterisco son obligatorios.</p>
    <div class="registration-fieldset">
        <h4>Número de la Inscripción</h4>
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
                <span class='js-editable-registrationCategory' data-original-title="Opción" data-emptytext="Seleccione una opción" data-value="<?php echo htmlentities($entity->category) ?>"><?php echo $entity->category ?></span>
            </p>
        </div>
    <?php endif; ?>
    <div class="registration-fieldset">
        <h4>Agentes (solicitantes)</h4>
        <p class="registration-help">Relacione los agentes responsbles por la inscripción.</p>
        <!-- agentes relacionados a inscricao -->
        <ul class="registration-list">
            <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <li ng-repeat="def in data.entity.registrationAgents" ng-if="def.use != 'dontUse'" class="registration-list-item registration-edit-mode">
                <div class="registration-label">{{def.label}} <span ng-if="def.use === 'required'" class="required">*</span></div>
                <div class="registration-description">{{def.description}}</div>

                <div id="registration-agent-{{def.agentRelationGroupName}}" class="js-registration-agent registration-agent" ng-class="{pending: def.relationStatus < 0}">
                    <p ng-if="def.relationStatus < 0" class="alert warning" style="display:block !important /* está oculto no scss */" >Esperando confirmación</p>
                    <div class="clearfix">
                        <img ng-src="{{def.agent.avatarUrl || data.assets.avatarAgent}}" class="registration-agent-avatar" />
                        <div>
                            <a ng-if="def.agent" href="{{def.agent.singleUrl}}">{{def.agent.name}}</a>
                            <span ng-if="!def.agent">No informado</span>
                        </div>
                    </div>
                </div>

                <div ng-if="data.isEditable" class="btn-group">
                    <span ng-if="def.agent">
                        <a class="btn btn-default edit hltip" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Editar {{def.label}}">Cambiar agente</a>
                        <a class="btn btn-default delete hltip" ng-if="def.agentRelationGroupName != 'owner' && def.use != 'required'" ng-click="unsetRegistrationAgent(def.agent.id, def.agentRelationGroupName)" title="Eliminar {{def.label}}">Eliminar</a>
                    </span>
                    <a class="btn btn-default add hltip" ng-if="!def.agent" ng-click="openEditBox('editbox-select-registration-' + def.agentRelationGroupName, $event)" title="Agregar {{def.label}}">Agregar</a>
                </div>

                <edit-box id="editbox-select-registration-{{def.agentRelationGroupName}}" position="left" title="Seleccionar {{def.label}}" cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                    <!-- <p ng-if='def.agentRelationGroupName != "owner"'><label><input type="checkbox"> Permitir que este agente também edite essa inscripción.</label></p> -->
                    <find-entity id='find-entity-registration-{{def.agentRelationGroupName}}' name='{{def.agentRelationGroupName}}' api-query="data.relationApiQuery[def.agentRelationGroupName]" entity="agent" no-results-text="Ningún agente encontrado" select="setRegistrationAgent" spinner-condition="data.registrationSpinner"></find-entity>
                </edit-box>
            </li>
        </ul>
    </div>
    <!-- anexos -->
    <div ng-if="data.entity.registrationFileConfigurations.length > 0" id="registration-attachments" class="registration-fieldset">
        <h4>Adjuntos (documentos necesarios)</h4>
        <p class="registration-help">Para efectuar su inscripción, cargue los documentos abajo.</p>
        <ul class="attachment-list" ng-controller="RegistrationFilesController">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item registration-edit-mode">
                <div class="label"> {{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}</div>
                <div class="attachment-description">
                    {{fileConfiguration.description}}
                    <span ng-if="fileConfiguration.template">
                        (<a class="attachment-template" target="_blank" href="{{fileConfiguration.template.url}}">bajar modelo</a>)
                    </span>
                </div>
                <a ng-if="fileConfiguration.file" class="attachment-title" href="{{fileConfiguration.file.url}}" target="_blank">{{fileConfiguration.file.name}}</a>
                <?php if($this->isEditable()): ?>
                    <div class="btn-group">
                        <!-- se já subiu o arquivo-->
                        <!-- se não subiu ainda -->
                        <a class="btn btn-default hltip" ng-class="{'send':!fileConfiguration.file,'edit':fileConfiguration.file}" ng-click="openFileEditBox(fileConfiguration.id, $index, $event)" title="{{!fileConfiguration.file ? 'enviar' : 'editar'}} adjunto">{{!fileConfiguration.file ? 'Enviar' : 'Editar'}}</a>
                        <a class="btn btn-default delete hltip" ng-if="!fileConfiguration.required && fileConfiguration.file" ng-click="removeFile(fileConfiguration.id, $index)" title="excluir anexo">Excluir</a>
                    </div>
                    <edit-box id="editbox-file-{{fileConfiguration.id}}" position="bottom" title="{{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendFile" submit-label="Enviar adjunto" index="{{$index}}" spinner-condition="data.uploadSpinner">
                        <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{fileConfiguration.groupName}}"  enctype="multipart/form-data">
                            <div class="alert danger hidden"></div>
                            <p class="form-help">Tamaño máximo del archivo: {{maxUploadSizeFormatted}}</p>
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
            <p class="registration-help">Asegúrese de que ha completado la información correctamente antes de presentar su solicitud. <strong>Después de enviada, no será posible editarla.</strong></p>
            <a class="btn btn-primary" ng-click="sendRegistration()">Enviar inscripción</a>
            <p class="registration-help"><strong>Debe Salvar antes de Enviar inscripción.</strong> Si no se puede enviar, se le indicarán los errores con un signo de exclamación en los registros correspondientes. </p>
            
        <?php else: ?>
            <p class="registration-help">
                <strong>
                    <?php // gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642
                    $date = strftime("%d de {%B} de %G a las %H:%M", $entity->project->registrationTo->getTimestamp());
                    $full_date = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
                        return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
                    }, $date);
                    ?>
                    Las inscripciones se cerrarán el <?php echo $full_date; ?>.
                </strong>
            </p>
        <?php endif; ?>
            
        <?php if(!$entity->project->isRegistrationOpen() && $app->user->is('superAdmin')): ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="Solamente super admins pueden usar este botón y solamente debe ser usado para enviar inscripciones que no fueron enviadas por problemas del sistema." data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>">enviar esta inscripción</a>
        <?php endif ?>
    </div>
</article>
<div class="sidebar-left sidebar registration">
</div>
<div class="sidebar registration sidebar-right">
</div>

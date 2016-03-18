<?php
use MapasCulturais\Entities\Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.project';

$this->addEntityToJs($entity);

$this->addProjectToJs($entity);

if(!$entity->isNew() && $entity->canUser('@control')){
    $this->addProjectEventsToJs($entity);
}

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');
}

$this->includeAngularEntityAssets($entity);

$child_entity_request = isset($child_entity_request) ? $child_entity_request : null;

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content project" ng-controller="ProjectController">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
        
        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
        
        <!--.header-image-->
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--project.png']); ?>
            
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
    <ul class="abas clearfix">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>

        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscricoes">Inscripciones</a></li>
        <?php if($entity->publishedRegistrations): ?>
            <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Resultados</a></li>
        <?php elseif($entity->canUser('@control')): ?>
            <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Inscriptos</a></li>
        <?php endif; ?>

        <?php if(!$entity->isNew()): ?>
            <li ng-if="data.entity.userHasControl && data.entity.events.length" ><a href="#eventos">Estado de los eventos</a></li>
        <?php endif; ?>
        <?php $this->applyTemplateHook('tabs','before'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>
    
    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <?php if($app->user->is('admin') || $app->user->is('staff')): // @TODO: remover este if quando o layout estiver pronto ?>
        <?php if(!$entity->isNew()): ?>
        <div id="eventos" ng-if="data.entity.userHasControl && data.entity.events.length" ng-controller="ProjectEventsController">

            <div class="alignright" >
                <span class="btn btn-small btn-default" ng-click="selectAll()">marcar eventos listados</span>
                <span class="btn btn-small btn-default" ng-click="deselectAll()">desmarcar eventos listados</span>
            </div>
            <input type="text" ng-model="data.eventFilter" ng-change="filterEvents()" placeholder="filtrar eventos" style="width:300px;"><br>

            <div class="eventos-selecionados">
                <div class="alignright" ng-show="!data.processing">
                    <span class="btn btn-small btn-default" ng-click="unpublishSelectedEvents()">cambiar a borrador</span>
                    <span class="btn btn-small btn-success" ng-click="publishSelectedEvents()">publicar</span>
                </div>
                <div ng-show="data.processing" class="mc-spinner alignright" ><img ng-src="{{data.spinnerUrl}}" /> {{data.processingText}}</div>
                {{numSelectedEvents}} {{numSelectedEvents == 1 ? 'evento seleccionado' : 'eventos seleccionados' }}
            </div>


            <article class="objeto clearfix" ng-repeat="event in events" ng-show="!event.hidden" ng-class="{'selected': event.selected, 'evt-publish': event.status == 1, 'evt-draft': event.status == 0}">
                <h1><input type='checkbox' ng-model="event.selected" ng-checked="event.selected">
                <a href='{{event.singleUrl}}'>{{event.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <div class="objeto-thumb"><img src="" ng-src="{{event['@files:avatar.avatarSmall'] ? event['@files:avatar.avatarSmall'].url : data.assets.avatarEvent }}"></div>
                    <div class="objeto-resumo">
                        <ul class="event-ocurrences">
                            <li ng-repeat='occ in event.occurrences'>
                                <a href="{{occ.space.singleUrl}}">{{occ.space.name}}</a> - {{occ.rule.description}} <span ng-if='occ.rule.price'>({{occ.rule.price}})</span>
                            </li>
                        </ul>
                    </div>

                    <div class="objeto-meta">
                        <div><span class="label">Estado:</span> {{event.status === 0 ? 'rascunho' : 'publicado'}}</div>
                        <div><span class="label">Autor:</span> <a href='{{event.owner.singleUrl}}'>{{event.owner.name}}</a></div>
                        <div><span class="label">Tipo de evento:</span> {{event.terms.linguagem.join(', ')}}</div>
                        <div><span class="label">Clasificación:</span> {{event.classificacaoEtaria}}</div>
                    </div>
                </div>
            </article>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        
        <div id="sobre" class="aba-content">
            <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
                <div class="highlighted-message clearfix">
                    <?php if($this->isEditable() || $entity->registrationFrom): ?>
                        <div class="registration-dates">
                            Inscripciones Abiertas de
                            <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="Data inicial"><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Fecha inicial'; ?></strong>
                            a
                            <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="Fecha final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Fecha final'; ?></strong>
                            a las
                            <strong class="js-editable" id="registrationTo_time" data-datetime-value="<?php echo $entity->registrationTo ? $entity->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="Hora final" data-emptytext="Hora final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('H:i') : ''; ?></strong>
                            .
                        </div>
                    <?php endif; ?>
                    <?php if ($entity->useRegistrations && !$this->isEditable() ) : ?>
                        <a ng-if="data.projectRegistrationsEnabled" class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()">Inscripciones online</a>
                    <?php endif; ?>
                    <div class="clear" ng-if="data.projectRegistrationsEnabled && data.isEditable">Inscripciones online <strong><span id="editable-use-registrations" class="js-editable clear" data-edit="useRegistrations" data-type="select" data-value="<?php echo $entity->useRegistrations ? '1' : '0' ?>"
                            data-source="[{value: 0, text: 'desactivadas'},{value: 1, text:'activadas'}]"></span></strong>
                    </div>

                </div>
            <?php endif; ?>
            <div class="ficha-spcultura">
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                    <div class="alert warning">El límite de caracteres de la descripción corta disminuyó a 400, pero su texto actual posee <?php echo strlen($entity->shortDescription) ?> caracteres. Debe cambiar su texto o este será recortado al salvar.</div>
                <?php endif; ?>

                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descripción Corta" data-emptytext="Ingrese una descripción corta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
                    <?php if($this->isEditable() || $entity->site): ?>
                        <p>
                            <span class="label">Sitio web:</span>
                            <span ng-if="data.isEditable" class="js-editable" data-edit="site" data-original-title="Sitio web" data-emptytext="Ingrese la url de su sitio web"><?php echo $entity->site; ?></span>
                            <a ng-if="!data.isEditable" class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                        </p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>
            </div>

            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descripción</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descripción del Proyecto" data-emptytext="Ingrese una descripción del proyecto" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
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
        <div ng-if="data.projectRegistrationsEnabled" id="inscricoes" class="aba-content">
            <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
                <p ng-if="data.isEditable" class="alert info">
                    Utilize este espacio en el caso que quiera abrir inscripciones para Agentes Culturales registrados en la plataforma.
                    <span class="close"></span>
                </p>
            <?php endif; ?>
            <?php if($registrations = $app->repo('Registration')->findByProjectAndUser($entity, $app->user)): ?>
                    <table class="my-registrations">
                        <caption>Mis inscripciones</caption>
                        <thead>
                            <tr>
                                <th class="registration-id-col">
                                    Inscripción
                                </th>
                                <th class="registration-agents-col">
                                    Agentes
                                </th>
                                <th class="registration-status-col">
                                    Estado (status)
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($registrations as $registration): ?>
                            <tr>
                                <td class="registration-id-col">
                                    <a href="<?php echo $registration->singleUrl ?>"><?php echo $registration->number ?></a>
                                </td>
                                <td class="registration-agents-col">
                                    <p>
                                        <span class="label">responsable</span><br>
                                        <?php echo $registration->owner->name ?>
                                    </p>
                                    <?php
                                    foreach($app->getRegisteredRegistrationAgentRelations() as $def):
                                        if(!$entity->useRegistrationAgentRelation($def))
                                            continue;
                                    ?>
                                        <?php if($agents = $registration->getRelatedAgents($def->agentRelationGroupName)): ?>
                                            <p>
                                                <span class="label"><?php echo $def->label ?></span><br>
                                                <?php echo $agents[0]->name ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </td>
                                <td class="registration-status-col">
                                    <?php if($registration->status > 0): ?>
                                        Enviada en <?php echo $registration->sentTimestamp->format('d/m/Y à\s H:i'); ?>.
                                    <?php else: ?>
                                        No enviada.<br>
                                        <a class="btn btn-small btn-primary" href="<?php echo $registration->singleUrl ?>">Editar y enviar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
            <?php endif; ?>
            <?php if($entity->introInscricoes || $this->isEditable()): ?>

                <div id="intro-das-inscricoes" ng-class="{'registration-fieldset': data.isEditable}">
                    <h4 ng-if="data.isEditable">1. Introducción</h4>
                    <p class="registration-help" ng-if="data.isEditable">Cree un texto de introducción.</p>
                    <p class="js-editable" data-edit="introInscricoes" data-original-title="Introducción de la inscripción" data-emptytext="Ingrese el texto." data-placeholder="Ingrese el texto." data-showButtons="bottom" data-placement="bottom"><?php echo $this->isEditable() ? $entity->introInscricoes : nl2br($entity->introInscricoes); ?></p>
                </div>
                <!-- #intro-das-inscricoes -->
            <?php endif; ?>
            <p ng-if="!data.isEditable && data.entity.registrationRulesFile"><a class="btn btn-default download" href="{{data.entity.registrationRulesFile.url}}" >Descargar el reglamento</a></p>
            <div ng-if="data.isEditable" class="registration-fieldset">
                <h4>2. Reglamento</h4>

                <?php if($this->controller->action == 'create'): ?>
                    <p class="allert warning">Antes de subir el reglamento es preciso salvar el proyecto.</p>

                <?php else: ?>
                    <p class="registration-help">Envíe un archivo con el reglamento. Formatos aceptados .doc, .odt e .pdf.</p>
                    <a class="btn btn-default send hltip" ng-if="!data.entity.registrationRulesFile" ng-click="openRulesUploadEditbox($event)" title="Enviar reglamento" >Enviar</a>
                    <div ng-if="data.entity.registrationRulesFile">
                        <span class="js-open-editbox mc-editable" ng-click="openRulesUploadEditbox($event)">{{data.entity.registrationRulesFile.name}}</span>
                        <a class="delete hltip" ng-click="removeRegistrationRulesFile()" title="eliminar reglamento"></a>
                    </div>
                    <edit-box id="edibox-upload-rules" position="bottom" title="Reglamento" submit-label="Enviar" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendRegistrationRulesFile" on-cancel="closeRegistrationRulesUploadEditbox" spinner-condition="data.uploadSpinner">
                        <form class="js-ajax-upload" method="post" action="<?php echo $app->createUrl('project', 'upload', array($entity->id))?>" data-group="rules"  enctype="multipart/form-data">
                            <div class="alert danger hidden"></div>
                            <p class="form-help">Tamaño máximo de archivo: {{maxUploadSizeFormatted}}</p>
                            <input type="file" name="rules" />

                            <div class="js-ajax-upload-progress">
                                <div class="progress">
                                    <div class="bar"></div>
                                    <div class="percent">0%</div>
                                </div>
                            </div>
                        </form>
                    </edit-box>
                <?php endif ?>
            </div>
            <!-- #registration-rules -->
            <?php if($this->isEditable()):
                $can_edit = $entity->canUser('modifyRegistrationFields');

                $ditable_class = $can_edit ? 'js-editable' : '';

                if($can_edit){
                    $registration_categories = $entity->registrationCategories ? implode("\n", $entity->registrationCategories) : '';
                }else{
                    $registration_categories = is_array($entity->registrationCategories) ? implode('; ', $entity->registrationCategories) : '';
                }

            ?>
                <div id="registration-categories" class="registration-fieldset">
                    <h4>3. Opciones</h4>
                    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Puede crear opciones para que los solicitantes elijan al momento de inscribirse, por ejemplo, "categorías" o "modalidades". Si no desea utilizar esta función, deje el campo "Opciones" en blanco.</p>
                    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">La edición de estas opciones está deshabilitada porque agentes ya se inscribieron en este proyecto. </p>
                    <p>
                        <span class="label">Título de las opciones</span><br>
                        <span class="<?php echo $ditable_class ?>" data-edit="registrationCategTitle" data-original-title="Título de las opciones" data-emptytext="Ingrese un título para el campo de opciones"><?php echo $entity->registrationCategTitle ?  $entity->registrationCategTitle : 'Categoría'; ?></span>
                    </p>
                    <p>
                        <span class="label">Descripción de las opciones</span><br>
                        <span class="<?php echo $ditable_class ?>" data-edit="registrationCategDescription" data-original-title="Descripción de las opciones" data-emptytext="Ingrese una descripción para el campo de opciones"><?php echo $entity->registrationCategDescription ? $entity->registrationCategDescription : 'Seleccione una categoría'; ?></span>
                    </p>
                    <p>
                        <span class="label">Opciones</span><br>
                        <span class="<?php echo $ditable_class ?>" data-edit="registrationCategories" data-type="textarea" data-original-title="Opciones de inscripción (coloque una opción por línea)" data-emptytext="Ingrese las opciones de inscripción"><?php echo $registration_categories; ?></span>
                    </p>
                </div>
                <!-- #registration-categories -->
                <div id="registration-agent-relations" class="registration-fieldset">
                    <h4>4. Agentes</h4>
                    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Toda inscripción obligatoriamente debe poseer un Agente Individual responsable, pero es posible que la inscripción sea hecha en nombre de un agente colectivo, con o sin RUT. En esos casos, es preciso definir abajo si esa información es necesaria e si es obligatoria.</p>
                    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">La edición de estas opciones están desahbilitadas porque agentes ya se inscribieron en este proyecto. </p>

                    <?php foreach($app->getRegisteredRegistrationAgentRelations() as $def):
                        $metadata_name = $def->metadataName;
                        if($can_edit){
                            $option_label = $entity->$metadata_name ? $entity->$metadata_name : 'dontUse';
                        }else{
                            $option_label = $def->getOptionLabel($entity->$metadata_name);
                        }
                    ?>
                        <div class="registration-related-agent-configuration">
                            <p>
                                <span class="label"><?php echo $def->label ?></span> <span class="registration-help">(<?php echo $def->description ?>)</span>
                                <br>
                                <span class="<?php echo $ditable_class ?>" data-edit="<?php echo $metadata_name ?>" data-original-title="<?php echo $def->metadataConfiguration['label'] ?>" data-emptytext="Seleccione una opción"><?php echo $option_label ?></span>
                            </p>

                        </div>
                    <?php endforeach; ?>

                    <p>
                        <span class="label">Número máximo de inscripciones por agente responsable</span><br>
                        <span class="registration-help">Cero (0) significa sin límites</span><br>
                        <span class="<?php echo $ditable_class ?>" data-edit="registrationLimitPerOwner" data-original-title="Número máximo de inscripciones por agente responsable" data-emptytext="Ingrese el número máximo de inscripciones por agente responsable"><?php echo $entity->registrationLimitPerOwner ? $entity->registrationLimitPerOwner : '0'; ?></span>
                    </p>
                </div>
                <!-- #registration-agent-relations -->
                <div id="registration-attachments" class="registration-fieldset">
                    <h4>5. Anexos</h4>
                    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Puede pedir a los solicitantes que envíen archivos adjuntos para registrarse en su Proyecto. Para cada archivo adjunto, puede proporcionar un modelo que el solicitante puede descargar, rellenar y volver a subir.</p>
                    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">La edición de estas opciones están deshabilitadas porque agentes ya se inscrebieron en este proyecto. </p>
                    <div ng-controller="RegistrationFileConfigurationsController">
                        <?php if($this->controller->action == 'create'): ?>
                            <p class="allert warning">Antes de agregar adjuntos es preciso salvar el proyecto.</p>
                        <?php else: ?>
                            <p ng-if="data.entity.canUserModifyRegistrationFields" ><a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-files', $event)">Agregar adjunto</a></p>
                        <?php endif; ?>
                        <!-- edit-box to add attachment -->
                        <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files" position="right" title="Agregar adjunto" cancel-label="Cancelar" submit-label="Crear" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
                            <input type="text" ng-model="data.newFileConfiguration.title" placeholder="Nombre del adjunto"/>
                            <textarea ng-model="data.newFileConfiguration.description" placeholder="Descripción del adjunto"/></textarea>
                            <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required">  es obligatorio el envío de este adjunto para inscribirse en este proyecto</label></p>
                        </edit-box>
                        <!-- added attachments list -->
                        <ul class="attachment-list">
                            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                                <div class="js-open-editbox" ng-class="{'mc-editable': data.entity.canUserModifyRegistrationFields}" ng-click="openFileConfigurationEditBox(fileConfiguration.id, $index, $event);">
                                    <div class="label">{{fileConfiguration.title}}</div>
                                    <span class="attachment-description">{{fileConfiguration.description}} ({{fileConfiguration.required.toString() === 'true' ? 'Obligatorio' : 'Opcional'}})</span>
                                </div>
                                <!-- edit-box to edit attachment -->
                                <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-{{fileConfiguration.id}}" position="right" title="Editar Adjunto" cancel-label="Cancelar" submit-label="Salvar" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                                    <input type="text" ng-model="fileConfiguration.title" placeholder="Nombre del adjunto"/>
                                    <textarea ng-model="fileConfiguration.description" placeholder="Descripción del adjunto"/></textarea>
                                    <p><label><input type="checkbox" ng-model="fileConfiguration.required" ng-checked="fileConfiguration.required">  es obligatorio el envío de este adjunto para inscribirse en este proyecto</label></p>
                                </edit-box>
                                <div class="file-{{fileConfiguration.template.id}}" ng-if="fileConfiguration.template">
                                    <span ng-if="data.entity.canUserModifyRegistrationFields" class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);">{{fileConfiguration.template.name}}</span>
                                    <a ng-if="data.entity.canUserModifyRegistrationFields" class="delete hltip" ng-click="removeFileConfigurationTemplate(fileConfiguration.id, $index)" title="Eliminar modelo"></a>
                                </div>
                                <p ng-if="!data.entity.canUserModifyRegistrationFields">
                                    <a class="file-{{fileConfiguration.template.id}} attachment-template"  href="{{fileConfiguration.template.url}}" target="_blank">{{fileConfiguration.template.name}}</a>
                                </p>
                                <!-- edit-box to upload attachments -->
                                <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-template-{{fileConfiguration.id}}" position="top" title="Enviar modelo" cancel-label="Cancelar" submit-label="Enviar modelo" on-submit="sendFile" close-on-cancel='true' spinner-condition="data.uploadSpinner">
                                    <p ng-if="fileConfiguration.template">
                                        <a class="file-{{fileConfiguration.template.id}} attachment-template"  href="{{fileConfiguration.template.url}}" target="_blank">{{fileConfiguration.template.name}}</a>
                                    </p>
                                    <form class="js-ajax-upload" method="post" data-group="{{uploadFileGroup}}" action="{{getUploadUrl(fileConfiguration.id)}}" enctype="multipart/form-data">
                                        <div class="alert danger hidden"></div>
                                        <p class="form-help">Tamaño máximo de archivo: {{maxUploadSizeFormatted}}</p>
                                        <input type="file" name="{{uploadFileGroup}}" />

                                        <div class="js-ajax-upload-progress">
                                            <div class="progress">
                                                <div class="bar"></div >
                                                <div class="percent">0%</div >
                                            </div>
                                        </div>

                                    </form>
                                </edit-box>
                                <div ng-if="data.entity.canUserModifyRegistrationFields" class="btn-group">
                                    <a class="btn btn-default send hltip" title="enviar modelo" ng-if="!fileConfiguration.template" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);" >Enviar modelo</a>
                                    <a data-href="{{fileConfiguration.deleteUrl}}" ng-click="removeFileConfiguration(fileConfiguration.id, $index)" class="btn btn-default delete hltip" title="excluir anexo">Eliminar</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- #registration-attachments -->
            <?php endif; ?>

            <?php if($entity->isRegistrationOpen() && !$this->isEditable() && $entity->useRegistrations): ?>
                <?php if($app->auth->isUserAuthenticated()):?>
                    <div class="registration-fieldset hide-tablet">
                        <p class="registration-help">No es posible realizar las inscripciones online a través de este dispositivo. Intente hacerlo en un dispositivo con una pantalla más grande.</p>
                    </div>
                    <form id="project-registration" class="registration-form clearfix">
                        <p class="registration-help">Para iniciar su inscripción, seleccione el agente responsable. Él debe ser un agente individual (persona física).</p>
                        <div>
                            <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : 'Agente responsable por la inscripción'}}</div>
                            <edit-box id="editbox-select-registration-owner" position="bottom" title="Selecione el Agente responsable por la inscripción." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                                <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="Ningún agente encontrado" select="setRegistrationOwner" api-query='data.relationApiQuery.owner' spinner-condition="data.registrationSpinner"></find-entity>
                            </edit-box>
                        </div>
                        <div>
                            <a class="btn btn-primary" ng-click="register()">Realizar inscripción</a>
                        </div>
                    </form>
                <?php else: ?>
                        <p>Para inscribirse es preciso tener una cuenta y estar logueado en nuestra plataforma. Cliquee en el botón de abajo para crear una cuenta o ingresar a la plataforma.</p>
                        <a class="btn btn-primary" href="<?php echo $app->createUrl('auth','login') ?>?redirectTo=<?php echo $entity->singleUrl , urlencode("#tab=inscricoes") ?>">Entrar</a>
                <?php endif;?>
            <?php endif; ?>
        </div>
        <!--#inscricoes-->
        <div ng-if="data.projectRegistrationsEnabled" id="inscritos" class="aba-content">
            <?php if($entity->canUser('@control')): ?>
                <header id="header-inscritos" class="clearfix">
                    <h3>Inscriptos</h3>
                    <div class="alert info hide-tablet">
                        No es posible alterar el estado de las inscripciones a través de este dispositivo. Intente hacerlo desde uno dispositivo con pantalla más grande.
                        <div class="close"></div>
                    </div>
                    <a class="btn btn-default download" href="<?php echo $this->controller->createUrl('report', [$entity->id]); ?>">Descargar lista de inscriptos</a>
                </header>
                <div id='status-info' class="alert info">
                    <p>Cambie el estado de las inscripciones en la última columna de la tabla de acuerdo con el seguinte criterio:</p>
                    <ul>
                        <li><span>Inválida - no cumple con lo establecido (ejem. documentación incorrecta).</span></li>
                        <li><span>Pendiente - aún no evaluada.</span></li>
                        <li><span>No seleccionada - evaluada, pero no seleccionada.</span></li>
                        <li><span>Suplente - evaluada, esperando vacante.</span></li>
                        <li><span>Seleccionada - evaluada y seleccionada.</span></li>
                        <li><span>Borrador - utilice esta opción para permitir que el responsable edite y reenvíe una inscripción. Al seleccionar esta opción, la inscripción no aparecerá en esta tabla.</span></li>
                    </ul>
                    <div class="close"></div>
                </div>
                <table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
                    <thead>
                        <tr>
                            <th class="registration-id-col">
                                Inscripción
                            </th>
                            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                                <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
                            </th>
                            <th class="registration-agents-col">
                                Agentes
                            </th>
                            <th ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                                Adjuntos
                            </th>
                            <th class="registration-status-col">
                                <mc-select placeholder="status" model="data.registrationStatus" data="data.registrationStatuses"></mc-select>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan='5'>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0">Ninguna inscripción enviada.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0">Ninguna inscripción encontrada con los filtros seleccionados.</span>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1">1 inscripción enviada.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1">1 inscripción encontrada con los filtros seleccionados.</span>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscripciones enviadas.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscripciones encontradas con los filtros seleccionados.</span>
                            </td>
                        </tr>
                        <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" >
                            <td class="registration-id-col"><a href="{{reg.singleUrl}}">{{reg.number}}</a></td>
                            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                            <td class="registration-agents-col">
                                <p>
                                    <span class="label">responsable</span><br />
                                    <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                                </p>

                                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                                    <span class="label">{{relation.label}}</span><br />
                                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                                </p>
                            </td>
                            <td ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                                <a ng-if="reg.files.zipArchive.url" class="icon icon-download" href="{{reg.files.zipArchive.url}}"><div class="screen-reader-text">Descargar archivos</div></a>
                            </td>
                            <td class="registration-status-col">
                                <?php if($entity->publishedRegistrations): ?>
                                    <span class="status status-{{getStatusSlug(reg.status)}}">{{getStatusNameById(reg.status)}}</span>
                                <?php else: ?>
                                    <mc-select model="reg" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if($entity->canUser('@control')): ?>
                    <?php if($entity->publishedRegistrations): ?>
                    <div class="clearfix">
                        <p class='alert success'>el resultado ya fue publicado</p>
                    </div>
                    <?php else: ?>
                    <div class="clearfix">
                        <?php if($entity->canUser('publishRegistrations')): ?>
                            <a id="btn-publish-results" class="btn btn-primary" href="<?php echo $app->createUrl('project', 'publishRegistrations', [$entity->id]) ?>">Publicar resultados</a>
                        <?php else: ?>
                            <a id="btn-publish-results" class="btn btn-primary disabled hltip" title="Usted puede publicar los resultados después de haber finalizado el período de inscripciones.">Publicar resultados</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif($entity->publishedRegistrations): ?>
                <table class="js-registration-list registrations-table published-registration-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
                    <thead>
                        <tr>
                            <th class="registration-id-col">
                                Inscripción
                            </th>
                            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                                <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
                            </th>
                            <th class="registration-agents-col">
                                Agentes
                            </th>
                            <th class="registration-status-col">
                                <mc-select placeholder="status" model="data.publishedRegistrationStatus" data="data.publishedRegistrationStatuses"></mc-select>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan='5'>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0">Ninguna inscripción enviada.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0">Ninguna inscripción encontrada con los filtros seleccionados.</span>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1">1 inscripción enviada.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1">1 inscripción encontrada con los filtros seleccionados.</span>
                                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscripciones enviadas.</span>
                                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscripciones encontradas con los filtros seleccionados.</span>
                            </td>
                        </tr>
                        <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" ng-if="reg.status == 10 || reg.status == 8" >
                            <td class="registration-id-col"><strong>{{reg.number}}</strong></td>
                            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                            <td class="registration-agents-col">
                                <p>
                                    <span class="label">responsable</span><br />
                                    <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                                </p>

                                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                                    <span class="label">{{relation.label}}</span><br />
                                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                                </p>
                            </td>
                            <td class="registration-status-col">
                                <?php if($entity->publishedRegistrations): ?>
                                    <span class="status status-{{getStatusSlug(reg.status)}}">{{getStatusNameById(reg.status)}}</span>
                                <?php else: ?>
                                    <mc-select model="reg" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <!--#inscritos-->
        
        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="sidebar-left sidebar project">
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>
<div class="sidebar project sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info">Para agregar archivos para descargar o links, primero es preciso salvar el proyecto.<span class="close"></span></p>
        </div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="widget">
            <?php if($entity->children && $entity->children->count()): ?>
            <h3>Sub-Proyectos</h3>
            <ul class="widget-list js-slimScroll">
                <?php foreach($entity->children as $space): ?>
                <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><span><?php echo $space->name; ?></span></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entity->id && $entity->canUser('createChild')): ?>
            <a class="btn btn-default add" href="<?php echo $app->createUrl('project','create', array('parentId' => $entity->id)) ?>">Agregar sub-proyecto</a>
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

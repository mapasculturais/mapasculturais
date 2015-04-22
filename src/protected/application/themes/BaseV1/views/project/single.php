<?php
use MapasCulturais\Entities\Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "Entity";

$this->addProjectToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');
}
$this->includeAngularEntityAssets($entity);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content project" ng-controller="ProjectController">
    <header class="main-content-header">
        <div
            <?php if ($header = $entity->getFile('header')): ?>
                style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="header-image js-imagem-do-header"
            <?php elseif($this->isEditable()): ?>
                class="header-image js-imagem-do-header"
            <?php endif; ?>
            >
            <?php if ($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-header" href="#">Editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php $this->ajaxUploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
                </div>
            <?php endif; ?>
        </div>
        <!--.header-image-->
        <div class="header-content">
            <div class="avatar <?php if($entity->avatar): ?>com-imagem<?php endif; ?>">
                <?php if($avatar = $entity->avatar): ?>
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                    <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
                <?php endif; ?>
                <?php if($this->isEditable()): ?>
                    <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#">Editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php $this->ajaxUploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
                <!-- pro responsivo!!! -->
                <?php if($entity->isVerified): ?>
                    <a class="verified-seal hltip active" title="Este <?php echo $entity->entityType ?> é verificado." href="#"></a>
                <?php endif; ?>
            </div>
            <!--.avatar-->
            <div class="entity-type project-type">
                <div class="icon icon-project"></div>
                <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Selecione um tipo" data-entity='project' data-value='<?php echo $entity->type ?>'>
                    <?php echo $entity->type? $entity->type->name : ''; ?>
                </a>
            </div>
            <?php if($this->isEditable() && $entity->canUser('modifyParent')): ?>
            <span  class="js-search js-include-editable"
                   data-field-name='parentId'
                   data-emptytext="Selecionar projeto pai"
                   data-search-box-width="400px"
                   data-search-box-placeholder="Selecionar projeto pai"
                   data-entity-controller="project"
                   data-search-result-template="#agent-search-result-template"
                   data-selection-template="#agent-response-template"
                   data-no-result-template="#agent-response-no-results-template"
                   data-selection-format="parentProject"
                   data-allow-clear="1"
                   title="Selecionar projeto pai"
                   data-value="<?php if($entity->parent) echo $entity->parent->id; ?>"
                   data-value-name="<?php if($entity->parent) echo $entity->parent->name; ?>"
             ><?php if($entity->parent) echo $entity->parent->name; ?></span>

            <?php elseif($entity->parent): ?>
                <h4 class="entity-parent-title"><a href="<?php echo $entity->parent->singleUrl; ?>"><?php echo $entity->parent->name; ?></a></h4>
            <?php endif; ?>
            <h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
        </div>
    </header>

    <ul class="abas clearfix">
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>

        <li ng-if="data.projectRegistrationsEnabled"><a href="#inscricoes">Inscrições</a></li>
        <?php if($entity->publishedRegistrations): ?>
            <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Resultado</a></li>
        <?php elseif($entity->canUser('@control')): ?>
            <li ng-if="data.projectRegistrationsEnabled"><a href="#inscritos">Inscritos</a></li>
        <?php endif; ?>
    </ul>

    <div id="sobre" class="aba-content">


        <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
            <div class="highlighted-message clearfix">
                <?php if($this->isEditable() || $entity->registrationFrom): ?>
                    <div class="registration-dates">
                        Inscrições abertas de
                        <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="Data inicial"><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong>
                        a
                        <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="Data final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong>
                        às
                        <strong class="js-editable" id="registrationTo_time" data-datetime-value="<?php echo $entity->registrationTo ? $entity->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="Hora final" data-emptytext="Hora final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('H:i') : ''; ?></strong>
                        .
                    </div>
                <?php endif; ?>
                <?php if ($entity->useRegistrations && !$this->isEditable() ) : ?>
                    <a ng-if="data.projectRegistrationsEnabled" class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()">Inscrições online</a>
                <?php endif; ?>
                <div class="clear" ng-if="data.projectRegistrationsEnabled && data.isEditable">Inscrições online <strong><span id="editable-use-registrations" class="js-editable clear" data-edit="useRegistrations" data-type="select" data-value="<?php echo $entity->useRegistrations ? '1' : '0' ?>"
                        data-source="[{value: 0, text: 'desativadas'},{value: 1, text:'ativadas'}]"></span></strong>
                </div>

            </div>
        <?php endif; ?>
        <div class="ficha-spcultura">
            <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
                <div class="alert warning">O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui <?php echo strlen($entity->shortDescription) ?> caracteres. Você deve alterar seu texto ou este será cortado ao salvar.</div>
            <?php endif; ?>

            <p>
                <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
            </p>

            <div class="servico">
                <?php if($this->isEditable() || $entity->site): ?>
                    <p>
                        <span class="label">Site:</span>
                        <span ng-if="data.isEditable" class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span>
                        <a ng-if="!data.isEditable" class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( $this->isEditable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Projeto" data-emptytext="Insira uma descrição do projeto" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
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
                Utilize este espaço caso queira abrir inscrições para Agentes Culturais cadastrados na plataforma.
                <span class="close"></span>
            </p>
        <?php endif; ?>
        <?php if($registrations = $app->repo('Registration')->findByProjectAndUser($entity, $app->user)): ?>
                <table class="my-registrations">
                    <caption>Minhas inscrições</caption>
                    <thead>
                        <tr>
                            <th class="registration-id-col">
                                Inscrição
                            </th>
                            <th class="registration-agents-col">
                                Agentes
                            </th>
                            <th class="registration-status-col">
                                Status
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
                                    <span class="label">Responsável</span><br>
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
                                    Enviada em <?php echo $registration->sentTimestamp->format('d/m/Y à\s H:i'); ?>.
                                <?php else: ?>
                                    Não enviada.<br>
                                    <a class="btn btn-small btn-primary" href="<?php echo $registration->singleUrl ?>">Editar e enviar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        <?php endif; ?>
        <?php if($entity->introInscricoes || $this->isEditable()): ?>

            <div id="intro-das-inscricoes" ng-class="{'registration-fieldset': data.isEditable}">
                <h4 ng-if="data.isEditable">1. Introdução</h4>
                <p class="registration-help" ng-if="data.isEditable">Crie um texto de introdução.</p>
                <p class="js-editable" data-edit="introInscricoes" data-original-title="Introdução da inscrição" data-emptytext="Insira o texto." data-placeholder="Insira o texto." data-showButtons="bottom" data-placement="bottom"><?php echo $this->isEditable() ? $entity->introInscricoes : nl2br($entity->introInscricoes); ?></p>
            </div>
            <!-- #intro-das-inscricoes -->
        <?php endif; ?>
        <p ng-if="!data.isEditable && data.entity.registrationRulesFile"><a class="btn btn-default download" href="{{data.entity.registrationRulesFile.url}}" >Baixar o regulamento</a></p>
        <div ng-if="data.isEditable" class="registration-fieldset">
            <h4>2. Regulamento</h4>

            <?php if($this->controller->action == 'create'): ?>
                <p class="allert warning">Antes de subir o regulamento é preciso salvar o projeto.</p>

            <?php else: ?>
                <p class="registration-help">Envie um arquivo com o regulamento. Formatos aceitos .doc, .odt e .pdf.</p>
                <a class="btn btn-default send hltip" ng-if="!data.entity.registrationRulesFile" ng-click="openRulesUploadEditbox($event)" title="Enviar regulamento" >Enviar</a>
                <div ng-if="data.entity.registrationRulesFile">
                    <span class="js-open-editbox mc-editable" ng-click="openRulesUploadEditbox($event)">{{data.entity.registrationRulesFile.name}}</span>
                    <a class="delete hltip" ng-click="removeRegistrationRulesFile()" title="excluir regulamento"></a>
                </div>
                <edit-box id="edibox-upload-rules" position="bottom" title="Regulamento" submit-label="Enviar" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendRegistrationRulesFile" on-cancel="closeRegistrationRulesUploadEditbox" spinner-condition="data.uploadSpinner">
                    <form class="js-ajax-upload" method="post" action="<?php echo $app->createUrl('project', 'upload', array($entity->id))?>" data-group="rules"  enctype="multipart/form-data">
                        <div class="alert danger hidden"></div>
                        <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
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
                <h4>3. Opções</h4>
                <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">É possível criar opções para os proponentes escolherem na hora de se inscrever, como, por exemplo, "categorias" ou "modalidades". Se não desejar utilizar este recurso, deixe em branco o campo "Opções".</p>
                <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto. </p>
                <p>
                    <span class="label">Título das opções</span><br>
                    <span class="<?php echo $ditable_class ?>" data-edit="registrationCategTitle" data-original-title="Título das opções" data-emptytext="Insira um título para o campo de opções"><?php echo $entity->registrationCategTitle ?  $entity->registrationCategTitle : 'Categoria'; ?></span>
                </p>
                <p>
                    <span class="label">Descrição das opções</span><br>
                    <span class="<?php echo $ditable_class ?>" data-edit="registrationCategDescription" data-original-title="Descrição das opções" data-emptytext="Insira uma descrição para o campo de opções"><?php echo $entity->registrationCategDescription ? $entity->registrationCategDescription : 'Selecione uma categoria'; ?></span>
                </p>
                <p>
                    <span class="label">Opções</span><br>
                    <span class="<?php echo $ditable_class ?>" data-edit="registrationCategories" data-type="textarea" data-original-title="Opções de inscrição (coloque uma opção por linha)" data-emptytext="Insira as opções de inscrição"><?php echo $registration_categories; ?></span>
                </p>
            </div>
            <!-- #registration-categories -->
            <div id="registration-agent-relations" class="registration-fieldset">
                <h4>4. Agentes</h4>
                <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Toda inscrição obrigatoriamente deve possuir um Agente Individual responsável, mas é possível que a inscrição seja feita em nome de um agente coletivo, com ou sem CNPJ. Nesses casos, é preciso definir abaixo se essas informações são necessárias e se são obrigatórias.</p>
                <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto. </p>

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
                            <span class="<?php echo $ditable_class ?>" data-edit="<?php echo $metadata_name ?>" data-original-title="<?php echo $def->metadataConfiguration['label'] ?>" data-emptytext="Selecione uma opção"><?php echo $option_label ?></span>
                        </p>

                    </div>
                <?php endforeach; ?>

                <p>
                    <span class="label">Número máximo de inscrições por agente responsável</span><br>
                    <span class="registration-help">Zero (0) significa sem limites</span><br>
                    <span class="<?php echo $ditable_class ?>" data-edit="registrationLimitPerOwner" data-original-title="Número máximo de inscrições por agente responsável" data-emptytext="Insira o número máximo de inscrições por agente responsável"><?php echo $entity->registrationLimitPerOwner ? $entity->registrationLimitPerOwner : '0'; ?></span>
                </p>
            </div>
            <!-- #registration-agent-relations -->
            <div id="registration-attachments" class="registration-fieldset">
                <h4>5. Anexos</h4>
                <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help">Você pode pedir para os proponentes enviarem anexos para se inscrever no seu projeto. Para cada anexo, você pode fornecer um modelo, que o proponente poderá baixar, preencher, e fazer o upload novamente.</p>
                <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help">A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto. </p>
                <div ng-controller="RegistrationFileConfigurationsController">
                    <?php if($this->controller->action == 'create'): ?>
                        <p class="allert warning">Antes de adicionar anexos é preciso salvar o projeto.</p>
                    <?php else: ?>
                        <p ng-if="data.entity.canUserModifyRegistrationFields" ><a class="btn btn-default add" title="" ng-click="editbox.open('editbox-registration-files', $event)">Adicionar anexo</a></p>
                    <?php endif; ?>
                    <!-- edit-box to add attachment -->
                    <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files" position="right" title="Adicionar anexo" cancel-label="Cancelar" submit-label="Criar" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
                        <input type="text" ng-model="data.newFileConfiguration.title" placeholder="Nome do anexo"/>
                        <textarea ng-model="data.newFileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
                        <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
                    </edit-box>
                    <!-- added attachments list -->
                    <ul class="attachment-list">
                        <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                            <div class="js-open-editbox" ng-class="{'mc-editable': data.entity.canUserModifyRegistrationFields}" ng-click="openFileConfigurationEditBox(fileConfiguration.id, $index, $event);">
                                <div class="label">{{fileConfiguration.title}}</div>
                                <span class="attachment-description">{{fileConfiguration.description}} ({{fileConfiguration.required.toString() === 'true' ? 'Obrigatório' : 'Opcional'}})</span>
                            </div>
                            <!-- edit-box to edit attachment -->
                            <edit-box ng-if="data.entity.canUserModifyRegistrationFields" id="editbox-registration-files-{{fileConfiguration.id}}" position="right" title="Editar Anexo" cancel-label="Cancelar" submit-label="Salvar" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                                <input type="text" ng-model="fileConfiguration.title" placeholder="Nome do anexo"/>
                                <textarea ng-model="fileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
                                <p><label><input type="checkbox" ng-model="fileConfiguration.required" ng-checked="fileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
                            </edit-box>
                            <div class="file-{{fileConfiguration.template.id}}" ng-if="fileConfiguration.template">
                                <span ng-if="data.entity.canUserModifyRegistrationFields" class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);">{{fileConfiguration.template.name}}</span>
                                <a ng-if="data.entity.canUserModifyRegistrationFields" class="delete hltip" ng-click="removeFileConfigurationTemplate(fileConfiguration.id, $index)" title="Excluir modelo"></a>
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
                                    <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
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
                                <a data-href="{{fileConfiguration.deleteUrl}}" ng-click="removeFileConfiguration(fileConfiguration.id, $index)" class="btn btn-default delete hltip" title="excluir anexo">Excluir</a>
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
                    <p class="registration-help">Não é possível realizar as inscrições online através desse dispositivo. Tente se inscrever a partir de um dispositivo com a tela maior.</p>
                </div>
                <form id="project-registration" class="registration-form clearfix">
                    <p class="registration-help">Para iniciar sua inscrição, selecione o agente responsável. Ele deve ser um agente individual (pessoa física), com um CPF válido preenchido.</p>
                    <div>
                        <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : 'Agente responsável pela inscrição'}}</div>
                        <edit-box id="editbox-select-registration-owner" position="bottom" title="Selecione o agente responsável pela inscrição." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                            <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationOwner" api-query='data.relationApiQuery.owner' spinner-condition="data.registrationSpinner"></find-entity>
                        </edit-box>
                    </div>
                    <div>
                        <a class="btn btn-primary" ng-click="register()">Fazer inscrição</a>
                    </div>
                </form>
            <?php else: ?>
                    <p>Para se inscrever é preciso ter uma conta e estar logado nesta plataforma. Clique no botão abaixo para criar uma conta ou fazer login.</p>
                    <a class="btn btn-primary" href="<?php echo $app->createUrl('auth','login') ?>?redirectTo=<?php echo $entity->singleUrl , urlencode("#tab=inscricoes") ?>">Entrar</a>
            <?php endif;?>
        <?php endif; ?>
    </div>
    <!--#inscricoes-->
    <div ng-if="data.projectRegistrationsEnabled" id="inscritos" class="aba-content">
        <?php if($entity->canUser('@control')): ?>
            <header id="header-inscritos" class="clearfix">
                <h3>Inscritos</h3>
                <div class="alert info hide-tablet">
                    Não é possível alterar o status das inscrições através desse dispositivo. Tente a partir de um dispositivo com tela maior.
                    <div class="close"></div>
                </div>
                <a class="btn btn-default download" href="<?php echo $this->controller->createUrl('report', [$entity->id]); ?>">Baixar lista de inscritos</a>
            </header>
            <div id='status-info' class="alert info">
                <p>Altere os status das inscrições na última coluna da tabela de acordo com o seguinte critério:</p>
                <ul>
                    <li><span>Inválida - em desacordo com o regulamento (ex. documentação incorreta).</span></li>
                    <li><span>Pendente - ainda não avaliada.</span></li>
                    <li><span>Não selecionada - avaliada, mas não selecionada.</span></li>
                    <li><span>Suplente - avaliada, mas aguardando vaga.</span></li>
                    <li><span>Selecionada - avaliada e selecionada.</span></li>
                    <li><span>Rascunho - utilize essa opção para permitir que o responsável edite e reenvie uma inscrição. Ao selecionar esta opção, a inscrição não será mais exibida nesta tabela.</span></li>
                </ul>
                <div class="close"></div>
            </div>
            <table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
                <thead>
                    <tr>
                        <th class="registration-id-col">
                            Inscrição
                        </th>
                        <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                            <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
                        </th>
                        <th class="registration-agents-col">
                            Agentes
                        </th>
                        <th ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                            Anexos
                        </th>
                        <th class="registration-status-col">
                            <mc-select placeholder="status" model="data.registrationStatus" data="data.registrationStatuses"></mc-select>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan='5'>
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição enviada.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição encontrada com os filtros selecionados.</span>
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1">1 inscrição enviada.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length === 1">1 inscrição encontrada com os filtros selecionados.</span>
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições enviadas.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições encontradas com os filtros selecionados.</span>
                        </td>
                    </tr>
                    <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" >
                        <td class="registration-id-col"><a href="{{reg.singleUrl}}">{{reg.number}}</a></td>
                        <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                        <td class="registration-agents-col">
                            <p>
                                <span class="label">Responsável</span><br />
                                <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                            </p>

                            <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                                <span class="label">{{relation.label}}</span><br />
                                <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                            </p>
                        </td>
                        <td ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                            <a ng-if="reg.files.zipArchive.url" class="icon icon-download" href="{{reg.files.zipArchive.url}}"><div class="screen-reader-text">Baixar arquivos</div></a>
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
                    <p class='alert success'>O resultado já foi publicado</p>
                </div>
                <?php else: ?>
                <div class="clearfix">
                    <?php if($entity->canUser('publishRegistrations')): ?>
                        <a id="btn-publish-results" class="btn btn-primary" href="<?php echo $app->createUrl('project', 'publish', [$entity->id]) ?>">Publicar resultados</a>
                    <?php else: ?>
                        <a id="btn-publish-results" class="btn btn-primary disabled hltip" title="Você só pode publicar a lista de aprovados após o término do período de inscrições.">Publicar resultados</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php elseif($entity->publishedRegistrations): ?>
            <table class="js-registration-list registrations-table published-registration-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
                <thead>
                    <tr>
                        <th class="registration-id-col">
                            Inscrição
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
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição enviada.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição encontrada com os filtros selecionados.</span>
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1">1 inscrição enviada.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length === 1">1 inscrição encontrada com os filtros selecionados.</span>
                            <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições enviadas.</span>
                            <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições encontradas com os filtros selecionados.</span>
                        </td>
                    </tr>
                    <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" ng-if="reg.status == 10 || reg.status == 8" >
                        <td class="registration-id-col"><strong>{{reg.number}}</strong></td>
                        <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
                        <td class="registration-agents-col">
                            <p>
                                <span class="label">Responsável</span><br />
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
            <p class="alert info">Para adicionar arquivos para download ou links, primeiro é preciso salvar o projeto.<span class="close"></span></p>
        </div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="widget">
            <?php if($entity->children): ?>
            <h3>Sub-projetos</h3>
            <ul class="widget-list js-slimScroll">
                <?php foreach($entity->children as $space): ?>
                <li class="widget-list-item"><a href="<?php echo $space->singleUrl; ?>"><span><?php echo $space->name; ?></span></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if($entity->id && $entity->canUser('createChield')): ?>
            <a class="btn btn-default add staging-hidden" href="<?php echo $app->createUrl('project','create', array('parentId' => $entity->id)) ?>">Adicionar sub-projeto</a>
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

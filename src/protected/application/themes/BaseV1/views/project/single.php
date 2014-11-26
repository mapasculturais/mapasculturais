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

<div class="sidebar-left sidebar project">
    <div class="setinha"></div>
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>

<article class="main-content project" ng-controller="ProjectController">
    <header class="main-content-header">
        <div
            <?php if($header = $entity->getFile('header')): ?>
                 style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem js-imagem-do-header"
                 <?php elseif($this->isEditable()): ?>
                 class="imagem-do-header js-imagem-do-header"
            <?php endif; ?>
        >
            <?php if($this->isEditable()): ?>
                <a class="botao editar js-open-editbox" data-target="#editbox-change-header" href="#">editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php $this->ajaxUploader ($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
                </div>
            <?php endif; ?>
        </div>
        <!--.imagem-do-header-->
        <div class="content-do-header">
            <div class="avatar <?php if($entity->avatar): ?>com-imagem<?php endif; ?>">
                <?php if($avatar = $entity->avatar): ?>
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                    <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
                <?php endif; ?>
                <?php if($this->isEditable()): ?>
                    <a class="botao editar js-open-editbox" data-target="#editbox-change-avatar" href="#">editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php $this->ajaxUploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <!--.avatar-->
            <div class="entity-type project-type">
                <div class="icone icon_document_alt"></div>
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
        <li><a href="#inscricoes">Inscrições</a></li>
        <?php if($entity->publishedRegistrations): ?>
            <li><a href="#inscritos">Resultado</a></li>
        <?php else: ?>
            <li><a href="#inscritos">Inscritos</a></li>
        <?php endif; ?>
    </ul>

    <div id="sobre" class="aba-content">
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
    <div id="inscricoes" class="aba-content">
        <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
            <p ng-if="data.isEditable" class="alert info">
                Utilize este espaço caso queira abrir inscrições para Agentes Culturais cadastrados na plataforma.
                <span class="close"></span>
            </p>
            <div id="registration-period" ng-class="{'registration-fieldset': data.isEditable}">
                <h4 ng-if="data.isEditable">1. Período de inscrições</h4>

                <?php if($this->isEditable() || $entity->registrationFrom): ?>
                    <p class="highlighted-message">
                        Inscrições abertas de
                        <span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong></span>
                        a
                        <span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong></span>.
                    </p>
                <?php endif; ?>
            </div>
            <!-- #registration-period -->
        <?php endif; ?>
        <?php if($registrations = $app->repo('Registration')->findByProjectAndUser($entity, $app->user)): ?>
                <h4>Minhas Inscrições</h4>
                <table class="my-registrations">
                    <thead>
                        <tr>
                            <th class="registration-id-col">
                                Nº
                            </th>
                            <th class="registration-agents-col">
                                Agente Responsável
                            </th>
                            <?php
                            foreach($app->getRegisteredRegistrationAgentRelations() as $def):
                                if(!$entity->useRegistrationAgentRelation($def))
                                    continue;

                            ?>
                            <th class="registration-agents-col">
                                <?php echo $def->label ?>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($registrations as $registration) ?>
                        <tr>
                            <td class="registration-id-col">
                            <a href="<?php echo $registration->singleUrl ?>"><?php echo $registration->number ?></a>
                            </td>
                            <td class="registration-agents-col">
                                <?php echo $registration->owner->name ?>
                            </td>
                            <?php
                            foreach($app->getRegisteredRegistrationAgentRelations() as $def):
                                if(!$entity->useRegistrationAgentRelation($def))
                                    continue;
                            ?>
                            <td class="registration-agents-col">
                                <?php if($agents = $registration->getRelatedAgents($def->agentRelationGroupName)): ?>
                                <?php echo $agents[0]->name ?>
                                <?php endif; ?>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
        <?php endif; ?>
        <?php if($entity->introInscricoes || $this->isEditable()): ?>

            <div id="intro-das-inscricoes" ng-class="{'registration-fieldset': data.isEditable}">
                <h4 ng-if="data.isEditable">2. Introdução</h4>
                <p class="registration-help" ng-if="data.isEditable">Você pode criar um texto de introdução de apenas um parágrafo.</p>
                <p class="js-editable" data-edit="introInscricoes" data-original-title="Introdução da inscrição" data-emptytext="Insira um parágrafo." data-placeholder="Insira um parágrafo." data-showButtons="bottom" data-placement="bottom"><?php echo $this->isEditable() ? $entity->introInscricoes : nl2br($entity->introInscricoes); ?></p>
            </div>
            <!-- #intro-das-inscricoes -->
        <?php endif; ?>
        <p><a class="botao download" href="{{data.entity.registrationRulesFile.url}}" ng-if="!data.isEditable && data.entity.registrationRulesFile" >Baixar o regulamento</a></p>
        <div ng-if="data.isEditable" class="registration-fieldset">
            <h4 >3. Regulamento</h4>
            <p class="registration-help">Mussum ipsum cacilds, vidis litro abertis. Consetis adipiscings elitis. Pra lá , depois divoltis porris, paradis. Paisis, filhis, espiritis santis.</p>

            <div class="btn-group">
                <!-- se já subiu o arquivo-->
                <!-- se não subiu ainda -->
                <a class="botao hltip" ng-class="{'editar':data.entity.registrationRulesFile, 'enviar':!data.entity.registrationRulesFile}" ng-click="openRulesUploadEditbox($event)" title="{{data.entity.registrationRulesFile ? 'editar' : 'enviar'}} regulamento">{{!data.entity.registrationRulesFile ? 'enviar' : 'editar'}}</a>
                <a ng-click="removeRegistrationRulesFile()" class="botao excluir hltip" title="excluir anexo">excluir</a>
            </div>
            <edit-box id="edibox-upload-rules" position="bottom" title="{{data.entity.registrationRulesFile ? 'Enviar regulamento' : 'Editar regulamento'}}" submit-label="Enviar" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendRegistrationRulesFile" on-cancel="closeRegistrationRulesUploadEditbox" spinner-condition="data.uploadSpinner">
                <form class="js-ajax-upload" method="post" action="<?php echo $app->createUrl('project', 'upload', array($entity->id))?>" data-group="rules"  enctype="multipart/form-data">
                    <div class="alert danger escondido"></div>
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
        </div>
        <!-- #registration-rules -->
        <?php if($this->isEditable()): ?>
            <div id="registration-categories" class="registration-fieldset">
                <h4>4. Opções</h4>
                <p class="registration-help">Também é possível criar opções para os inscritos escolherem na hora de se inscrever.
                    <br>
                    Para não utilizar opções, deixe em branco o campo "Opções selecionáveis"
                </p>
                <p>
                    <span class="label">Título das opções</span><br>
                    <span class="js-editable" data-edit="registrationCategTitle" data-original-title="Título das opções" data-emptytext="Insira um título para o campo de opções"><?php echo $entity->registrationCategTitle ?  $entity->registrationCategTitle : 'Categorias'; ?></span>
                </p>
                <p>
                    <span class="label">Descrição das opções</span><br>
                    <span class="js-editable" data-edit="registrationCategDescription" data-original-title="Descrição das opções" data-emptytext="Insira uma descrição para o campo de opções"><?php echo $entity->registrationCategDescription ? $entity->registrationCategDescription : 'Selecione uma categoria'; ?></span>
                </p>
                <p>
                    <span class="label">Opções selecionáveis</span><br>
                    <span class="js-editable" data-edit="registrationCategories" data-type="textarea" data-original-title="Opções de inscrição (coloque uma opção por linha)" data-emptytext="Insira as opções de inscrição"><?php echo $entity->registrationCategories ? implode("\n", $entity->registrationCategories) : ''; ?></span>
                </p>
            </div>
            <!-- #registration-categories -->
            <div id="registration-agent-relations" class="registration-fieldset">
                <h4>5. Agentes</h4>
                <p class="registration-help">Toda inscrição obrigatoriamente deve possuir um Agente Individual responsável, mas é possível que a inscrição seja feita em nome de um Agente Coletivo, com ou sem CNPJ. Nesses casos, é preciso definir abaixo se essas informações são necessárias e se são obrigatórias.</p>

                <?php foreach($app->getRegisteredRegistrationAgentRelations() as $def): $metadata_name = $def->metadataName;?>
                    <div class="registration-related-agent-configuration">
                        <p>
                            <span class="label"><?php echo $def->label ?></span> <span class="registration-help">(<?php echo $def->description ?>)</span>
                            <br>
                            <span class="js-editable" data-edit="<?php echo $metadata_name ?>" data-original-title="<?php echo $def->metadataConfiguration['label'] ?>" data-emptytext="Selecione uma opção"><?php echo $entity->$metadata_name ? $entity->$metadata_name : 'optional' ?></span>
                        </p>

                    </div>
                <?php endforeach; ?>
            </div>
            <!-- #registration-agent-relations -->
            <div id="registration-attachments" class="registration-fieldset">
                <h4>6. Anexos</h4>
                <p class="registration-help">Você pode pedir para os proponentes enviarem anexos para se inscrever no seu projeto. Para cada anexo, você pode fornecer um modelo, que o proponente poderá baixar, preencher, e fazer o upload novamente.</p>
                <div ng-controller="RegistrationFileConfigurationsController">
                    <?php if($this->controller->action == 'create'): ?>
                        <p class="allert warning">Antes de adicionar anexos é preciso salvar o projeto.</p>
                    <?php else: ?>
                        <p><a class="botao adicionar" title="" ng-click="editbox.open('editbox-registration-files', $event)">adicionar anexo</a></p>
                    <?php endif; ?>
                    <!-- edit-box to add attachment -->
                    <edit-box id="editbox-registration-files" position="bottom" title="Adicionar Anexo" cancel-label="Cancelar" submit-label="Criar" close-on-cancel='true' on-cancel="closeNewFileConfigurationEditBox" on-submit="createFileConfiguration" spinner-condition="data.uploadSpinner">
                        <input type="text" ng-model="data.newFileConfiguration.title" placeholder="Nome do anexo"/>
                        <textarea ng-model="data.newFileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
                        <p><label><input type="checkbox" ng-model="data.newFileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
                    </edit-box>
                    <!-- added attachments list -->
                    <ul class="attachment-list">
                        <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item">
                            <div class="mc-editable js-open-editbox" ng-click="openFileConfigurationEditBox(fileConfiguration.id, $index, $event);">
                                <div class="label">{{fileConfiguration.title}}</div>
                                <span class="attachment-description">{{fileConfiguration.description}} ({{fileConfiguration.required === true ? 'Obrigatório' : 'Opcional'}})</span>
                            </div>
                            <!-- edit-box to edit attachment -->
                            <edit-box id="editbox-registration-files-{{fileConfiguration.id}}" position="bottom" title="Editar Anexo" cancel-label="Cancelar" submit-label="Salvar" close-on-cancel='true' on-cancel="cancelFileConfigurationEditBox" on-submit="editFileConfiguration" index="{{$index}}" spinner-condition="data.uploadSpinner">
                                <input type="text" ng-model="fileConfiguration.title" placeholder="Nome do anexo"/>
                                <textarea ng-model="fileConfiguration.description" placeholder="Descrição do anexo"/></textarea>
                                <p><label><input type="checkbox" ng-model="fileConfiguration.required">  É obrigatório o envio deste anexo para se inscrever neste projeto</label></p>
                            </edit-box>
                            <div class="file-{{fileConfiguration.template.id}}" ng-if="fileConfiguration.template">
                                <span class="js-open-editbox mc-editable attachment-title" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);">{{fileConfiguration.template.name}}</span>
                                <a class="excluir hltip" ng-click="removeFileConfigurationTemplate(fileConfiguration.id, $index)" hltitle="excluir modelo"></a>
                            </div>
                            <!-- edit-box to upload attachments -->
                            <edit-box id="editbox-registration-files-template-{{fileConfiguration.id}}" position="bottom" title="Enviar modelo" cancel-label="Cancelar" submit-label="Enviar modelo" on-submit="sendFile" close-on-cancel='true' spinner-condition="data.uploadSpinner">
                                <p ng-if="fileConfiguration.template">
                                    <a class="file-{{fileConfiguration.template.id}} attachment-template"  href="{{fileConfiguration.template.url}}" target="_blank">{{fileConfiguration.template.name}}</a>
                                </p>
                                <form class="js-ajax-upload" method="post" data-group="{{uploadFileGroup}}" action="{{getUploadUrl(fileConfiguration.id)}}" enctype="multipart/form-data">
                                    <div class="alert danger escondido"></div>
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
                            <div class="btn-group">
                                <a class="botao enviar hltip" title="enviar modelo" ng-if="!fileConfiguration.template" ng-click="openFileConfigurationTemplateEditBox(fileConfiguration.id, $index, $event);" >enviar modelo</a>
                                <a data-href="{{fileConfiguration.deleteUrl}}" ng-click="removeFileConfiguration(fileConfiguration.id, $index)" class="botao excluir hltip" title="excluir anexo">excluir</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- #registration-attachments -->
        <?php endif; ?>

        <?php if($entity->isRegistrationOpen() && !$this->isEditable()): ?>
            <?php if($app->auth->isUserAuthenticated()):?>
                <form id="project-registration" class="registration-form clearfix">
                    <div>
                        <div id="select-registration-owner-button" class="input-text" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : 'Agente responsável'}}</div>
                        <edit-box id="editbox-select-registration-owner" position="bottom" title="Selecione o agente responsável pela inscrição." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                            <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationOwner" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
                        </edit-box>
                    </div>
                    <div>
                        <a class="botao principal" ng-click="register()">Fazer inscrição</a>
                    </div>
                </form>
            <?php else: ?>
                    <p>Para se inscrever é preciso ter uma conta e estar logado nesta plataforma. Clique no botão abaixo para criar uma conta ou fazer login.</p>
                    <a class="botao principal" href="<?php echo $app->createUrl('auth','login') ?>?redirectTo=<?php echo $entity->singleUrl , urlencode("#tab=inscricoes") ?>">Entrar</a>
            <?php endif;?>
        <?php endif; ?>
    </div>
    <!--#inscricoes-->
    <div id="inscritos" class="aba-content privado">
        <?php if($entity->canUser('@control')): ?>
            <div class="clearfix">
                <h3 class="alignleft"><span class="icone icon_lock"></span>Inscritos</h3>
                <a class="alignright botao download" href="#">baixar lista de inscritos</a>
            </div>
            <div class="alert info">
                <p>Altere os status das inscrições na última coluna da tabela de acordo com o seguinte critério:</p>
                <ul>
                    <li><span>Inválida - em desacordo com o regulamento (ex. documentação incorreta).</span></li>
                    <li><span>Pendente - ainda não avaliada.</span></li>
                    <li><span>Rejeitada - avaliada, mas não aprovada.</span></li>
                    <li><span>Suplente - avaliada, mas aguardando vaga.</span></li>
                    <li><span>Aprovada - avaliada e aprovada.</span></li>
                    <li><span>Rascunho - utilize essa opção para permitir que o responsável edite e reenvie uma inscrição. Ao selecionar esta opção, a inscrição não será mais exibida nesta tabela.</span></li>
                </ul>
                <div class="close"></div>
            </div>


            <table class="js-registration-list registrations-table <!-- registrations-results -->">
                <thead>
                    <tr>
                        <th class="registration-id-col">
                            Inscrição
                        </th>
                        <th class="registration-option-col">
                            <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
                        </th>
                        <th class="registration-agents-col">
                            Agentes
                        </th>
                        <th class="registration-attachments-col">
                            Anexos
                        </th>
                        <th class="registration-status-col">
                            <mc-select placeholder="status" model="data.registrationStatus" data="data.registrationStatuses"></mc-select>
                        </th>
                    </tr>
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
                </thead>
                <caption></caption>
                <tbody>
                    <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" >
                        <td class="registration-id-col"><a href="{{reg.singleUrl}}">{{reg.number}}</a></td>
                        <td class="registration-option-col">{{reg.category}}</td>
                        <td class="registration-agents-col">
                            <p>
                                <span class="label">Responsável</span><br />
                                <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                            </p>

                            <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                                <span class="label">relation.label</span><br />
                                <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                            </p>
                        </td>
                        <td class="registration-attachments-col">
                            <a class="icone icon_download" href="{{reg.files.zipArchive.url}}"><span class="screen-reader">file.name</span></a>
                        </td>
                        <td class="registration-status-col">
                            <?php if($entity->publishedRegistrations): ?>
                                <span class="status-{{getStatusSlug(reg.status)}}">{{getStatusSlug(reg.status)}}</span>
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
                    <a class="alignright botao principal <?php if(!$entity->canUser('publishRegistrations')) echo 'disabled hltip'; ?>" <?php if(!$entity->canUser('publishRegistrations')) echo 'title="Você só pode publicar a lista de aprovados após o término do período de inscrições."'; ?> href="<?php echo $app->createUrl('project', 'publish', [$entity->id]) ?>">Publicar lista de aprovados</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <!--#inscritos-->
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="sidebar project sidebar-right">
    <div class="setinha"></div>
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">Para adicionar arquivos para download ou links, primeiro é preciso salvar o projeto.</div>
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
            <a class="botao adicionar staging-hidden" href="<?php echo $app->createUrl('project','create', array('parentId' => $entity->id)) ?>">adicionar sub-projeto</a>
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

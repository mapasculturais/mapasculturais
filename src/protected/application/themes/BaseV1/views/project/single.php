<?php
use MapasCulturais\Entities\ProjectAgentRelation as Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);
$registrationForm = $entity->getFile('registrationForm');

$this->bodyProperties['ng-app'] = "Entity";

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('tag');


    $app->hook('mapasculturais.scripts', function() use ($app, $entity){

        $ids = array_map(function($e){

            return $e->agent->id;
        }, $entity->registrations);
        ?>
        <script type="text/javascript">
            MapasCulturais.agentRelationDisabledCD = ['<?php echo $app->txt('project registration')?>'];
        </script>
        <?php
    });
}
$this->includeAngularEntityAssets($entity);
$this->enqueueScript('app', 'ng-project', 'js/Project.js', array('entity'));

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
            <?php if($avatar = $entity->avatar): ?>
                <div class="avatar com-imagem">
                    <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
                <?php else: ?>
                    <div class="avatar">
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
                   data-allow-clear="1",
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
        <li><a href="#inscritos">Inscritos</a></li>
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
                    <p><span class="label">Site:</span>
                    <?php if($this->isEditable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
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
            <?php if($this->isEditable()): ?>
                <p>
                    Utilize este espaço caso queira abrir inscrições para este projeto para Agentes Culturais cadastrados na plataforma.
                </p>
            <?php endif; ?>
            <p>
                <?php if($this->isEditable()): ?><span class="label">1. Selecione o período em que as inscrições ficarão abertas:</span> <br/><?php endif; ?>
                <?php if($this->isEditable() || $entity->registrationFrom): ?>As inscrições estão abertas de <span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong></span><?php endif; ?>
                <?php if($this->isEditable() || ($entity->registrationFrom && $entity->registrationTo)) echo ' a '; ?>
                <?php if($this->isEditable() || $entity->registrationTo): ?><span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong></span><?php endif; ?>.
            </p>
        <?php endif; ?>

        <?php if($entity->introInscricoes || $this->isEditable()): ?>
        <div id="intro-das-inscricoes">
            <?php if($this->isEditable()): ?><span class="label">2. Texto introdutório:</span> <br/> <?php endif; ?>
            <p class="js-editable" data-edit="introInscricoes" data-original-title="Texto introdutório da inscrição" data-emptytext="Insira um texto de introdução para as inscrições" data-placeholder="Insira um texto de introdução para as inscrições" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->introInscricoes; ?></p>
        </div>
        <?php endif; ?>
                <div>
            <?php if ($this->isEditable()): ?>
                <p>
                    <span class="label">3. Suba uma ficha de inscrição:</span> <br/>
                    Isto é opcional. Você pode anexar uma ficha de inscrição. Os candidatos farão download dessa ficha, para que possam preencher e anexar ao fazer a inscrição para o seu projeto.
                </p>
            <?php endif; ?>
            <p class="js-ficha-inscricao">
                <?php if($registrationForm): ?>
                    <a href="<?php echo $registrationForm->url?>" class="botao principal"><span class="icone icon_download"></span>Baixar a ficha de inscrição</a>
                    <?php if($this->isEditable()): ?>
                        <a class='botao excluir simples js-remove-item' data-href='<?php echo $registrationForm->deleteUrl ?>' data-target=".js-ficha-inscricao>*" data-remove-callback="$('#upload-registration-button').removeClass('oculto');" data-confirm-message="Excluir a ficha de inscrição?">Excluir a ficha de inscrição</a>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if($this->controller->action == 'edit'): ?>
            <p id="upload-registration-button" <?php if($registrationForm): ?>class="oculto"<?php endif; ?>>
                <a class="botao adicionar simples js-open-editbox" data-target="#editbox-upload-registration-form">Subir uma ficha de inscrição</a>
            </p>
            <div id="editbox-upload-registration-form" class="js-editbox mc-right" title="Subir ficha de inscrição" data-success-callback="$('#upload-registration-button').addClass('oculto');">
                <?php $this->ajaxUploader ($entity, 'registrationForm', 'set-content', '.js-ficha-inscricao',''
                        . '<a href="{{url}}" class="botao principal"><span class="icone icon_download"></span>Baixar a ficha de inscrição</a> '
                        . '<a class="botao excluir simples js-remove-item" data-href="{{deleteUrl}}" data-target=".js-ficha-inscricao>*" data-remove-callback="$(\'#upload-registration-button\').removeClass(\'oculto\');" data-confirm-message="Excluir a ficha de inscrição?">Excluir a ficha de inscrição</a>','',false,'.doc, .xls, .pdf'); ?>
            </div>
        <?php endif; ?>
        <?php if($app->auth->isUserAuthenticated() && $entity->isRegistrationOpen() && !$this->isEditable()): ?>
            <form id="project-registration">
                <div>
                    <a id="select-registration-owner-button" class="botao" ng-click="editbox.open('editbox-select-registration-owner', $event)">{{data.registration.owner ? data.registration.owner.name : 'Selecione o agente responsável'}}</a>
                    <edit-box id="editbox-select-registration-owner" position="bottom" title="Selecione o agente responsável pela inscrição." cancel-label="Cancelar" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                        <find-entity id='find-entity-registration-owner' entity="agent" no-results-text="Nenhum agente encontrado" select="setRegistrationOwner" api-query='data.apiQueryRegistrationAgent' spinner-condition="data.registrationSpinner"></find-entity>
                    </edit-box>
                </div>
                <div>
                    <mc-select placeholder="Selecione a categoria" model="data.register.category" data="data.registrationCategories"></mc-select>
                </div>
                <div>
                    <button class="botao principal" ng-click="register()">inscrever-se</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <!--#inscricoes-->
    <div id="inscritos" class="aba-content">
        <?php if($entity->canUser('approveRegistration')): ?>
            <div class="privado">
                <div class="clearfix">
                    <h3 class="alignleft"><span class="icone icon_lock"></span>Inscritos</h3>
                    <a class="alignright botao download" href="#"><span class="icone icon_download"></span>Baixar lista de inscritos</a>
                </div>
                <table class="js-registration-list registrations-list">
                    <thead>
                        <tr>
                            <th class="registration-id">
                                Nº
                            </th>
                            <th class="registration-agents">
                                Agentes
                            </th>
                            <th class="registration-attachments">
                                Anexos
                            </th>
                            <th class="registration-status">
                                <div class="dropdown">
                                    <div class="placeholder">Status</div>
                                    <div class="submenu-dropdown">
                                        <ul>
                                            <li>Aprovado</li>
                                            <li>Suplente</li>
                                            <li>Rejeitado</li>
                                        </ul>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($entity->registrations as $registration): ?>
                        <tr id="registration-<?php echo $registration->id ?>" data-registration-id="<?php echo $registration->id ?>" class="
                        <?php if ($registration->status == Registration::STATUS_ENABLED)
                            {
                            echo 'approved';
                            }
                            else if ($registration->status == Registration::STATUS_REGISTRATION_REJECTED)
                            {
                            echo 'rejected';
                            }
                            else if ($registration->status == Registration::STATUS_MAYBE)
                            {
                            echo 'maybed';
                            }
                        ?>">
                            <td class="registration-id">
                                <?php echo $registration->id ?>
                            </td>
                            <td class="registration-agents">
                                <p>
                                    <span class="label">Responsável</span><br />
                                    <a href="<?php echo $registration->agent->singleUrl ?>"><?php echo $registration->agent->name ?></a>
                                </p>
                                <p>
                                    <span class="label">Instituição</span><br />
                                    <a href="#">Nome da Instituição</a>
                                </p>
                                <p>
                                    <span class="label">Coletivo</span><br />
                                    <a href="#">Nome do Coletivo</a>
                                </p>
                            </td>
                            <td class="registration-attachments">
                                <ul>
                                    <li><?php if($form = $registration->getFile('registrationForm')): ?><a href="<?php echo $form->url ?>">Anexo 1</a><?php endif; ?></li>
                                </ul>
                            </td>
                            <td class="registration-status">
                                <span class="js-registration-action approve hltip <?php if($registration->status == Registration::STATUS_ENABLED) echo 'selected' ?>" data-agent-id="<?php echo $registration->agent->id ?>" data-href="<?php echo $app->createUrl('project', 'approveRegistration', array($entity->id)) ?>" title="Aprovar"></span>
                                <span class="js-registration-action maybe hltip" title="Talvez"></span>
                                <span class="js-registration-action reject hltip <?php if($registration->status == Registration::STATUS_REGISTRATION_REJECTED) echo 'selected' ?>" data-agent-id="<?php echo $registration->agent->id ?>" data-href="<?php echo $app->createUrl('project', 'rejectRegistration', array($entity->id)) ?>" title="Rejeitar"></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="clearfix">
                    <a class="alignright botao principal" href="#">Publicar lista de aprovados</a>
                </div>
            </div>
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
            <ul class="js-slimScroll">
                <?php foreach($entity->children as $space): ?>
                <li><a href="<?php echo $space->singleUrl; ?>"><?php echo $space->name; ?></a></li>
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

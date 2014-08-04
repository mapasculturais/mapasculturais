<?php
use MapasCulturais\Entities\ProjectAgentRelation as Registration;

$action = preg_replace("#^(\w+/)#", "", $this->template);
$registrationForm = $entity->getFile('registrationForm');

$this->bodyProperties['ng-app'] = "Entity";

if(is_editable()){
    add_entity_types_to_js($entity);
    add_taxonoy_terms_to_js('tag');

    $app->enqueueScript('vendor', 'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.js', array('jquery'));
    //$app->enqueueStyle('vendor',  'jquery-ui-datepicker', '/vendor/jquery-ui.datepicker.min.css');
    
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

add_agent_relations_to_js($entity);
add_angular_entity_assets($entity);

add_entity_properties_metadata_to_js($entity);
?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<div class="barra-esquerda barra-lateral projeto">
    <div class="setinha"></div>
    <?php $this->part('verified', array('entity' => $entity)); ?>
    <?php $this->part('redes-sociais', array('entity'=>$entity)); ?>
</div>

<article class="main-content projeto">
    <header class="main-content-header">
        <div
            <?php if($header = $entity->getFile('header')): ?>
                 style="background-image: url(<?php echo $header->transform('header')->url; ?>);" class="imagem-do-header com-imagem js-imagem-do-header"
                 <?php else: ?>
                 class="imagem-do-header js-imagem-do-header"
            <?php endif; ?>
        >
            <?php if(is_editable()): ?>
                <a class="botao editar js-open-editbox" data-target="#editbox-change-header" href="#">editar</a>
                <div id="editbox-change-header" class="js-editbox mc-bottom" title="Editar Imagem da Capa">
                    <?php add_ajax_uploader ($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
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
                        <img class="js-avatar-img" src="<?php echo $app->assetUrl ?>/img/avatar--project.png" />
            <?php endif; ?>
                <?php if(is_editable()): ?>
                    <a class="botao editar js-open-editbox" data-target="#editbox-change-avatar" href="#">editar</a>
                    <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
                        <?php add_ajax_uploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                    </div>
                <?php endif; ?>
            </div>
            <!--.avatar-->

            <?php if(is_editable() && $entity->canUser('modifyParent')): ?>
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
                <h4><a href="<?php echo $entity->parent->singleUrl; ?>"><?php echo $entity->parent->name; ?></a></h4>
            <?php endif; ?>

            <h2><span class="js-editable" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
            <div class="objeto-meta">
                <div>
                    <span class="label">Tipo: </span>
                    <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Selecione um tipo" data-entity='project' data-value='<?php echo $entity->type ?>'><?php echo $entity->type? $entity->type->name : ''; ?></a>
                </div>
                <div>
                    <?php if(is_editable() || !empty($entity->terms['tag'])): ?>
                        <span class="label">Tags: </span>
                        <?php if(is_editable()): ?>
                            <span class="js-editable-taxonomy" data-original-title="Tags" data-emptytext="Insira tags" data-taxonomy="tag"><?php echo implode('; ', $entity->terms['tag'])?></span>
                        <?php else: ?>
                            <?php foreach($entity->terms['tag'] as $i => $term): if($i) echo '; '; ?>
                                <a href="<?php echo $app->createUrl('site', 'search')?>#taxonomies[tags][]=<?php echo $term ?>"><?php echo $term ?></a>
                            <?php endforeach; ?>
                        <?php endif;?>
                    <?php endif;?>
                </div>
            </div>
            <!--.objeto-meta-->
        </div>
    </header>

    <ul class="abas clearfix">
        <li class="active"><a href="#sobre">Sobre</a></li>
        <li><a href="#agenda">Agenda</a></li>
        <li><a href="#inscricoes">Inscrições</a></li>
    </ul>
    <div id="sobre" class="aba-content">
        <div class="ficha-spcultura">
            <p>
                <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="700"></textarea>'><?php echo $entity->shortDescription; ?></span>
            </p>
            <div class="servico">
                <?php if(is_editable() || $entity->site): ?>
                    <p><span class="label">Site:</span>
                    <?php if(is_editable()): ?>
                        <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( is_editable() || $entity->longDescription ): ?>
            <h3>Descrição</h3>
            <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Projeto" data-emptytext="Insira uma descrição do projeto" ><?php echo $entity->longDescription; ?></span>
        <?php endif; ?>


        <!-- Video Gallery BEGIN -->
        <?php $app->view->part('parts/video-gallery.php', array('entity'=>$entity)); ?>
        <!-- Video Gallery END -->

        <!-- Image Gallery BEGIN -->
        <?php $app->view->part('parts/gallery.php', array('entity'=>$entity)); ?>
        <!-- Image Gallery END -->
    </div>
    <!-- #sobre -->
    <div id="agenda" class="aba-content lista">
        <?php $this->part('parts/agenda', array('entity' => $entity)); ?>
    </div>
    <!-- #agenda -->

    <div id="inscricoes" class="aba-content">
        <?php if(is_editable() || $entity->registrationFrom || $entity->registrationTo): ?>
            <?php if(is_editable()): ?>
                <p>
                    Utilize este espaço caso queira abrir inscrições para este projeto para Agentes Culturais cadastrados na plataforma.
                </p>
            <?php endif; ?>
            <p>
                <?php if(is_editable()): ?><span class="label">1. Selecione o período em que as inscrições ficarão abertas:</span> <br/><?php endif; ?>
                <?php if(is_editable() || $entity->registrationFrom): ?>As inscrições estão abertas de <span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong></span><?php endif; ?>
                <?php if(is_editable() || ($entity->registrationFrom && $entity->registrationTo)) echo ' a '; ?>
                <?php if(is_editable() || $entity->registrationTo): ?><span class="js-editable" data-type="date" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-showbuttons="false" data-original-title=""><strong><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong></span><?php endif; ?>.
            </p>
        <?php endif; ?>

        <?php if($entity->introInscricoes || is_editable()): ?>
        <div id="intro-das-inscricoes">
            <?php if(is_editable()): ?><span class="label">2. Texto introdutório:</span> <br/> <?php endif; ?>
            <p class="js-editable" data-edit="introInscricoes" data-original-title="Texto introdutório da inscrição" data-emptytext="Insira um texto de introdução para as inscrições" data-placeholder="Insira um texto de introdução para as inscrições" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->introInscricoes; ?></p>
        </div>
        <?php endif; ?>

        <div>
            <?php if (is_editable()): ?>
                <p>
                    <span class="label">3. Suba uma ficha de inscrição:</span> <br/>
                    Isto é opcional. Você pode anexar uma ficha de inscrição. Os candidatos farão download dessa ficha, para que possam preencher e anexar ao fazer a inscrição para o seu projeto.
                </p>
            <?php endif; ?>
            <p class="js-ficha-inscricao">
                <?php if($registrationForm): ?>
                    <a href="<?php echo $registrationForm->url?>" class="botao principal"><span class="icone icon_download"></span>Baixar a ficha de inscrição</a>
                    <?php if(is_editable()): ?>
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
                <?php add_ajax_uploader ($entity, 'registrationForm', 'set-content', '.js-ficha-inscricao',''
                        . '<a href="{{url}}" class="botao principal"><span class="icone icon_download"></span>Baixar a ficha de inscrição</a> '
                        . '<a class="botao excluir simples js-remove-item" data-href="{{deleteUrl}}" data-target=".js-ficha-inscricao>*" data-remove-callback="$(\'#upload-registration-button\').removeClass(\'oculto\');" data-confirm-message="Excluir a ficha de inscrição?">Excluir a ficha de inscrição</a>','',false,'.doc, .xls, .pdf'); ?>
            </div>
        <?php endif; ?>
        <?php if($app->auth->isUserAuthenticated() && $entity->isRegistrationOpen() && !is_editable()): ?>
            <p><a class="botao principal js-open-dialog" data-dialog="#dialog-registration-form" href="#">Fazer inscrição</a></p>

            <div id="dialog-registration-form" class="js-dialog" title="Inscrição">
                <form class="js-ajax-upload" method="POST" data-action="project-registration" action="<?php echo $app->createUrl('project', 'register', array($entity->id)); ?>" enctype="multipart/form-data">
                    <div class="mensagem erro"></div>
                <h4 class="js-search js-xedit"
                           data-field-name='agentId'
                           data-emptytext="Selecione um agente"
                           data-search-box-width="400px"
                           data-search-box-placeholder="Selecione um agente"
                           data-entity-controller="agent"
                           data-search-result-template="#agent-search-result-template"
                           data-selection-template="#agent-response-template"
                           data-no-result-template="#agent-response-no-results-template"
                           data-selection-format="projectRegistration"
                           data-value="<?php echo $app->user->profile->id ?>"
                           title="Repassar propriedade"
                     ><?php echo $app->user->profile->name ?></h4>
                <?php if($registrationForm): ?>
                <p>Selecione o arquivo com sua ficha de inscrição preenchida e clique em "Enviar Inscrição"</p>
                <input type="file" name="registrationForm" />
                <?php endif; ?>
                <input id="registration-agent-id" name='agentId' type="hidden" value="<?php echo $app->user->profile->id ?>" />
                <input type="submit" value="Enviar Inscrição" />
                </form>
            </div>
        <?php endif; ?>

        <?php if($entity->canUser('approveRegistration')): ?>
        <div id="inscritos" class="privado lista-sem-thumb">
            <div class="clearfix">
                <h3 class="alignleft"><span class="icone icon_lock"></span>Inscritos</h3>
                <a class="alignright botao download" href="#"><span class="icone icon_download"></span>Baixar a Lista de Inscritos</a>
            </div>
            <div class="js-registration-list">
                <?php foreach($entity->registrations as $registration): ?>
                <article id="registration-<?php echo $registration->id ?>" data-registration-id="<?php echo $registration->id ?>" class="objeto evento clearfix">
                    <h1><a href="<?php echo $registration->agent->singleUrl ?>"><?php echo $registration->agent->name ?></a></h1>
                    <div class="objeto-meta">
                        <div><span class="label">Área de atuação:</span> <?php echo implode(',', $registration->agent->terms['area']) ?></div>
                        <div><span class="label">Tipo:</span> <?php echo $registration->agent->type->name ?></div>
                    </div>
                    <div>
                        <a href="#" class="js-registration-action action botao <?php if($registration->status == Registration::STATUS_ENABLED) echo 'selected' ?>" data-agent-id="<?php echo $registration->agent->id ?>" data-href="<?php echo $app->createUrl('project', 'approveRegistration', array($entity->id)) ?>">aprovar</a>
                        <a href="#" class="js-registration-action action botao <?php if($registration->status == Registration::STATUS_REGISTRATION_REJECTED) echo 'selected' ?>" data-agent-id="<?php echo $registration->agent->id ?>" data-href="<?php echo $app->createUrl('project', 'rejectRegistration', array($entity->id)) ?>">rejeitar</a>
                        <?php if($form = $registration->getFile('registrationForm')): ?><a class="action" href="<?php echo $form->url ?>">baixar ficha</a><?php endif; ?>
                    </div>
                </article>
                <!--.objeto-->
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <!--#inscricoes-->

    <?php $this->part('parts/owner', array('entity' => $entity, 'owner' => $entity->owner)) ?>
</article>
<div class="barra-lateral projeto barra-direita">
    <div class="setinha"></div>
    <?php if($this->controller->action == 'create'): ?>
        <div class="bloco">Para adicionar arquivos para download ou links, primeiro é preciso salvar o projeto.</div>
    <?php endif; ?>
    <!-- Related Agents BEGIN -->
    <?php $app->view->part('parts/related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->
    <?php if($this->controller->action !== 'create'): ?>
        <div class="bloco">
            <?php if($entity->children): ?>
            <h3 class="subtitulo">Sub-projetos</h3>
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
    <?php $app->view->part('parts/downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
    <?php $app->view->part('parts/link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>

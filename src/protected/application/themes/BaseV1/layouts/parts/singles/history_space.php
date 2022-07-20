<?php 
use MapasCulturais\i;
$this->bodyProperties['ng-controller'] = "EntityController";

$entity = $entityRevision;
$action = "single";
$userCanView = $entity->userCanView;

$this->addEntityToJs($entity->entity);
$this->includeAngularEntityAssets($entity->entity);
$this->includeMapAssets();

?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'spaces','home_title' => 'entities: My Spaces']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<div id="editable-entity" class="clearfix sombra editable-entity-single" data-action="single" data-entity="entityRevision" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <div class="controles">
        <a class="btn btn-warning" href="<?php echo $app->createUrl('panel',$entity->controller_id . 's'); ?>"><?php i::_e("Cancelar");?></a>
    </div>
</div>

<article class="main-content agent">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">    
        <?php $this->applyTemplateHook('header-image','before'); ?>

        <div class="header-image js-imagem-do-header"></div>
        
        <?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->applyTemplateHook('entity-status','before'); ?>
        <div class="alert info"><?php printf(i::__("As informações deste registro é histórico gerado em %s."), $this->controller->requestedEntity->createTimestamp->format('d/m/Y á\s H:i:s'));?>
        <br>
        <?php if($entity->status === \MapasCulturais\Entity::STATUS_ENABLED): ?>
            <?php printf(i::__("Este %s está como <b>publicado</b>"), strtolower($entity->entity->entityTypeLabel));?>
        <?php elseif($entity->status === \MapasCulturais\Entity::STATUS_DRAFT): ?>
            <?php printf(i::__("Este %s é um <b>rascunho</b>"), strtolower($entity->entity->entityTypeLabel));?>
        <?php elseif($entity->status === \MapasCulturais\Entity::STATUS_TRASH): ?>
            <?php printf(i::__("Este %s está na <b>lixeira</b>"), strtolower($entity->entity->entityTypeLabel));?>
        <?php elseif($entity->status === \MapasCulturais\Entity::STATUS_ARCHIVED): ?>
            <?php printf(i::__("Este %s está <b>arquivado</b>"), strtolower($entity->entity->entityTypeLabel));?>
        <?php endif; ?>, <?php printf(i::__("e pode ser acessado clicando <a href=\"%s\" rel='noopener noreferrer'>aqui</a>"), $entity->entity->singleUrl); ?>
        </div>
        <?php $this->applyTemplateHook('entity-status','after'); ?>

        <?php $this->applyTemplateHook('header-content','before'); ?>
        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->applyTemplateHook('avatar','before'); ?>
            <div class="avatar">
               <img class="js-avatar-img" src="'img/avatar--agent.png'" />
            </div>
            <!--.avatar-->
            <?php $this->applyTemplateHook('avatar','after'); ?>

            <?php $this->applyTemplateHook('type','before'); ?>
            <div class="entity-type <?php echo $entity->controller_id ?>-type">
                <div class="icon icon-<?php echo $entity->controller_id ?>"></div>
                <a href="#" class='' data-original-title="<?php i::esc_attr_e("Tipo");?>" data-emptytext="<?php i::esc_attr_e("Selecione um tipo");?>" data-entity='<?php echo $entity->controller_id ?>' data-value='<?php echo $entity->_type ?>'>
                    <?php echo $app->getRegisteredEntityTypeById($entity->entityClassName,$entity->_type)->name; ?>
                </a>
            </div>
            <!--.entity-type-->
            <?php $this->applyTemplateHook('type','after'); ?>
            
            <?php if(isset($entity->parent)): ?>
                <h4 class="entity-parent-title"><a href="<?php echo $app->createUrl('entityRevision','history',[$entity->parent->revision]); ?>"><?php echo $entity->parent->name; ?></a></h4>
            <?php endif; ?>

            <?php $this->applyTemplateHook('name','before'); ?>
            <h2><span class="" data-edit="name" data-original-title="<?php i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php i::esc_attr_e("Nome de exibição");?>"><?php echo $entity->name; ?></span></h2>
            <?php $this->applyTemplateHook('name','after'); ?>

            <?php $this->applyTemplateHook('header-content','end'); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <?php $this->part('tab', ['id' => 'sobre', 'label' => i::__("Sobre"), 'active' => true]) ?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="ficha-spcultura">
                <p>
                    sss
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>

                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

                    <?php if(isset($entity->acessibilidade)): ?>
                        <p><span class="label"><?php i::_e("Acessibilidade");?>: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="Acessibilidade"><?php echo $entity->acessibilidade; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->acessibilidade_fisica)): ?>
                        <p>
                            <span class="label"><?php i::_e("Acessibilidade física");?>: </span><span class="js-editable" data-edit="acessibilidade_fisica" data-original-title="Acessibilidade Física" data-emptytext="Especifique a acessibilidade física <?php $this->dict('entities: of the space') ?>"><?php echo $entity->acessibilidade_fisica; ?></span>
                        </p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('acessibilidade','after'); ?>

                    <?php if(isset($entity->capacidade)): ?>
                        <p>
                            <span class="label"><?php i::_e("Capacidade");?>: </span><span class="js-editable" data-edit="capacidade" data-original-title="Capacidade" data-emptytext="Especifique a capacidade <?php $this->dict('entities: of the space') ?>"><?php echo $entity->capacidade; ?></span>
                        </p>
                    <?php endif; ?>

                    <?php if(isset($entity->horario)): ?>
                        <p>
                            <span class="label"><?php i::_e("Horário de funcionamento");?>: </span><span class="js-editable" data-edit="horario" data-original-title="Horário de Funcionamento" data-emptytext="Insira o horário de abertura e fechamento"><?php echo $entity->horario; ?></span>
                        </p>
                    <?php endif; ?>

                    <?php if(isset($entity->site)): ?>
                        <p><span class="label"><?php i::_e("Site");?>:</span>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>

                    <?php if(isset($entity->emailPrivado) && $userCanView): ?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php i::_e("Email Privado");?>:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="<?php i::esc_attr_e("Email Privado");?>" data-emptytext="<?php i::esc_attr_e("Insira um email que não será exibido publicamente");?>"><?php echo $entity->emailPrivado; ?></span></p>
                    <?php endif;?>

                    <?php if(isset($entity->emailPublico)): ?>
                        <p><span class="label"><?php i::_e("Email");?>:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="<?php i::esc_attr_e("Email Público");?>" data-emptytext="<?php i::esc_attr_e("Insira um email que será exibido publicamente");?>"><?php echo $entity->emailPublico; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->telefonePublico)): ?>
                        <p><span class="label"><?php i::_e("Telefone Público");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="<?php i::esc_attr_e("Telefone Público");?>" data-emptytext="<?php i::esc_attr_e("Insira um telefone que será exibido publicamente");?>"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->telefone1) && $userCanView): ?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php i::_e("Telefone 1");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="<?php i::esc_attr_e("Telefone Privado");?>" data-emptytext="<?php i::esc_attr_e("Insira um telefone que não será exibido publicamente");?>"><?php echo $entity->telefone1; ?></span></p>
                    <?php endif;?>

                    <?php if(isset($entity->telefone2) && $userCanView): ?>
                        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php i::_e("Telefone 2");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="<?php i::esc_attr_e("Telefone Privado");?>" data-emptytext="<?php i::esc_attr_e("Insira um telefone que não será exibido publicamente");?>"><?php echo $entity->telefone2; ?></span></p>
                    <?php endif; ?>
                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php $this->applyTemplateHook('location','before'); ?>
                <input type="hidden" class="latitude" id="latitude" name="latitude" value="<?php echo $entity->location->latitude;?>">
                <input type="hidden" class="longitude" id="longitude" name="longitude" value="<?php echo $entity->location->longitude;?>">
                <div id="space-map" style="width:100%; height:500px"></div>
                <div class="servico clearfix">
                    <!--.mapa-->
                    <div class="infos">
                        <input type="hidden" class="js-editable" id="endereco" data-edit="endereco" data-original-title="<?php i::esc_attr_e("Endereço");?>" data-emptytext="<?php i::esc_attr_e("Insira o endereço");?>" data-showButtons="bottom" value="<?php echo $entity->endereco ?>" data-value="<?php echo $entity->endereco ?>">
                        <p class="endereco"><span class="label"><?php i::_e("Endereço");?>:</span> <span class="js-endereco"><?php echo $entity->endereco ?></span></p>
                        <p><span class="label"><?php i::_e("CEP");?>:</span> <span class="js-editable js-mask-cep" id="En_CEP" data-edit="En_CEP" data-original-title="<?php i::esc_attr_e("CEP");?>" data-emptytext="<?php i::esc_attr_e("Insira o CEP");?>" data-showButtons="bottom"><?php echo $entity->En_CEP ?></span></p>
                        <p><span class="label"><?php i::_e("Logradouro");?>:</span> <span class="js-editable" id="En_Nome_Logradouro" data-edit="En_Nome_Logradouro" data-original-title="<?php i::esc_attr_e("Logradouro");?>" data-emptytext="<?php i::esc_attr_e("Insira o logradouro");?>" data-showButtons="bottom"><?php echo $entity->En_Nome_Logradouro ?></span></p>
                        <p><span class="label"><?php i::_e("Número");?>:</span> <span class="js-editable" id="En_Num" data-edit="En_Num" data-original-title="<?php i::esc_attr_e("Número");?>" data-emptytext="<?php i::esc_attr_e("Insira o Número");?>" data-showButtons="bottom"><?php echo $entity->En_Num ?></span></p>
                        <p><span class="label"><?php i::_e("Complemento");?>:</span> <span class="js-editable" id="En_Complemento" data-edit="En_Complemento" data-original-title="<?php i::esc_attr_e("Complemento");?>" data-emptytext="<?php i::esc_attr_e("Insira um complemento");?>" data-showButtons="bottom"><?php echo $entity->En_Complemento ?></span></p>
                        <p><span class="label"><?php i::_e("Bairro");?>:</span> <span class="js-editable" id="En_Bairro" data-edit="En_Bairro" data-original-title="<?php i::esc_attr_e("Bairro");?>" data-emptytext="<?php i::esc_attr_e("Insira o Bairro");?>" data-showButtons="bottom"><?php echo $entity->En_Bairro ?></span></p>
                        <p><span class="label"><?php i::_e("Município");?>:</span> <span class="js-editable" id="En_Municipio" data-edit="En_Municipio" data-original-title="<?php i::esc_attr_e("Município");?>" data-emptytext="<?php i::esc_attr_e("Insira o Município");?>" data-showButtons="bottom"><?php echo $entity->En_Municipio ?></span></p>
                        <p><span class="label"><?php i::_e("Estado");?>:</span> <span class="js-editable" id="En_Estado" data-edit="En_Estado" data-original-title="<?php i::esc_attr_e("Estado");?>" data-emptytext="<?php i::esc_attr_e("Insira o Estado");?>" data-showButtons="bottom"><?php echo $entity->En_Estado ?></span></p>
                    </div>
                    <!--.infos-->
                </div>
                <!--.servico-->
                <?php $this->applyTemplateHook('location','after'); ?>
            </div>
            <!--.ficha-spcultura-->

            <?php $this->applyTemplateHook('tab-about-extra-info','before'); ?>
            <?php if ( isset($entity->longDescription) ): ?>
                <h3><?php i::_e("Descrição");?></h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php $this->dict('entities: Description of the space') ?>" data-emptytext="<?php $this->dict('entities: Description of the space') ?>" ><?php echo nl2br($entity->longDescription); ?></span>
            <?php endif; ?>

            <?php if ( isset($entity->criterios) ): ?>
                <h3><?php $this->dict('entities: Usage criteria of the space') ?></h3>
                <div class="descricao js-editable" data-edit="criterios" data-original-title="<?php $this->dict('entities: Usage criteria of the space') ?>" data-emptytext="<?php $this->dict('entities: Usage criteria of the space') ?>" data-placeholder="<?php $this->dict('entities: Usage criteria of the space') ?>" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
            <?php endif; ?>
            <?php $this->applyTemplateHook('tab-about-extra-info','after'); ?>

            <!-- Video Gallery BEGIN -->
            <?php if (isset($entity->videos)): ?>
                <h3><?php i::_e("Vídeos");?></h3>
                <a name="video" rel='noopener noreferrer'></a>
                <div id="video-player" class="video" ng-non-bindable>
                    <iframe id="video_display" width="100%" height="100%" src="" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                </div>
                <ul class="clearfix js-videogallery" ng-non-bindable>
                    <?php foreach($entity->videos as $video): ?>
                        <li id="video-<?php echo $video->id ?>">
                            <a class="js-metalist-item-display" data-videolink="<?php echo $video->value;?>" title="<?php echo $video->title;?>">
                                <img src="<?php $this->asset('img/spinner_192.gif'); ?>" alt="" class="thumbnail_med_wide"/>
                                <h1 class="title"><?php echo $video->title;?></h1>
                            </a>
                        </li>
                    <?php endforeach;?>
                </ul>
            <?php endif;?>
            <!-- Video Gallery END -->

            <?php $this->applyTemplateHook('tab-about','end'); ?>
        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after');?>

    <footer id='entity-owner' class="owner clearfix js-owner" ng-controller="ChangeOwnerController">
        <img src="" class="avatar js-owner-avatar" />
        <p class="small bottom"><?php i::_e("Publicado por");?></p>

        <h6 class='js-owner-name'><a href="<?php echo $app->createUrl('entityRevision', 'history', [$entity->owner->revision]);?>"><?php echo $entity->owner->name ?></a></h6>

        <p class="owner-description js-owner-description"><?php echo nl2br($entity->owner->shortDescription); ?></p>
    </footer>

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar agent">
    <!-- Related Seals BEGIN -->
    <?php if(isset($entity->_seals)):?>
        <div class="selos-add">
            <div class="widget">
                <h3 text-align="left" vertical-align="bottom"><?php i::_e("Selos Aplicados");?> 
                <div class="selos clearfix">
                <?php foreach($entity->_seals as $seal):?>
                    <div class="avatar-seal">
                        <a href="" rel='noopener noreferrer'>
                            <img src="<?php $this->asset('img/avatar--agent.png'); ?>">
                        </a>
                        <div class="descricao-do-selo">
                            <h1><a href="<?php echo $app->createUrl('seal','single',[$seal->id]);?>"><?php echo $seal->name;?></a></h1>
                        </div>
                    </div>
                <?php endforeach;?>
                </div>
            </div>
        </div>
    <?php endif;?>
    <!-- Related Seals END -->

    <?php $this->part('singles/space-public', ['entity' => $entity]); ?>

    <?php if(isset($entity->_terms) && isset($entity->_terms->area)):?>
        <div class="widget">
        <h3><?php $this->dict('taxonomies:area: name') ?></h3>
            <?php foreach($entity->_terms->area as $area): ?>
                <a class="tag tag-<?php echo $entity->controller_id ?>">
                    <?php echo $area ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif;?>

    <?php if(isset($entity->_terms) && isset($entity->_terms->tag)): ?>
    <div class="widget">
        <h3><?php i::_e("Tags");?></h3>
        <?php foreach($entity->_terms->tag as $tag): ?>
            <a class="tag tag-<?php echo $entity->controller_id ?>" href="">
                <?php echo $tag; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<div class="sidebar agent sidebar-right">
    <!-- Related Agents BEGIN -->
    <?php if(isset($entity->_agents)):?>
        <div class="agentes-relacionados">
            <?php foreach($entity->_agents as $group => $agents): ?>
            <div class="widget">
                <h3><?php echo $group;?></h3>
                <div class="agentes clearfix">
                    <?php foreach($agents as $agent): ?>
                        <div class="avatar">
                            <a href="" rel='noopener noreferrer'>
                                <img ng-src="" />
                            </a>
                            <div class="descricao-do-agente">
                                <h1><a href="" rel='noopener noreferrer'><?php echo $agent->name;?></a></h1>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    <?php endif;?>
    <!-- Related Agents END -->

    <!-- Spaces BEGIN -->
    <?php if(isset($entities->_spaces)): ?>
        <div class="widget">
            <h3><?php $this->dict('entities: Spaces of the agent'); ?></h3>
            <ul class="widget-list js-slimScroll">
                <?php foreach($entities->_spaces as $space): ?>
                    <li class="widget-list-item"><a href="" rel='noopener noreferrer'><span><?php echo $space->name; ?></span></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- Spaces END -->

    <!-- Link List BEGIN -->
    <?php if (isset($entity->links)): ?>
        <div class="widget">
            <h3><?php i::_e("Links");?></h3>
            <ul class="js-metalist widget-list js-slimScroll">
                <?php foreach($entity->links as $link): ?>
                    <li id="link-<?php echo $link->id ?>" class="widget-list-item" >
                        <a class="js-metalist-item-display" href="<?php echo $link->value;?>"><span><?php echo $link->title;?></span></a>
                    </li>
                <?php endforeach;?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- Link List END -->
</div>

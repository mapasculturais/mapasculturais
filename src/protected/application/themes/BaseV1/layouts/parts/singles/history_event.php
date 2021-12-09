<?php
use MapasCulturais\i;

$this->enqueueScript('app', 'events', '/js/events.js', array('mapasculturais'));

$entity = $entityRevision;
$action = "single";
$app = MapasCulturais\App::i();
?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'events','home_title' => 'entities: My Events']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<div id="editable-entity" class="clearfix sombra editable-entity-single" data-action="single" data-entity="entityRevision" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <div class="controles">
        <a class="btn btn-warning" href="<?php echo $app->createUrl('panel',$entity->controller_id . 's'); ?>"><?php i::_e("Cancelar");?></a>
    </div>
</div>

<article class="main-content event">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">    
        <?php $this->applyTemplateHook('header-image','before'); ?>

        <div class="header-image js-imagem-do-header"></div>
        
        <?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->applyTemplateHook('entity-status','before'); ?>
        <div class="alert info"><?php printf(i::__("As informações deste registro é histórico gerado em %s."), $this->controller->requestedEntity->createTimestamp->format('d/m/Y á\s H:i:s'))?>
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

            <div class="entity-type event-type">
                <div class="icon icon-event"></div>
                <a href="#" rel='noopener noreferrer'><?php i::_e("Evento");?></a>
            </div>
            <!--.entity-type-->
            <?php $this->applyTemplateHook('type','after'); ?>

            <?php $this->applyTemplateHook('name','before'); ?>
            <h2><span class="" data-edit="name" data-original-title="<?php i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php i::esc_attr_e("Nome de exibição");?>"><?php echo $entity->name; ?></span></h2>
            <?php $this->applyTemplateHook('name','after'); ?>

            <?php if (isset($entity->subTitle)): ?>
                <?php $this->applyTemplateHook('subtitle','before'); ?>
                <h4 class="event-subtitle">
                    <span class="js-editable>" data-edit="subTitle" data-original-title="<?php i::esc_attr_e("Subtítulo");?>" data-emptytext="<?php i::esc_attr_e("Insira um subtítulo para o evento");?>" data-tpl='<input tyle="text" maxlength="140"></textarea>'><?php echo $entity->subTitle; ?></span>
                </h4>
                <?php $this->applyTemplateHook('subtitle','after'); ?>
            <?php endif; ?>

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
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo nl2br($entity->shortDescription); ?></span>
                </p>

                <?php $this->applyTemplateHook('tab-about-service','before'); ?>

                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

                    <?php if(isset($entity->registrationInfo)): ?>
                        <p>
                            <span class="label"><?php i::_e("Inscrições");?>:</span><span class="js-editable" data-edit="registrationInfo" data-original-title="<?php i::esc_attr_e("Inscrições");?>" data-emptytext="<?php i::esc_attr_e("Informações sobre as inscrições");?>"><?php echo $entity->registrationInfo; ?></span>
                        </p>
                    <?php endif; ?>

                    <?php if (isset($entity->classificacaoEtaria)): ?>
                        <?php if(!$entity->classificacaoEtaria){
                            $entity->classificacaoEtaria = 'Livre';
                        }
                        ?>
                        <p>
                            <span class="label"><?php i::_e("Classificação Etária");?>: </span><span class="js-editable" data-edit="classificacaoEtaria" data-original-title="<?php i::esc_attr_e("Classificação Etária");?>" data-emptytext="<?php i::esc_attr_e("Informe a classificação etária do evento");?>"><?php echo $entity->classificacaoEtaria; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->site)): ?>
                        <p><span class="label"><?php i::_e("Site");?>:</span>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>

                    <?php if(isset($entity->telefonePublico)): ?>
                        <p><span class="label"><?php i::_e("Telefone Público");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="<?php i::esc_attr_e("Telefone Público");?>" data-emptytext="<?php i::esc_attr_e("Insira um telefone que será exibido publicamente");?>"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->traducaoLibras) || isset($entity->descricaoSonora)): ?>
                        <br>
                        <p>
                            <span><?php i::_e("Acessibilidade");?>:</span>

                            <?php if(isset($entity->traducaoLibras)): ?>
                                <p><span class="label"><?php i::_e("Tradução para LIBRAS");?>: </span><span class="js-editable" data-edit="traducaoLibras" data-original-title="<?php i::esc_attr_e("Tradução para LIBRAS");?>"><?php echo $entity->traducaoLibras; ?></span></p>
                            <?php endif; ?>

                            <?php if(isset($entity->descricaoSonora)): ?>
                                <p><span class="label"><?php i::_e("Áudio Descrição");?>: </span><span class="js-editable" data-edit="descricaoSonora" data-original-title="<?php i::esc_attr_e("Descrição Sonora");?>"><?php echo $entity->descricaoSonora; ?></span></p>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <!--.servico-->
                <div class="servico ocorrencia clearfix">

                    <div class="js-event-occurrence">

                    <?php if(isset($entity->occurrences)):?>
                        <?php foreach($entity->occurrences as $key => $space): ?>
                            <div class="regra clearfix">
                                <header class="clearfix">
                                    <h3 class="alignleft"><a href="<?php echo $app->createUrl("entityRevision","history",[$space->revision])?>"><?php echo $space->name?></a></h3>
                                    <a class="toggle-mapa" href="#" rel='noopener noreferrer'><span class="ver-mapa"><?php i::_e("ver mapa");?></span><span class="ocultar-mapa"><?php i::_e("ocultar mapa");?></span> <span class="icon icon-show-map"></span></a>
                                </header>
                                <div id="occurrence-map-<?php echo $key?>" class="mapa js-map" data-lat="<?php echo $space->location->latitude;?>" data-lng="<?php echo $space->location->longitude;?>"></div>
                                <!-- .mapa -->
                                <?php
                                $occurrencesDescription = "";
                                foreach($space->items as $occurrence) {
                                    if(!empty($occurrence->rule->description)) {
                                        $occurrencesDescription .= trim($occurrence->rule->description);
                                    }
                                    if($occurrence->rule->price) {
                                        $occurrencesDescription .= '. '.$occurrence->rule->price;
                                    }
                                    if(!empty($occurrence->rule->description)) {
                                        $occurrencesDescription .= '; ';
                                    }
                                }
                                $occurrencesDescription = substr($occurrencesDescription,0,-2);
                                ?>
                                <div class="infos">
                                    <p class="descricao-legivel"><?php echo $occurrencesDescription;?></p>
                                    <?php if(is_array($space->items) && count($space->items) == 1 && !empty($space->items[0]->rule->price)): ?>
                                        <p><span class="label"><?php i::_e("Preço");?>:</span> <?php echo $space->items[0]->rule->price?></p>
                                    <?php endif;?>
                                    <p><span class="label"><?php i::_e("Endereço");?>:</span> <?php echo $space->endereco;?></p>
                                </div>
                                <!-- .infos -->
                            </div>
                        <?php endforeach;?>
                        <?php endif;?>
                        </div>
                    </div>
                </div>
            </div>
            <!--.ficha-spcultura-->

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
<div class="sidebar-left sidebar event">
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

    <?php if(isset($entity->project)): ?>
        <div class="widget">
            <h3><?php i::_e("Projeto");?></h3>
            <a class="event-project-link" href="<?php echo $app->createUrl('project','single',[$entity->project->id]); ?>"><?php echo $entity->project->name; ?></a>
        </div>
    <?php endif; ?>

    <?php if(isset($entity->_terms) && isset($entity->_terms->linguagem)):?>
        <div class="widget">
        <h3><?php $this->dict('taxonomies:linguagem: name') ?></h3>
            <?php foreach($entity->_terms->linguagem as $linguagem): ?>
                <a class="tag tag-<?php echo $entity->controller_id ?>">
                    <?php echo $linguagem ?>
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
<div class="sidebar event sidebar-right">
    
    <!-- Opportunities BEGIN -->
    <?php if(isset($entity->_opportunities)): ?>
        <div class="widget">
            <h3><?php $this->dict('entities: Opportunities of the event'); ?></h3>
            <ul class="widget-list js-slimScroll">
                <?php foreach($entities->_opportunities as $opportunity): ?>
                    <li class="widget-list-item"><a href="" rel='noopener noreferrer'><span><?php echo $opportunity->name; ?></span></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- Opportunities END -->
    
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

<?php 
$entity = $entityRevision;
$action = "single";
?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'events','home_title' => 'entities: My Events']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<div id="editable-entity" class="clearfix sombra editable-entity-single" data-action="single" data-entity="entityRevision" data-id="<?php echo $entity->id ?>">
    <?php $this->part('editable-entity-logo') ?>
    <div class="controles">
        <a class="btn btn-warning" href="<?php echo $app->createUrl('panel',$entity->controller_id . 's'); ?>"><?php \MapasCulturais\i::_e("Cancelar");?></a>
    </div>
</div>

<article class="main-content event">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
    <header class="main-content-header">    
        <?php $this->applyTemplateHook('header-image','before'); ?>

        <div class="header-image js-imagem-do-header"></div>
        
        <?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->applyTemplateHook('entity-status','before'); ?>
        <div class="alert info"><?php echo \MapasCulturais\i::__("As informações deste registro é histórico gerado em " .$entity->createTimestamp->format('d/m/Y H:i:s').".");?></div>
        <?php $this->applyTemplateHook('entity-status','after'); ?>

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
                <a href="#"><?php \MapasCulturais\i::_e("Evento");?></a>
            </div>
            <!--.entity-type-->
            <?php $this->applyTemplateHook('type','after'); ?>

            <?php $this->applyTemplateHook('name','before'); ?>
            <h2><span class="" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>"><?php echo $entity->name; ?></span></h2>
            <?php $this->applyTemplateHook('name','after'); ?>

            <?php if (isset($entity->subTitle)): ?>
                <?php $this->applyTemplateHook('subtitle','before'); ?>
                <h4 class="event-subtitle">
                    <span class="js-editable>" data-edit="subTitle" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Subtítulo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um subtítulo para o evento");?>" data-tpl='<input tyle="text" maxlength="140"></textarea>'><?php echo $entity->subTitle; ?></span>
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
        <li class="active"><a href="#sobre"><?php \MapasCulturais\i::_e("Sobre");?></a></li>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="ficha-spcultura">
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>

                <?php $this->applyTemplateHook('tab-about-service','before'); ?>

                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

                    <?php if(isset($entity->registrationInfo)): ?>
                        <p>
                            <span class="label"><?php \MapasCulturais\i::_e("Inscrições");?>:</span><span class="js-editable" data-edit="registrationInfo" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Inscrições");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informações sobre as inscrições");?>"><?php echo $entity->registrationInfo; ?></span>
                        </p>
                    <?php endif; ?>

                    <?php if (isset($entity->classificacaoEtaria)): ?>
                        <?php if(!$entity->classificacaoEtaria){
                            $entity->classificacaoEtaria = 'Livre';
                        }
                        ?>
                        <p>
                            <span class="label"><?php \MapasCulturais\i::_e("Classificação Etária");?>: </span><span class="js-editable" data-edit="classificacaoEtaria" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Classificação Etária");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe a classificação etária do evento");?>"><?php echo $entity->classificacaoEtaria; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->site)): ?>
                        <p><span class="label"><?php \MapasCulturais\i::_e("Site");?>:</span>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>

                    <?php if(isset($entity->telefonePublico)): ?>
                        <p><span class="label"><?php \MapasCulturais\i::_e("Telefone Público");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Telefone Público");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um telefone que será exibido publicamente");?>"><?php echo $entity->telefonePublico; ?></span></p>
                    <?php endif; ?>

                    <?php if(isset($entity->traducaoLibras) || isset($entity->descricaoSonora)): ?>
                        <br>
                        <p>
                            <span><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span>

                            <?php if(isset($entity->traducaoLibras)): ?>
                                <p><span class="label"><?php \MapasCulturais\i::_e("Tradução para LIBRAS");?>: </span><span class="js-editable" data-edit="traducaoLibras" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Tradução para LIBRAS");?>"><?php echo $entity->traducaoLibras; ?></span></p>
                            <?php endif; ?>

                            <?php if(isset($entity->descricaoSonora)): ?>
                                <p><span class="label"><?php \MapasCulturais\i::_e("Áudio Descrição");?>: </span><span class="js-editable" data-edit="descricaoSonora" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Sonora");?>"><?php echo $entity->descricaoSonora; ?></span></p>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

                <?php /*$this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]);*/ ?>

            </div>
            <!--.ficha-spcultura-->

            <?php $this->applyTemplateHook('tab-about-extra-info','before'); ?>
            <?php /*$this->part('singles/space-extra-info', ['entity' => $entity])*/ ?>
            <?php $this->applyTemplateHook('tab-about-extra-info','after'); ?>

            <!-- Video Gallery BEGIN -->
            <?php if (isset($entity->videos)): ?>
                <h3><?php \MapasCulturais\i::_e("Vídeos");?></h3>
                <a name="video"></a>
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

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
<div class="sidebar-left sidebar event">
    <!-- Related Seals BEGIN -->
    <?php if(isset($entity->_seals)):?>
        <div class="sidebar-left sidebar event">
            <div class="selos-add">
            <div class="widget">
                <h3 text-align="left" vertical-align="bottom"><?php \MapasCulturais\i::_e("Selos Aplicados");?> 
                <div class="selos clearfix">
                <?php foreach($entity->_seals as $seal):?>
                    <div class="avatar-seal ng-scope">
                        <a href="<?php echo $app->createUrl('seal','single',[$seal->id]);?>" class="ng-binding">
                            <img ng-src="<?php echo $seal->url;?>">
                        </a>
                        <div class="descricao-do-selo">
                            <h1><a href="<?php echo $app->createUrl('seal','single',[$seal->id]);?>" class="ng-binding"><?php echo $seal->name;?></a></h1>
                        </div>
                    </div>
                <?php endforeach;?>
                </div>
            </div>
        </div>
    <?php endif;?>
    <!-- Related Seals END -->

    <?php /*$this->part('singles/space-public', ['entity' => $entity])*/ ?>

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
        <h3><?php \MapasCulturais\i::_e("Tags");?></h3>
        <?php foreach($entity->_terms->tag as $tag): ?>
            <a class="tag tag-<?php echo $this->controller_id ?>" href="">
                <?php echo $tag; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<div class="sidebar event sidebar-right">
    <!-- Related Agents BEGIN -->
    <?php if(isset($entity->_agents)):?>
        <div class="agentes-relacionados">
            <?php foreach($entity->_agents as $group => $agent): ?>
            <div class="widget">
                <h3><?php echo $group;?></h3>
                <div class="agentes clearfix">
                    <div class="avatar">
                        <a href="">
                            <img ng-src="" />
                        </a>
                        <div class="descricao-do-agente">
                            <h1><a href=""><?php echo $agent->name;?></a></h1>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    <?php endif;?>
    <!-- Related Agents END -->


    <?php /*$this->part('singles/space-children', ['entity' => $entity]);*/ ?>

    <!-- Spaces BEGIN -->
    <?php if(isset($entities->_spaces)): ?>
        <div class="widget">
            <h3><?php $this->dict('entities: Spaces of the agent'); ?></h3>
            <ul class="widget-list js-slimScroll">
                <?php foreach($entities->_spaces as $space): ?>
                    <li class="widget-list-item"><a href=""><span><?php echo $space->name; ?></span></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <!-- Spaces END -->

    <!-- Link List BEGIN -->
    <?php if (isset($entity->links)): ?>
        <div class="widget">
            <h3><?php \MapasCulturais\i::_e("Links");?></h3>
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

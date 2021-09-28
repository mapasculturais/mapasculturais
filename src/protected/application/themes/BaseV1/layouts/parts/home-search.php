<section id="home-intro" class="js-page-menu-item home-entity clearfix">
    <?php $this->applyTemplateHook('home-search','begin'); ?>
    <div class="box">
        <h1><?php echo $app->view->renderMarkdown($this->dict('home: title',false)); ?></h1>
        <p><?php echo $app->view->renderMarkdown($this->dict('home: welcome',false)); ?></p>
        <?php $this->applyTemplateHook('home-search-form','begin'); ?>
        <form id="home-search-form" class="clearfix" ng-non-bindable>
            <?php $this->applyTemplateHook('home-search-form','before'); ?>
            <input tabindex="1" id="campo-de-busca" class="search-field" type="text" name="campo-de-busca" placeholder="<?php \MapasCulturais\i::esc_attr_e("Digite uma palavra-chave");?>"/>
            <div id="home-search-filter" class="dropdown" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}}),{{entity}}:(keyword:'{{keyword}}'))">
                <div class="placeholder"><span class="icon icon-search"></span><?php \MapasCulturais\i::_e("Buscar");?></div>
                <div class="submenu-dropdown">
                    <ul>
                        <?php if($app->isEnabled('events')): ?>
                            <li tabindex="2" id="events-filter"  data-entity="event"><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Eventos");?></li>
                        <?php endif; ?>

                        <?php if($app->isEnabled('agents')): ?>
                            <li tabindex="3" id="agents-filter"  data-entity="agent"><span class="icon icon-agent"></span> <?php \MapasCulturais\i::_e("Agentes");?></li>
                        <?php endif; ?>

                        <?php if($app->isEnabled('spaces')): ?>
                            <li tabindex="4" id="spaces-filter"  data-entity="space"><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></li>
                        <?php endif; ?>

                        <?php if($app->isEnabled('projects')): ?>
                            <li tabindex="5" id="projects-filter" data-entity="project" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}},viewMode:list),{{entity}}:(keyword:'{{keyword}}'))"><span class="icon icon-project"></span> <?php \MapasCulturais\i::_e("Projetos");?></li>
                        <?php endif; ?>

                        <?php if($app->isEnabled('opportunities')): ?>
                            <li tabindex="5" id="opportunities-filter" data-entity="opportunity" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}},viewMode:list),{{entity}}:(keyword:'{{keyword}}'))"><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Oportunidades");?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php $this->applyTemplateHook('home-search-form','end'); ?>
        </form>
        <?php $this->applyTemplateHook('home-search-form','after'); ?>
        <a class="btn btn-accent btn-large" href="<?php echo $app->createUrl('panel') ?>"><?php $this->dict('home: colabore') ?></a>
    </div>
    <div class="view-more"><a class="hltip icon icon-select-arrow" href="#home-events" title="<?php \MapasCulturais\i::esc_attr_e("Saiba mais");?>"></a></div>
    <?php $this->applyTemplateHook('home-search','end'); ?>
</section>


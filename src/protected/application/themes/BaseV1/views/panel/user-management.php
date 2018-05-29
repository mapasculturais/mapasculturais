<?php
  $this->layout = 'panel';
  $first = true;
  $noSubSite = ($app->getCurrentSubsiteId() == 0 || $app->getCurrentSubsiteId() == null);
?>
<div class="panel-list panel-main-content">
  <div class="box">
    <header class="panel-header clearfix">
      <h2
        ><?php \MapasCulturais\i::_e("Gerenciador de usuários"); ?>
      </h2>
    </header>    

    <div id="usuarios" class="user-managerment clearfix">
      <input tabindex="1" id="campo-de-busca" class="search-field" type="text" name="campo-de-busca" placeholder="<?php \MapasCulturais\i::esc_attr_e("Digite uma palavra-chave");?>"/>

      <div id="search-filter" class="dropdown" data-searh-url-template="<?php echo $app->createUrl('site','search'); ?>##(global:(enabled:({{entity}}:!t),filterEntity:{{entity}}),{{entity}}:(keyword:'{{keyword}}'))">
        <div class="placeholder">
          <span class="icon icon-search"></span><?php \MapasCulturais\i::_e("Buscar");?>
        </div>
        <div class="submenu-dropdown">
          <ul>
            <?php if($app->isEnabled('agents')): ?>
            <li tabindex="2" id="agents-filter" data-entity="agent"><span class="icon icon-agent"></span><?php \MapasCulturais\i::_e("Agentes");?></li>
            <?php endif; ?>
            
            <?php if($app->isEnabled('events')): ?>
            <li tabindex="3" id="events-filter"  data-entity="event"><span class="icon icon-event"></span> <?php \MapasCulturais\i::_e("Eventos");?></li>
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
    </div>
</div>
</div>
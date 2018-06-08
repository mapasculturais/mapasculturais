<?php
  use MapasCulturais\App;
  $this->layout = 'panel';
  $first = true;
  $noSubSite = ($app->getCurrentSubsiteId() == 0 || $app->getCurrentSubsiteId() == null);
  
  
  $this->includeMapAssets();
  $this->includeCommonAssets();  
  $this->includeSearchAssets();  
  $this->enqueueScript('app', 'ng.usermanager.app', 'js/ng.user-management.js', array('ng-mapasculturais'));
  
  $this->bodyProperties['ng-app'] = "ng.usermanager.app";
  //$this->bodyProperties['ng-controller'] = "UserManagermentController";
?>

<div class="panel-list panel-main-content" ng-controller="UserManagermentController">
  <div class="box user-managerment">
    <header class="panel-header clearfix">
      <h2
        ><?php \MapasCulturais\i::_e("Gerenciador de usuários"); ?>
      </h2>
    </header>

    <div id="user-managerment-dialog" class="js-dialog entity-modal" title="user">
      <div class="js-dialog-content">
        <div style="float: left;">
          <p>
            <span class="label">email:</span>
            <span class="js-editable editable editable-click editable-empty" data-edit="email" data-original-title="email" data-emptytext="">
              {{user.email}}
            </span>
            <br>
            <span class="label">id:</span> 
            <span class="js-editable editable editable-click editable-empty" data-edit="" data-original-title="id" data-emptytext="">
              {{user.id}}
            </span>
            <br>
            <span class="label">autenticação:</span> 
            <span class="js-editable editable editable-click editable-empty" data-edit="" data-original-title="autenticação" data-emptytext="">
              {{user.authProvider}} <!-- // como pegar pelo ID no registerAuthProvider? -->
            </span>
            <br>
            <span class="label">id autenticação:</span>
            <span class="js-editable editable editable-click editable-empty" data-edit="" data-original-title="id autenticação" data-emptytext="">
              {{user.authUid}}
            </span>
            <br>
            <span class="label">status:</span>
            <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="status" data-emptytext="">
              {{user.status}}
            </span>
            <br>
            <span class="label">último login:</span>
            <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="último login" data-emptytext="">
              {{user.lastLoginTimestamp.date | date:'MM/dd/yyyy'}}
            </span>
            <br>
            <span class="label">data criação:</span>
            <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="data criação" data-emptytext="">
              {{user.createTimestamp.date | date:'MM/dd/yyyy'}}
            </span>
          </p>
        </div>
        
        <div style="float: left;">
          <ul class="abas clearfix clear">
            <li class="active"><a href="#agente"><?php \MapasCulturais\i::_e("Agentes");?></a></li>
            <li><a href="#evento"><?php \MapasCulturais\i::_e("Eventos");?></a></li>
            <li><a href="#espacos"><?php \MapasCulturais\i::_e("Espaços");?></a></li>
            <li><a href="#permissoes"><?php \MapasCulturais\i::_e("Permissões");?></a></li>          
            <li><a href="#atividade"><?php \MapasCulturais\i::_e("Atividades");?></a></li>
          </ul>
        </div>
        <div class="tabs-content">
          <div id="agente" class="aba-content">
            <span ng-show="user.agents.spinnerShow">
              <img src="<?php $this->asset('img/spinner.gif') ?>" />
              <span><?php \MapasCulturais\i::_e("obtendo agentes..."); ?></span>
            </span>
            <div>
            <table>
              <thead>
                <tr>
                  <td>id</td>
                  <td>Nome</td>
                  <td>Subsite</td>                
                </tr>
              </thead>
              <tbody>
                <tr ng-repeat="agent in user.agents.list">
                  <td><a href={{agent.singleUrl}}>{{agent.id}}</a></td>
                  <td>{{agent.name}}</td>
                  <td>{{agent.subsite.name}}</td>
                </tr>
              </tbody>
              </table>
            </div>
          </div>
          <div id="evento" class="aba-content">
            evento
          </div>
        </div>

      </div>
    </div>

    <div class="user-managerment-search clearfix">
      <form id="user-managerment-search-form" class="clearfix" ng-non-bindable>
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
      </form>
    </div>

    <div id="lista" ng-animate="{show:'animate-show', hide:'animate-hide'}">
      <?php $this->part('user-management/list-agent'); ?>
      <?php $this->part('user-management/list-opportunity'); ?>
      <?php $this->part('user-management/list-project'); ?>
      
      <?php $this->part('user-management/list-space'); ?>
      <?php $this->part('user-management/list-event'); ?>
    </div>
    <button ng-click="getUserByAgent(8006)" class="btn-warning"> teste </butoon>
  </div>

</div>
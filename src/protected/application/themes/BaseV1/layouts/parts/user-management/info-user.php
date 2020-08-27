<?php
  use MapasCulturais\Entities\Agent;
  use MapasCulturais\Entities\Space;
  use MapasCulturais\Entities\Event;
  $current_user = $app->user;
  
  $subsitesAdmin = $app->repo('User')->getSubsitesAdminRoles($current_user->id);
  $this->jsObject['subsitesAdmin'] = $subsitesAdmin;  
?>
<div class="user-managerment-infos" ng-init="load(<?php echo $user->id;?>)">  
  <div class="user-info">
    <div style="float:left">
      <span class="label"><?php \MapasCulturais\i::_e("id:"); ?></span> 
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("id"); ?>" data-emptytext="">
        <?php echo $user->id;?>
      </span> <br />
      <span class="label"><?php \MapasCulturais\i::_e("email:"); ?></span>
      <span class="js-editable editable-click editable-empty" data-edit="email" data-original-title="<?php \MapasCulturais\i::esc_attr_e("email"); ?>" data-emptytext="">
        <?php echo $user->email;?> 
      </span> <br />
      <span class="label"><?php \MapasCulturais\i::_e("autenticação:"); ?></span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("autenticação"); ?>" data-emptytext="">
        <?php echo $user->authProvider;?> <!-- // como pegar pelo ID no registerAuthProvider? -->
      </span> <br />
      <span class="label"><?php \MapasCulturais\i::_e("id autenticação:"); ?></span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("id autenticação"); ?>" data-emptytext="">
        <?php echo $user->authUid;?>
      </span> <br />
    </div>

    <div style="float:left">
      <span class="label"><?php \MapasCulturais\i::_e("status:"); ?></span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("status"); ?>" data-emptytext="">
        <?php 
          if ($user->status == 1)
            echo \MapasCulturais\i::_e("Ativo");
          else 
          echo \MapasCulturais\i::_e("Inativo");
        ?>
      </span> <br />
      <span class="label">último login:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("último login"); ?>" data-emptytext="">
      <?php echo $user->lastLoginTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i'));?>
      </span> <br />
      <span class="label"><?php \MapasCulturais\i::_e("data criação:"); ?></span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="<?php \MapasCulturais\i::esc_attr_e("data criação"); ?>" data-emptytext="">
        <?php echo $user->createTimestamp->format(\MapasCulturais\i::__('d/m/Y à\s H:i'));?> 
      </span> <br />
    </div>

    <span class="clearfix clear" />
  </div>
    
  <div>
    <ul class="abas clearfix clear">
      <li class="active"><a href="#agentes" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Agentes");?></a></li>
      <li><a href="#espacos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Espaços");?></a></li>
      <li><a href="#eventos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Eventos");?></a></li>
      <li><a href="#projetos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Projetos");?></a></li>
      <li><a href="#oportunidades" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Oportunidades");?></a></li>
      <li><a href="#permissoes" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Permissões");?></a></li>
      <li><a href="#atividade" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Atividades");?></a></li>
    </ul>
  </div>
    
  <div class="tabs-content">
    
    <div id="agentes" class="aba-content">
      <div class="tab-table">
        <button class="tablinks active" data-entity="agentes" data-tab="agents-ativos">     <?php \MapasCulturais\i::_e("Ativos");?>      (<?php echo count($user->enabledAgents);?>)   </button>
        <button class="tablinks"        data-entity="agentes" data-tab="agents-concedidos"> <?php \MapasCulturais\i::_e("Concedidos");?>  (<?php echo count($user->hasControlAgents);?>)</button>
        <button class="tablinks"        data-entity="agentes" data-tab="agents-rascunhos">  <?php \MapasCulturais\i::_e("Rascunhos");?>   (<?php echo count($user->draftAgents);?>)     </button>
        <button class="tablinks"        data-entity="agentes" data-tab="agents-lixeira">    <?php \MapasCulturais\i::_e("Lixeira");?>     (<?php echo count($user->trashedAgents);?>)   </button>
        <button class="tablinks"        data-entity="agentes" data-tab="agents-arquivo">    <?php \MapasCulturais\i::_e("Arquivo");?>     (<?php echo count($app->user->archivedAgents);?>)</button>
      </div>
      
      <div>
        <div id="agents-ativos" class="tab-content-table" style="display: block;">
          <?php $this->part('user-management/user-info/info-agents', array('agents' => $user->enabledAgents)); ?>
        </div>
        <div id="agents-concedidos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-agents', array('agents' => $user->hasControlAgents)); ?>
        </div>
        <div id="agents-rascunhos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-agents', array('agents' => $user->draftAgents)); ?>
        </div>
        <div id="agents-lixeira" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-agents', array('agents' => $user->trashedAgents)); ?>
        </div>
        <div id="agents-arquivo" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-agents', array('agents' => $user->archivedAgents)); ?>
        </div>
      </div>
    </div>

    <div id="espacos" class="aba-content">

      <div class="tab-table">
        <button class="tablinks active" data-entity="espacos" data-tab="spaces-ativos">    <?php \MapasCulturais\i::_e("Ativos");?>      (<?php echo count($user->enabledSpaces); ?>)  </button>
        <button class="tablinks "       data-entity="espacos" data-tab="spaces-concedidos"><?php \MapasCulturais\i::_e("Concedidos");?>  (<?php echo count($user->hasControlSpaces);?>)</button>
        <button class="tablinks "       data-entity="espacos" data-tab="spaces-rascunhos"> <?php \MapasCulturais\i::_e("Rascunhos");?>   (<?php echo count($user->draftSpaces); ?>)    </button>
        <button class="tablinks "       data-entity="espacos" data-tab="spaces-lixeira">   <?php \MapasCulturais\i::_e("Lixeira");?>     (<?php echo count($user->trashedSpaces); ?>)  </button>
        <button class="tablinks "       data-entity="espacos" data-tab="spaces-arquivo">   <?php \MapasCulturais\i::_e("Arquivo");?>     (<?php echo count($user->archivedSpaces); ?>) </button>
      </div>

      <div>
        <div id="spaces-ativos" class="tab-content-table" style="display: block;">
          <?php $this->part('user-management/user-info/info-spaces', array('spaces' => $user->enabledSpaces)); ?>
        </div>
        <div id="spaces-concedidos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-spaces', array('spaces' => $user->hasControlSpaces)); ?>
        </div>
        <div id="spaces-rascunhos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-spaces', array('spaces' => $user->draftSpaces)); ?>
        </div>
        <div id="spaces-lixeira" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-spaces', array('spaces' => $user->trashedSpaces)); ?>
        </div>
        <div id="spaces-arquivo" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-spaces', array('spaces' => $user->archivedSpaces)); ?>
        </div>
      </div>
    
    </div>

    <div id="eventos" class="aba-content">

      <div class="tab-table">
        <button class="tablinks active" data-entity="eventos" data-tab="events-ativos">    <?php \MapasCulturais\i::_e("Ativos");?>      (<?php echo count($user->enabledEvents); ?>)  </button>
        <button class="tablinks "       data-entity="eventos" data-tab="events-concedidos"><?php \MapasCulturais\i::_e("Concedidos");?>  (<?php echo count($user->hasControlEvents);?>)</button>
        <button class="tablinks "       data-entity="eventos" data-tab="events-rascunhos"> <?php \MapasCulturais\i::_e("Rascunhos");?>   (<?php echo count($user->draftEvents); ?>)    </button>
        <button class="tablinks "       data-entity="eventos" data-tab="events-lixeira">   <?php \MapasCulturais\i::_e("Lixeira");?>     (<?php echo count($user->trashedEvents); ?>)  </button>
        <button class="tablinks "       data-entity="eventos" data-tab="events-arquivo">   <?php \MapasCulturais\i::_e("Arquivo");?>     (<?php echo count($user->archivedEvents); ?>) </button>
      </div>

      <div>
        <div id="events-ativos" class="tab-content-table" style="display: block;">
          <?php $this->part('user-management/user-info/info-events', array('events' => $user->enabledEvents)); ?>
        </div>
        <div id="events-concedidos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-events', array('events' => $user->hasControlEvents)); ?>
        </div>
        <div id="events-rascunhos" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-events', array('events' => $user->draftEvents)); ?>
        </div>
        <div id="events-lixeira" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-events', array('events' => $user->trashedEvents)); ?>
        </div>
        <div id="events-arquivo" class="tab-content-table">
          <?php $this->part('user-management/user-info/info-events', array('events' => $user->archivedEvents)); ?>
        </div>
      </div>

    </div>

    <div id="projetos" class="aba-content">
      <div class="tab-table">
        <button class="tablinks active" data-entity="projetos" data-tab="projetos-ativos">    <?php \MapasCulturais\i::_e("Ativos");?>      (<?php echo count($user->enabledProjects); ?>)  </button>
        <button class="tablinks "       data-entity="projetos" data-tab="projetos-concedidos"><?php \MapasCulturais\i::_e("Concedidos");?>  (<?php echo count($user->hasControlProjects);?>)</button>
        <button class="tablinks "       data-entity="projetos" data-tab="projetos-rascunhos"> <?php \MapasCulturais\i::_e("Rascunhos");?>   (<?php echo count($user->draftProjects); ?>)    </button>
        <button class="tablinks "       data-entity="projetos" data-tab="projetos-lixeira">   <?php \MapasCulturais\i::_e("Lixeira");?>     (<?php echo count($user->trashedProjects); ?>)  </button>
        <button class="tablinks "       data-entity="projetos" data-tab="projetos-arquivo">   <?php \MapasCulturais\i::_e("Arquivo");?>     (<?php echo count($user->archivedProjects); ?>) </button>
      </div>

      <div id="projetos-ativos" class="tab-content-table" style="display: block;">
        <?php $this->part('user-management/user-info/info-projects', array('projects' => $user->enabledProjects)); ?>
      </div>
      <div id="projetos-concedidos" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-projects', array('projects' => $user->hasControlProjects)); ?>
      </div>
      <div id="projetos-rascunhos" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-projects', array('projects' => $user->draftProjects)); ?>
      </div>
      <div id="projetos-lixeira" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-projects', array('projects' => $user->trashedProjects)); ?>
      </div>
      <div id="projetos-arquivo" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-projects', array('projects' => $user->archivedProjects)); ?>
      </div>
    </div>

    <div id="oportunidades" class="aba-content">
      <div class="tab-table">
        <button class="tablinks active" data-entity="oportunidades" data-tab="oportunidades-ativos">    <?php \MapasCulturais\i::_e("Ativos");?>      (<?php echo count($user->enabledOpportunities); ?>)  </button>
        <button class="tablinks "       data-entity="oportunidades" data-tab="oportunidades-concedidos"><?php \MapasCulturais\i::_e("Concedidos");?>  (<?php echo count($user->hasControlOpportunities);?>)</button>
        <button class="tablinks "       data-entity="oportunidades" data-tab="oportunidades-rascunhos"> <?php \MapasCulturais\i::_e("Rascunhos");?>   (<?php echo count($user->draftOpportunities); ?>)    </button>
        <button class="tablinks "       data-entity="oportunidades" data-tab="oportunidades-lixeira">   <?php \MapasCulturais\i::_e("Lixeira");?>     (<?php echo count($user->trashedOpportunities); ?>)  </button>
        <button class="tablinks "       data-entity="oportunidades" data-tab="oportunidades-arquivo">   <?php \MapasCulturais\i::_e("Arquivo");?>     (<?php echo count($user->archivedOpportunities); ?>) </button>
      </div>

      <div id="oportunidades-ativos" class="tab-content-table" style="display: block;">
        <?php $this->part('user-management/user-info/info-opportunities', array('opportunities' => $user->enabledOpportunities)); ?>
      </div>
      <div id="oportunidades-concedidos" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-opportunities', array('opportunities' => $user->hasControlOpportunities)); ?>
      </div>
      <div id="oportunidades-rascunhos" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-opportunities', array('opportunities' => $user->draftOpportunities)); ?>
      </div>
      <div id="oportunidades-lixeira" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-opportunities', array('opportunities' => $user->trashedOpportunities)); ?>
      </div>
      <div id="oportunidades-arquivo" class="tab-content-table">
        <?php $this->part('user-management/user-info/info-opportunities', array('opportunities' => $user->archivedOpportunities)); ?>
      </div>
    </div>
  
    <div id="permissoes" class="aba-content">
      <div>

        <table class="permissions-table entity-table">
          <caption>
            <?php \MapasCulturais\i::_e("Permissões");?>
          </caption>
          <thead>
            <tr>
              <td><?php \MapasCulturais\i::_e("id");?></td>
              <td><?php \MapasCulturais\i::_e("subsite");?></td>
              <td><?php \MapasCulturais\i::_e("permissão");?></td>
            </tr>
          </thead>
          <tbody>
          <?php
            foreach ($roles as $role) {
          ?>
            <tr>
              <td style="white-space: nowrap;  width:1%;"><?php echo $role['id'];?></td>
              <td><?php echo $role['subsite'];?></td>
              <td style="white-space: nowrap;  width:1%;">
                <?php if ( $current_user->is('superAdmin', $role['subsite_id']) ) { ?>

                  <div id="funcao-do-agente-user-managerment" class="dropdown dropdown-select">
                    <div class="placeholder js-selected">
                      <span data-role="<?php echo $role['role'];?>" data-subsite="<?php echo $role['subsite_id'];?>"><?php echo $role['role']; ?></span>
                    </div>

                    <div class="submenu-dropdown js-options">
                      <ul>
                        <li data-subsite="<?php echo $role['subsite_id'];?>">
                          <span><?php \MapasCulturais\i::_e("Normal");?></span>
                        </li>

                        <?php if ($user->canUser('addRoleAdmin')): ?>
                          <li data-role="admin" data-subsite="<?php echo $role['subsite_id'];?>">
                            <span><?php echo $app->getRoleName('admin') ?></span>
                          </li>
                        <?php endif; ?>

                        <?php if ($user->canUser('addRoleSuperAdmin')): ?>
                          <li data-role="superAdmin" data-subsite="<?php echo $role['subsite_id'];?>">
                            <span><?php echo $app->getRoleName('superAdmin') ?></span>
                          </li>
                        <?php endif; ?>

                        <?php if ($user->canUser('addRoleSaasAdmin')): ?>
                          <li data-role="saasAdmin" data-subsite="<?php echo $role['subsite_id'];?>">
                            <span><?php echo $app->getRoleName('saasAdmin') ?></span>
                          </li>
                        <?php endif; ?>
                        
                        <?php if ($user->canUser('addRoleSaasSuperAdmin')): ?>
                          <li data-role="saasSuperAdmin" data-subsite="<?php echo $role['subsite_id'];?>">
                            <span><?php echo $app->getRoleName('saasSuperAdmin') ?></span>
                          </li>
                        <?php endif; ?>
                      </ul>
                    </div>
                  </div>

                <?php 
                  } else {
                    echo $role['role'];
                  }
                ?>
              </td>
            </tr>
          <?php
            }
          ?>
          </tbody>
        </table>

        <a class="add js-open-dialog" data-dialog="#add-roles" data-dialog-block="true" rel='noopener noreferrer'>
          <?php \MapasCulturais\i::_e("Adicionar");?>
        </a>

        <div id="add-roles" class="js-dialog entity-modal" title="<?php \MapasCulturais\i::_e("Adicionar permissão");?>">
          <div>
            <label for="subsiteList"  style="width:125px; display:inline-block">
              <?php \MapasCulturais\i::_e("Subsite:");?>
            </label>
            <select id="subsiteList" >
              <?php
                $subsites = $app->repo('User')->getSubsitesCanAddRoles($current_user->id);
                foreach($subsites as $subsite) { ?>
                  <option value="<?php echo $subsite->id;?>"> <?php echo $subsite->id.'-'.$subsite->name;?> </option>
              <?php } ?>
            </select>
            <br />
            <label for="permissionList" style="width:125px; display:inline-block">
              <?php \MapasCulturais\i::_e("Permissão:");?>
            </label>
            <select id="permissionList">
              <?php if ($user->canUser('addRoleAdmin')): ?>
                <option value="admin"><?php echo $app->getRoleName('admin');?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSuperAdmin')): ?>
                <option value="superAdmin"><?php echo $app->getRoleName('superAdmin'); ?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSaasAdmin')): ?>
                <option value="saasAdmin"><?php echo $app->getRoleName('saasAdmin'); ?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSaasSuperAdmin')): ?>
                <option value="saasSuperAdmin"><?php echo $app->getRoleName('saasSuperAdmin'); ?></option>
              <?php endif; ?>
            </select>
            <br>
            <button class="btn add" id="user-managerment-addRole" ><?php \MapasCulturais\i::_e("Adicionar permissão");?></button>
          </div>
       </div>

      </div>
    </div>

    <div id="atividade" class="aba-content">
      <span ng-show="user.history.spinnerShow">
        <img src="<?php $this->asset('img/spinner.gif') ?>" />
        <span><?php \MapasCulturais\i::_e("Obtendo histório..."); ?></span>
      </span>
      <div ng-show="!user.history.spinnerShow">
        <table class="history-table entity-table">
          <caption>
            <?php \MapasCulturais\i::_e("Log de atividades");?>
          </caption>
          <thead>
            <tr>
              <td><?php \MapasCulturais\i::_e("id da entidade");?></td>
              <td><?php \MapasCulturais\i::_e("tipo entidade");?></td>
              <td><?php \MapasCulturais\i::_e("descrição");?></td>
              <td><?php \MapasCulturais\i::_e("data");?></td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="history in user.history.list">
              <td>{{history.objectId}}</td>
              <td>{{history.objectType}}</td>
              <td>{{history.message}}</td>
              <td>{{history.createTimestamp.date | date: 'dd-mm-yyyy HH:mm:ss'}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>

<?php
  use MapasCulturais\Entities\Agent;
  use MapasCulturais\Entities\Space;
  use MapasCulturais\Entities\Event;
  $current_user = $app->user;
  
  $subsitesAdmin = $app->repo('User')->getSubsitesAdminRoles($current_user->id);
  $this->jsObject['subsitesAdmin'] = $subsitesAdmin;
?>
<div class="user-managerment-infos" ng-init="load(<?=$user->id?>)">  
  <div class="user-info">
    <div style="float:left">
      <span class="label">id:</span> 
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="id" data-emptytext="">
        <?=$user->id?>
      </span> <br />
      <span class="label">email:</span>
      <span class="js-editable editable-click editable-empty" data-edit="email" data-original-title="email" data-emptytext="">
        <?=$user->email?> 
      </span> <br />
      <span class="label">autenticação:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="autenticação" data-emptytext="">
        <?=$user->authProvider?> <!-- // como pegar pelo ID no registerAuthProvider? -->
      </span> <br />
      <span class="label">id autenticação:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="id autenticação" data-emptytext="">
        <?=$user->authUid?>
      </span> <br />
    </div>

    <div style="float:left">
      <span class="label">status:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="status" data-emptytext="">
        <?php 
          if ($user->status == 1)
            echo \MapasCulturais\i::_e("Ativo");
          else 
          echo \MapasCulturais\i::_e("Inativo");
        ?>
      </span> <br />
      <span class="label">último login:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="último login" data-emptytext="">
      <?=$user->lastLoginTimestamp->format('d-m-Y \a\s H:i:s')?>
      </span> <br />
      <span class="label">data criação:</span>
      <span class="js-editable editable-click editable-empty" data-edit="" data-original-title="data criação" data-emptytext="">
        <?=$user->createTimestamp->format('d-m-Y \a\s H:i:s')?>
      </span> <br />
    </div>

    <span class="clearfix clear" />
  </div>
    
  <div>
    <ul class="abas clearfix clear">
      <li class="active"><a href="#agentes"><?php \MapasCulturais\i::_e("Agentes");?></a></li>
      <li><a href="#espacos"><?php \MapasCulturais\i::_e("Espaços");?></a></li>
      <li><a href="#eventos"><?php \MapasCulturais\i::_e("Eventos");?></a></li>
      <li><a href="#permissoes"><?php \MapasCulturais\i::_e("Permissões");?></a></li>          
      <li><a href="#atividade"><?php \MapasCulturais\i::_e("Atividades");?></a></li>
    </ul>
  </div>
    
  <div class="tabs-content">
    <div id="agentes" class="aba-content">
      <span ng-show="user.agents.spinnerShow">
        <img src="<?php $this->asset('img/spinner.gif') ?>" />
        <span><?php \MapasCulturais\i::_e("obtendo agentes..."); ?></span>
      </span>
      <div ng-show="!user.agents.spinnerShow">
        <table class="agents-table entity-table">
          <caption> 
              <?=\MapasCulturais\i::_e("Agentes");?>
          </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <td>Subsite</td>
              <td>Operações</td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="agent in user.agents.list">
              <td style="white-space: nowrap;  width:1%;">
                <span ng-if="<?=$user->profile->id?> == agent.id">
                  <a class="icon icon-agent active" title="<?php \MapasCulturais\i::esc_attr_e("Este é o agente padrão.");?>"></a>
                  <a href={{agent.singleUrl}}>{{agent.id}}</a>
                </span>
                <span ng-if="<?=$user->profile->id?> != agent.id">
                  <a class="icon icon-agent"></a>
                  <a href={{agent.singleUrl}}>{{agent.id}}</a>
                </span>
              </td>
              <td>{{agent.name}}</td>
              <td style="width:1%;">{{agent.subsite.name}}</td>
              <td>
                <span ng-if="hasAdmin(agent.subsite) || hasControl(agent.agentRelations, 'agent')">
                  <a class="btn btn-small btn-danger" ng-if="agent.status == <?=Agent::STATUS_ENABLED?>" href="{{agent.deleteUrl}}"><?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" ng-if="agent.status == <?=Agent::STATUS_ENABLED?>" href="{{agent.archiveUrl}}"><?php \MapasCulturais\i::_e("arquivar");?></a>
                  <a class="btn btn-small btn-warning" ng-if="agent.status == <?=Agent::STATUS_DRAFT?>" href="{{agent.publishUrl}}"><?php \MapasCulturais\i::_e("publicar");?></a>
                  <a class="btn btn-small btn-success" ng-if="agent.status == <?=Agent::STATUS_ARCHIVED?>" href="{{agent.unarchiveUrl}}"><?php \MapasCulturais\i::_e("desarquivar");?></a>
                  <a class="btn btn-small btn-success" ng-if="agent.status == <?=Agent::STATUS_ARCHIVED?>" href="{{agent.undeleteUrl}}"><?php \MapasCulturais\i::_e("recuperar");?></a>
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <table class="agents-table entity-table">
          <caption> <?=\MapasCulturais\i::_e("Agentes controlados");?> </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <td>Subsite</td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="agent in user.agents.relatedsAgents">
              <td style="white-space: nowrap;  width:1%;">
                <span>
                  <a class="icon icon-agent"></a>
                  <a href={{agent.singleUrl}}>{{agent.id}}</a>
                </span>
              </td>
              <td>{{agent.name}}</td>
              <td style="width:1%;">{{agent.subsite.name}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div id="espacos" class="aba-content">
      <span ng-show="user.spaces.spinnerShow">
        <img src="<?php $this->asset('img/spinner.gif') ?>" />
        <span><?php \MapasCulturais\i::_e("obtendo espaços..."); ?></span>
      </span>
      <div ng-show="!user.spaces.spinnerShow">
        <table class="spaces-table entity-table">
          <caption> 
              <?=\MapasCulturais\i::_e("Espaços");?>
          </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <td>Subsite</td>
              <td>Operações</td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="space in user.spaces.list">
              <td>
                <a href={{space.singleUrl}} class="icon icon-space"></a>
                <a href={{space.singleUrl}}>{{space.id}}</a>
              </td>
              <td>{{space.name}}</td>
              <td>{{space.subsite.name}}</td>
              <td>
                <span ng-if="hasAdmin(space.subsite) || hasControl(space.agentRelations, 'agent')">
                  <a class="btn btn-small  btn-danger" ng-if="space.status == <?=Space::STATUS_ENABLED?>"  href="{{space.deleteUrl}}">   <?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" ng-if="space.status == <?=Space::STATUS_ENABLED?>"  href="{{space.archiveUrl}}">  <?php \MapasCulturais\i::_e("arquivar");?></a>
                  <a class="btn btn-small btn-warning" ng-if="space.status == <?=Space::STATUS_DRAFT?>"    href="{{space.publishUrl}}">  <?php \MapasCulturais\i::_e("publicar");?></a>
                  <a class="btn btn-small  btn-danger" ng-if="space.status == <?=Space::STATUS_DRAFT?>"    href="{{space.deleteUrl}}">   <?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-success" ng-if="space.status == <?=Space::STATUS_ARCHIVED?>" href="{{space.unarchiveUrl}}"><?php \MapasCulturais\i::_e("desarquivar");?></a>
                  <a class="btn btn-small btn-success" ng-if="space.status == <?=Space::STATUS_ARCHIVED?>" href="{{space.undeleteUrl}}"> <?php \MapasCulturais\i::_e("recuperar");?></a>
                </span>
              </td>
            </tr>
          </tbody>
        </table>

        <table class="spaces-table entity-table">
          <caption>
              <?=\MapasCulturais\i::_e("Espaços controlados");?>
          </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <td>Subsite</td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="space in user.spaces.relatedsSpaces">
              <td> 
                <a href={{space.singleUrl}} class="icon icon-space"></a> 
                <a href={{space.singleUrl}}>{{space.id}}</a> </td>
              <td>{{space.name}}</td>
              <td>{{space.subsite.name}}</td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    <div id="eventos" class="aba-content">
      <span ng-show="user.events.spinnerShow">
        <img src="<?php $this->asset('img/spinner.gif') ?>" />
        <span><?php \MapasCulturais\i::_e("obtendo eventos..."); ?></span>
      </span>
      <div ng-show="!user.spaces.spinnerShow">
        <table class="events-table entity-table">
          <caption>
            <?=\MapasCulturais\i::_e("Eventos");?>
          </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <td>Operações</td>
              <!-- <td>Subsite</td> -->
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="event in user.events.list">
              <td>
                <a href={{event.singleUrl}} class="icon icon-event"></a>
                <a href={{event.singleUrl}}>{{event.id}}</a>
              </td>
              <td>{{event.name}}</td>
              <td>
                <span ng-if="hasAdmin(event.subsite)">
                  <a class="btn btn-small btn-danger"  ng-if="space.status == <?=Event::STATUS_ENABLED?>" href="{{event.deleteUrl}}"><?php \MapasCulturais\i::_e("excluir");?></a>
                  <a class="btn btn-small btn-danger"  ng-if="space.status == <?=Event::STATUS_DRAFT?>"   href="{{event.deleteUrl}}"><?php \MapasCulturais\i::_e("excluir");?></a>
                </span>
              </td>
              <!-- <td>{{event.subsite.name}}</td> -->
            </tr>
          </tbody>
        </table>


        <table class="events-table entity-table">
          <caption>
            <?=\MapasCulturais\i::_e("Eventos controlados");?>
          </caption>
          <thead>
            <tr>
              <td>id</td>
              <td>Nome</td>
              <!-- <td>Subsite</td> -->
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="event in user.events.relatedsSpaces">
              <td> 
              <td>
                <a href={{event.singleUrl}} class="icon icon-event"></a>
                <a href={{event.singleUrl}}>{{event.id}}</a>
              </td>                
              <td>{{event.name}}</td>
              <!-- <td>{{event.subsite.name}}</td> -->
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  
    <div id="permissoes" class="aba-content">
      <div>

        <table class="permissions-table entity-table">
          <caption>
            <?=\MapasCulturais\i::_e("Permissões");?>
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
              <td style="white-space: nowrap;  width:1%;"><?=$role['id']?></td>
              <td><?=$role['subsite']?></td>
              <td style="white-space: nowrap;  width:1%;">
                <?php if ( $current_user->is('superAdmin', $role['subsite_id']) ) { ?>

                  <div id="funcao-do-agente-user-managerment" class="dropdown dropdown-select">
                    <div class="placeholder js-selected">
                      <span data-role="<?=$role['role']?>" data-subsite="<?=$role['subsite_id']?>"><?php echo $role['role']; ?></span>
                    </div>

                    <div class="submenu-dropdown js-options">
                      <ul>
                        <li data-subsite="<?=$role['subsite_id']?>">
                          <span><?php \MapasCulturais\i::_e("Normal");?></span>
                        </li>

                        <?php if ($user->canUser('addRoleAdmin')): ?>
                          <li data-role="admin" data-subsite="<?=$role['subsite_id']?>">
                            <span><?php echo $app->getRoleName('admin') ?></span>
                          </li>
                        <?php endif; ?>

                        <?php if ($user->canUser('addRoleSuperAdmin')): ?>
                          <li data-role="superAdmin" data-subsite="<?=$role['subsite_id']?>">
                            <span><?php echo $app->getRoleName('superAdmin') ?></span>
                          </li>
                        <?php endif; ?>

                        <?php if ($user->canUser('addRoleSaasAdmin')): ?>
                          <li data-role="saasAdmin" data-subsite="<?=$role['subsite_id']?>">
                            <span><?php echo $app->getRoleName('saasAdmin') ?></span>
                          </li>
                        <?php endif; ?>
                        
                        <?php if ($user->canUser('addRoleSaasSuperAdmin')): ?>
                          <li data-role="saasSuperAdmin" data-subsite="<?=$role['subsite_id']?>">
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

        <a class="add js-open-dialog" data-dialog="#add-roles" data-dialog-block="true">
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
                  <option value="<?=$subsite->id?>"> <?=$subsite->id.'-'.$subsite->name?> </option>
              <?php } ?>
            </select>
            <br />
            <label for="permissionList" style="width:125px; display:inline-block">
              <?php \MapasCulturais\i::_e("Permissão:");?>
            </label>
            <select id="permissionList">
              <?php if ($user->canUser('addRoleAdmin')): ?>
                <option value="admin"><?=$app->getRoleName('admin')?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSuperAdmin')): ?>
                <option value="superAdmin"><?=$app->getRoleName('superAdmin') ?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSaasAdmin')): ?>
                <option value="saasAdmin"><?=$app->getRoleName('saasAdmin') ?></option>
              <?php endif; ?>

              <?php if ($user->canUser('addRoleSaasSuperAdmin')): ?>
                <option value="saasSuperAdmin"><?=$app->getRoleName('saasSuperAdmin') ?></option>
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
            <?=\MapasCulturais\i::_e("Log de atividades");?>
          </caption>
          <thead>
            <tr>
              <td><?php \MapasCulturais\i::_e("id");?></td>
              <td><?php \MapasCulturais\i::_e("id da entidade");?></td>
              <td><?php \MapasCulturais\i::_e("tipo entidade");?></td>
              <td><?php \MapasCulturais\i::_e("ação");?></td>
              <td><?php \MapasCulturais\i::_e("descrição");?></td>
              <td><?php \MapasCulturais\i::_e("data");?></td>
            </tr>
          </thead>
          <tbody>
            <tr ng-repeat="history in user.history.list">
              <td>{{history.id}}</td>
              <td>{{history.objectId}}</td>
              <td>{{history.objectType}}</td>
              <td>{{history.action}}</td>
              <td>{{history.message}}</td>
              <td>{{history.createTimestamp.date}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</div>
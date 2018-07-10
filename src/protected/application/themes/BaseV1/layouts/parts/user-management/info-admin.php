<?php
  use MapasCulturais\App;
  use MapasCulturais\Entities\Agent;
  use MapasCulturais\Entities\Space;
  use MapasCulturais\Entities\Event;


  $this->requireAuthentication();
  $app = App::i(); 
  $roles = $app->getRoles();
  $subsite_id = $app->getCurrentSubsiteId();

  $first = true;
  $noSubSite = ($app->getCurrentSubsiteId() == 0 || $app->getCurrentSubsiteId() == null);

  if (!$app->user->is('admin')) 
    $app->user->checkPermission('addRole'); // dispara exceção se não for admin ou sueradmin

  $Repo = $app->repo('User');
  $vars = array();

  foreach ($roles as $roleSlug => $roleInfo) {
    $vars['list_' . $roleSlug] = $Repo->getByRole($roleSlug, $subsite_id);
    if ($roleSlug == 'superAdmin') {
      $roles[$roleSlug]['permissionSuffix'] = 'SuperAdmin';
    } elseif ($roleSlug == 'admin') {
      $roles[$roleSlug]['permissionSuffix'] = 'Admin';
    } else {
      $roles[$roleSlug]['permissionSuffix'] = '';
    }
    
    $roles[$roleSlug]['users'] = array();
    
    foreach($vars['list_' . $roleSlug] as $u):
      $remove_role_url = false;
      $subsiteUrl = '';
      $subsiteName = "MapasCulturais";
      if ($u->user->profile->canUser('RemoveRole' . $roles[$roleSlug]['permissionSuffix'])):
        $remove_role_url = $app->createUrl('agent', 'removeRole', ['id' => $u->user->profile->id, 'role' => $roleSlug]);
        if($noSubSite && is_object($u->subsite)):
          $remove_role_url = $app->createUrl('agent', 'removeRole', ['id' => $u->user->profile->id, 'role' => $roleSlug, 'subsiteId' => $u->subsite->id]);
          $subsiteUrl = 'http://' . $u->subsite->url;
          $subsiteName = $u->subsite->name;
        endif;
      endif;

      $roles[$roleSlug]['users'][$subsiteName][] = ['name' => $u->user->profile->name, 
                                      'singleUrl' => $u->user->profile->singleUrl,
                                      'removeRoleUrl' => $remove_role_url,
                                      'subsiteURL' => $subsiteUrl];
    endforeach;

  }
  $this->jsObject['infoAdmin']['roles'] = $roles;
  //\dump($roles); die();
?>

<div class="user-managerment-admin">

  <div class="option-roles">
    <label class="small" for="roles">Papeis:</label>
    <select id="roles" ng-model="selectGroupAdmin" style="display: block;">
      <?php foreach ($roles as $roleSlug => $role) : ?>
        <option value="<?php echo $roleSlug; ?>">
          <?php echo $this->dict($role['pluralLabel']); ?>
        </option>
      <?php endforeach; ?>
    </select>
    
    <label class="small" for="subsites">Subsites:</label>
    <select id="subsites" size="8" style="height: 300px;" ng-model="selectSubsite">
      <option title="{{key}}" class="icon icon-subsite" ng-repeat="(key, value) in data.infoAdmin.roles[selectGroupAdmin].users" value="{{key}}">
        {{key}}
      </option>
    </select>
  </div>

  <div class="entity-table-content">
    <table class="user-admin-table entity-table">
      <caption>
        <?=\MapasCulturais\i::_e("Administradores");?> : {{selectSubsite}}
      </caption>

      <thead>
        <tr>
          <td>Nome</td>
          <td>Operação</td>
        </tr>
      </thead>

      <tbody>
        <tr ng-repeat="usr in data.infoAdmin.roles[selectGroupAdmin].users[selectSubsite]">
          <td style="width: 30%;">
            <div>
              <span class="truncate">
                <a title={{usr.name}} href="{{usr.singleUrl}}">{{usr.name}}</a>
              </span>
              <span class="truncate">
                <a class="small" href="{{usr.subsiteURL}}" style="color:#C3C3C3" target="_blank">{{usr.subsiteURL}}</a>
              </span>
            </div>
          </td>
          <td style="width: 30%;">
            <a class="btn btn-small btn-danger js-confirm-before-go icon icon-minus"
              data-confirm-text="Você tem certeza que deseja remover este usuário da lista" 
              href="{{usr.removeRoleUrl}}" title="remover do papel"> Excluir
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
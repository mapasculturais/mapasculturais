<?php
  use MapasCulturais\App;
  use MapasCulturais\Entities\Agent;
  use MapasCulturais\Entities\Space;
  use MapasCulturais\Entities\Event;
use MapasCulturais\i;

  $app = App::i(); 
  $roles = [];
  foreach ($app->getRoles() as $def) {
    $roles[$def->role] = [
      'name' => $def->role,
      'singularLabel' => $def->name,
      'pluralLabel' => $def->pluralName,
    ];
  }

  $subsite_id = $app->getCurrentSubsiteId();

  $first = true;
  
  if (!$app->user->is('admin')) 
    $app->user->checkPermission('addRole'); // dispara exceção se não for admin ou sueradmin

  $Repo = $app->repo('User');
  $vars = array();
  
  $subsites = $Repo->getSubsitesAdminRoles($app->user->id);
  foreach ($roles as $roleSlug => $roleInfo) {
    $vars['list_' . $roleSlug] = [];
    if($app->user->is('saasSuperAdmin'))
      $vars['list_' . $roleSlug] = $Repo->getByRole($roleSlug);
    else
      foreach($subsites as $sub) {
        $aux = $Repo->getByRole($roleSlug, $sub->id);
        if(!empty($aux))
          $vars['list_' . $roleSlug] = array_merge($vars['list_' . $roleSlug], $aux);
      }
    if ($roleSlug == 'superAdmin') {
      $roles[$roleSlug]['permissionSuffix'] = 'SuperAdmin';
    } elseif ($roleSlug == 'admin') {
      $roles[$roleSlug]['permissionSuffix'] = 'Admin';
    } else {
      $roles[$roleSlug]['permissionSuffix'] = '';
    }

    foreach($vars['list_' . $roleSlug] as $u):
      $remove_role_url = false;
      $subsiteUrl = '';
      $subsiteName = "MapasCulturais";
      if ($u->user->profile->canUser('RemoveRole' . $roles[$roleSlug]['permissionSuffix']) && $u->user->id != $app->user->id ):
        $remove_role_url = $app->createUrl('agent', 'removeRole', ['id' => $u->user->profile->id, 'role' => $roleSlug]);
        if(is_object($u->subsite) && $app->getCurrentSubsiteId() != $u->subsite->id):
          $remove_role_url = $app->createUrl('agent', 'removeRole', ['id' => $u->user->profile->id, 'role' => $roleSlug, 'subsiteId' => $u->subsite->id]);
        endif;
      endif;
      if(is_object($u->subsite)):
        $subsiteUrl = 'http://' . $u->subsite->url;
        $subsiteName = $u->subsite->name;
      endif;
      $roles['users'][$subsiteName][] = ['name' => $u->user->profile->name,
                                      'singleUrl' => $u->user->profile->singleUrl,
                                      'removeRoleUrl' => $remove_role_url,
                                      'subsiteURL' => $subsiteUrl,
                                      'role' => $roleSlug];
    endforeach;
  }
  $this->jsObject['infoAdmin']['roles'] = $roles;
?>

<div class="user-managerment-admin">
  <div style="width: 100%;">
    <table class="user-admin-table entity-table">
      <caption>
        <div ng-if="hasSubsites()">
          <?php \MapasCulturais\i::_e('Administradores do Subsite:'); ?>
          <select id="subsites" ng-model="selectSubsite" style="margin-bottom: 0px; min-width: 140px;">
            <option title="{{key}}" class="icon icon-subsite" ng-repeat="(key, value) in data.infoAdmin.roles.users" value="{{key}}">
              {{key}}
            </option>
          </select>
        </div>
        <div ng-if="!hasSubsites()">
          <?php i::_e('Administradores') ?>
        </div>

      </caption>

      <thead>
        <tr>
          <td><b><?php \MapasCulturais\i::_e('Nome'); ?></b></td>
          <td><b><?php \MapasCulturais\i::_e('Grupo'); ?></b></td>
          <td><b><?php \MapasCulturais\i::_e('Operação'); ?></b></td>
        </tr>
      </thead>

      <tbody>
        <tr ng-repeat="usr in data.infoAdmin.roles.users[selectSubsite]">
          <td style="width: 30%;">
            <div>
              <span class="truncate">
                <a title={{usr.name}} href="{{usr.singleUrl}}" rel='noopener noreferrer'>{{usr.name}}</a>
              </span>
              <span class="truncate">
                <a class="small" href="{{usr.subsiteURL}}" style="color:#C3C3C3" target="_blank" rel='noopener noreferrer'>{{usr.subsiteURL}}</a>
              </span>
            </div>
          </td>
          <td style="width: 30%;">
            {{usr.role}}
          </td>
          <td style="width: 30%;">
            <a class="btn btn-small btn-danger js-confirm-before-go icon icon-minus" ng-if="usr.removeRoleUrl"
              data-confirm-text="<?php \MapasCulturais\i::esc_attr_e('Você tem certeza que deseja remover este usuário da lista'); ?>" 
              href="{{usr.removeRoleUrl}}" title="remover do papel"> <?php \MapasCulturais\i::_e('Excluir'); ?>
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

</div>
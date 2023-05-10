<?php
namespace MapasCulturais\Definitions;

use MapasCulturais\App;
use MapasCulturais\UserInterface;

/**
 * Definição de Role
 * 
 * @property-read string $role Slug da role
 * @property-read string $name Nome da role
 * @property-read string $pluralName Nome da role no plural
 * @property-read bool $subsiteContext O role é válido somente no contexto do subsite?
 * @property-read string[] $anotherRoles Outras roles desta role
 */
class Role extends \MapasCulturais\Definition{
    public $_role;

    public $_name;

    public $_pluralName;

    public $_subsiteContext;

    public $_canUserManageRole;

    public $_anotherRoles;

    public function __construct($role, $name, $plural_name, bool $subsite_context, callable $can_user_manage_role, array $another_roles = []) {
        $this->role = $role;
        $this->name = $name;
        $this->pluralName = $plural_name;
        $this->canUserManageRole = $can_user_manage_role;
        $this->subsiteContext = $subsite_context;

        $another_roles[] = $role;
        $this->anotherRoles = array_unique($another_roles);
    }

    function getRole() {
        return $this->role;
    }   

    function getName() {
        return $this->name;
    }

    function getPluralName() {
        return $this->pluralName;
    }

    function getAnotherRoles() {
        return $this->anotherRoles;
    }

    function getSubsiteContext() {
        return $this->subsiteContext;
    }

    function canUserManageRole(UserInterface $logged_in_user = null, $subsite_id = false) {
        $app = App::i();
        if ($subsite_id === false) {
            $subsite_id = $app->getCurrentSubsiteId();
        }

        if (is_null($logged_in_user)) {
            $logged_in_user = $app->user;
        }
        $function = $this->canUserManageRole;
        return $function($logged_in_user, $subsite_id);
    }

    function hasRole(string $role) {
        return in_array($role, $this->anotherRoles);
    }

}

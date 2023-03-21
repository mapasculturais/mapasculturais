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
    protected $_role;

    protected $_name;

    protected $_pluralName;

    protected $_subsiteContext;

    protected $_canUserManageRole;

    protected $_anotherRoles;

    public function __construct($role, $name, $plural_name, bool $subsite_context, callable $can_user_manage_role, array $another_roles = []) {
        $this->_role = $role;
        $this->_name = $name;
        $this->_pluralName = $plural_name;
        $this->_canUserManageRole = $can_user_manage_role;
        $this->_subsiteContext = $subsite_context;

        $another_roles[] = $role;
        $this->_anotherRoles = array_unique($another_roles);
    }

    function getRole() {
        return $this->_role;
    }   

    function getName() {
        return $this->_name;
    }

    function getPluralName() {
        return $this->_pluralName;
    }

    function getAnotherRoles() {
        return $this->_anotherRoles;
    }

    function getSubsiteContext() {
        return $this->_subsiteContext;
    }

    function canUserManageRole(UserInterface $logged_in_user = null, $subsite_id = false) {
        $app = App::i();
        if ($subsite_id === false) {
            $subsite_id = $app->getCurrentSubsiteId();
        }

        if (is_null($logged_in_user)) {
            $logged_in_user = $app->user;
        }
        $function = $this->_canUserManageRole;
        return $function($logged_in_user, $subsite_id);
    }

    function hasRole(string $role) {
        return in_array($role, $this->_anotherRoles);
    }

}

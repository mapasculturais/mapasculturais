<?php
namespace MapasCulturais\Definitions;

use MapasCulturais\App;
use MapasCulturais\UserInterface;

/**
 * Definição de Role
 */
class Role extends \MapasCulturais\Definition{
    /**
     * Slug da role
     * @var string
     */
    public string $role;

    /**
     * Nome da role, legível para humanos
     * @var string
     */
    public string $name;

    /**
     * Nome da role no plural, legível para humanos
     * @var string
     */
    public string $pluralName;

    /**
     * Indica se a role, quando aplicada a um usuário, se limita a um subsite 
     * ou se é para todo o SaaS
     * 
     * @var bool
     */
    public $subsiteContext;

    /**
     * 
     * @var callable
     */
    public $canUserManageRole;

    /**
     * Lista das roles que um usuário que tenha a role que está sendo registrada também terá implicitamente
     * 
     * por exemplo: Um Super Admin também é um Admin, implicitamente, então no registro da role `superAdmin`, 
     * deve ser indicado neste campo a role `admin`
     * 
     * @var array
     */
    public $anotherRoles;

    public function __construct(string $role, string $name, string $plural_name, bool $subsite_context, callable $can_user_manage_role, array $another_roles = []) {
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

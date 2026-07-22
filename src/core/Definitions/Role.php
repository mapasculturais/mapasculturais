<?php
namespace MapasCulturais\Definitions;

use MapasCulturais\App;
use MapasCulturais\UserInterface;

/**
 * Definição de Role (Papel de Usuário)
 * 
 * Define um papel de usuário no sistema com suas permissões e contexto
 * 
 * @package MapasCulturais\Definitions
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
     * Função que verifica se um usuário pode gerenciar esta role
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

    /**
     * Construtor da classe
     * 
     * @param string $role Slug da role
     * @param string $name Nome da role
     * @param string $plural_name Nome da role no plural
     * @param bool $subsite_context Indica se a role é limitada a um subsite
     * @param callable $can_user_manage_role Função que verifica se um usuário pode gerenciar a role
     * @param array $another_roles Lista de roles implícitas
     */
    public function __construct(string $role, string $name, string $plural_name, bool $subsite_context, callable $can_user_manage_role, array $another_roles = []) {
        $this->role = $role;
        $this->name = $name;
        $this->pluralName = $plural_name;
        $this->canUserManageRole = $can_user_manage_role;
        $this->subsiteContext = $subsite_context;

        $another_roles[] = $role;
        
        $this->anotherRoles = array_unique($another_roles);
    }

    /**
     * Obtém o slug da role
     * 
     * @return string
     */
    function getRole() {
        return $this->role;
    }   

    /**
     * Obtém o nome da role
     * 
     * @return string
     */
    function getName() {
        return $this->name;
    }

    /**
     * Obtém o nome da role no plural
     * 
     * @return string
     */
    function getPluralName() {
        return $this->pluralName;
    }

    /**
     * Obtém a lista de roles implícitas
     * 
     * @return array
     */
    function getAnotherRoles() {
        return $this->anotherRoles;
    }

    /**
     * Obtém o contexto da role (subsite ou global)
     * 
     * @return bool
     */
    function getSubsiteContext() {
        return $this->subsiteContext;
    }

    /**
     * Verifica se um usuário pode gerenciar esta role
     * 
     * @param UserInterface|null $logged_in_user Usuário logado
     * @param mixed $subsite_id ID do subsite
     * @return bool
     */
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

    /**
     * Verifica se a role inclui outra role específica
     * 
     * @param string $role Slug da role a verificar
     * @return bool
     */
    function hasRole(string $role) {
        return in_array($role, $this->anotherRoles);
    }

}

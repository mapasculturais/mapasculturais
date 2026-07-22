<?php
namespace MapasCulturais;

/**
 * Classe que representa um usuário não autenticado (visitante)
 * 
 * @property-read bool $isEvaluator Indica se o usuário é um avaliador (sempre false para convidados)
 * 
 * @package MapasCulturais
 */
class GuestUser implements UserInterface{
    use Traits\Singleton,
        Traits\MagicGetter,
        Traits\MagicCallers;

    /**
     * ID do usuário convidado (sempre 0)
     * @var int
     */
    public $id = 0;

    /**
     * Perfil do usuário convidado (sempre null)
     * @var null
     */
    public $profile = null;
    
    function __toString() {
        return "guest:" . session_id();
    }

    /**
     * Verifica se o usuário possui um papel (role). Sempre retorna true apenas para 'guest'.
     * 
     * @param string $role
     * @param mixed $subsite
     * @return bool
     */
    function is(string $role, $subsite = false){
        return $role == 'guest';
    }

    /**
     * Verifica se o usuário é procurador para uma ação. Sempre retorna false para convidados.
     * 
     * @param string $action
     * @param mixed $user
     * @return bool
     */
    function isAttorney($action, $user= null){
        return false;
    }

    /**
     * Retorna se o usuário é avaliador. Sempre retorna false para convidados.
     * 
     * @return bool
     */
    function getIsEvaluator() {
        return false;
    }
    
    /**
     * Verifica se este objeto é igual a outro
     * 
     * @param mixed $obj
     * @return bool
     */
    function equals($obj){
        return $this == $obj;
    }

    /**
     * Retorna o próprio objeto como usuário proprietário
     * 
     * @return self
     */
    function getOwnerUser() {
        return $this;
    }

    /**
     * Retorna a lista de selos que o usuário controla. Sempre vazio para convidados.
     * 
     * @return array
     */
    function getHasControlSeals () {
        return [];
    }
}
<?php
namespace MapasCulturais;

class GuestUser implements UserInterface{
    use Traits\Singleton;
    use Traits\MagicGetter;

    public $id = 0;

    public $profile = null;
    
    function __toString() {
        return "guest:" . session_id();
    }

    function is(string $role, $subsite = false){
        return $role == 'guest';
    }

    function isAttorney($action, $user= null){
        return false;
    }

    function getIsEvaluator() {
        return false;
    }
    
    function equals($obj){
        return $this == $obj;
    }

    function getOwnerUser() {
        return $this;
    }

    function getHasControlSeals () {
        return [];
    }
}
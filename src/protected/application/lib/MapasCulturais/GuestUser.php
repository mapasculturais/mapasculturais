<?php
namespace MapasCulturais;

class GuestUser implements UserInterface{
    use Traits\Singleton;

    public $id = 0;

    public $profile = null;
    
    function __construct() {
        $this->profile = new \stdClass;
    }
    
    function __toString() {
        return "guest:" . session_id();
    }

    function is($role){
        return $role == 'guest';
    }
    
    function equals($obj){
        return $this == $obj;
    }
}
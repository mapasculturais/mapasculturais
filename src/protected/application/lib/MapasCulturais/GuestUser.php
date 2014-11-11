<?php
namespace MapasCulturais;

class GuestUser{
    use Traits\Singleton;

    public $id = 0;

    public $profile = null;
    
    function __construct() {
        $this->profile = new \stdClass;
    }

    function is($role){
        return $role == 'guest';
    }
    
    function equals($obj){
        return $this == $obj;
    }
}
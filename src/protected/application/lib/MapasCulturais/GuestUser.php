<?php
namespace MapasCulturais;

class GuestUser{
    use Traits\Singleton;
    
    public $id = 'guest';

    function is($role){
        return $role == 'guest';
    }
}
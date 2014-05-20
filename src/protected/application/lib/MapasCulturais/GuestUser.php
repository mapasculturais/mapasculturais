<?php
namespace MapasCulturais;

class GuestUser{
    use Traits\Singleton;

    public $id = 'guest';

    public $profile = null;

    function is($role){
        return $role == 'guest';
    }
}
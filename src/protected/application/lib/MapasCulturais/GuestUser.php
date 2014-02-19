<?php
namespace MapasCulturais;

class GuestUser{
    public $id = 'guest';

    function is($role){
        return $role == 'guest';
    }
}
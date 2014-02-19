<?php
namespace MapasCulturais\Definitions;

class Role{
    protected $role;

    protected $name;

    public function __construct($role, $name) {
        $this->role = $role;
        $this->name = $name;
    }
}
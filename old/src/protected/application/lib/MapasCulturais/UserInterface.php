<?php
namespace MapasCulturais;

interface UserInterface{
    function is(string $role, $subsite_id = false);

    function isAttorney($action, $user= null);
}
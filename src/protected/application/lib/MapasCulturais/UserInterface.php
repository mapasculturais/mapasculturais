<?php
namespace MapasCulturais;

interface UserInterface{
    function is($role);

    function isAttorney($action, $user= null);
}
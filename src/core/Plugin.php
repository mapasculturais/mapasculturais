<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

abstract class Plugin extends Module {
    static function preInit() {}
    
    static function isPlugin() {
        return true;
    }
}
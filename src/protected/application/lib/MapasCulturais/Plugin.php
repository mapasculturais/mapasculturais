<?php
namespace MapasCulturais;

use MapasCulturais\Traits;

abstract class Plugin extends Module {
    static function isPlugin() {
        return true;
    }
}
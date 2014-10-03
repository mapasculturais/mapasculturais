<?php
namespace SpCultura;
use MapasCulturais\Themes\MapasV1;

class Theme extends MapasV1\Theme{
    static function getThemeFolder() {
        return __DIR__;
    }
}

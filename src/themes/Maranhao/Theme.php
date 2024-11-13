<?php

namespace MapasCulturais\Themes\Maranhao;

use MapasCulturais\App;

class Theme extends \MapasCulturais\Themes\BaseV2\Theme
{
    function _init()
    {
        parent::_init();
        $this->bodyClasses[] = 'maranhao-theme';
    }
}

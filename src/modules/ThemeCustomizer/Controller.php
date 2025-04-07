<?php

namespace ThemeCustomizer;

use MapasCulturais\App;

class Controller extends \MapasCulturais\Controller
{
    function __construct()
    {
        $this->layout = 'theme-customizer';
    }
    
    function GET_index() {
        $this->requireAuthentication();
        $this->render('index');
    }
}

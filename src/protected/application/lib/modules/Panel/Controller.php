<?php

namespace Panel;

class Controller extends \MapasCulturais\Controller
{
    function __construct()
    {
        $this->layout = 'panel';
    }
    
    function GET_index() {
        $this->requireAuthentication();
        $this->render('index');
    }    
}

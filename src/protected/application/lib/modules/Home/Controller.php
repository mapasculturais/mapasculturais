<?php

namespace Home;

class Controller extends \MapasCulturais\Controller
{
    function __construct()
    {
        $this->layout = 'home';
    }
    
    function GET_index() {
        $this->requireAuthentication();
        $this->render('index');
    }

}

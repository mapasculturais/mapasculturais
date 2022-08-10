<?php

namespace Search;

class Controller extends \MapasCulturais\Controller
{
    function __construct()
    {
    }
    
    function GET_index() {
        $this->render('index', []);
    }
    function GET_agents() {
        $this->render('agent', []);
    }
    
}

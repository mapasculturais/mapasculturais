<?php

namespace Search;

class Controller extends \MapasCulturais\Controller
{
    function __construct()
    {
    }
    
    function GET_index() {
        $this->render('search-agent', []);
    }
}

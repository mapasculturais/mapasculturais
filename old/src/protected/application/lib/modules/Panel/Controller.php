<?php

namespace Panel;

use MapasCulturais\App;

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

    function GET_agents() {
        $this->requireAuthentication();
        $this->render('agents');
    }

    function GET_spaces() {
        $this->requireAuthentication();
        $this->render('spaces');
    }

    function GET_projects() {
        $this->requireAuthentication();
        $this->render('projects');
    }

    function GET_events() {
        $this->requireAuthentication();
        $this->render('events');
    }

    function GET_terms() {
        $this->requireAuthentication();
        $this->render('terms');
    }
}

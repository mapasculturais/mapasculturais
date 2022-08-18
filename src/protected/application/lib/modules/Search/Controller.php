<?php

namespace Search;

class Controller extends \MapasCulturais\Controller
{
    
    function GET_index() {
        $this->render('index', []);
    }

    function GET_agents() {
        $this->render('agent', []);
    }
    
    function GET_events() {
        $this->render('event', []);
    }

    function GET_projects() {
        $this->render('project', []);
    }

    function GET_opportunities() {
        $this->render('opportunity', []);
    }

    function GET_spaces() {
        $this->render('space', []);
    }
    
}

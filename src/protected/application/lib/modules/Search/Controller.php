<?php

namespace Search;

use MapasCulturais\App;

class Controller extends \MapasCulturais\Controller
{
    
    function GET_index() {
        $this->render('index', []);
    }

    function GET_agents() {
        $this->render('agent', ['initial_pseudo_query' => []]);
    }
    
    function GET_events() {
        $app = App::i();
        $initial_pseudo_query = [
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', strtotime($app->config['search.events.to']))
        ];
        $this->render('event', ['initial_pseudo_query' => $initial_pseudo_query]);
    }

    function GET_projects() {
        $this->render('project', ['initial_pseudo_query' => []]);
    }

    function GET_opportunities() {
        $this->render('opportunity', ['initial_pseudo_query' => []]);
    }

    function GET_spaces() {
        $this->render('space', ['initial_pseudo_query' => []]);
    }
}

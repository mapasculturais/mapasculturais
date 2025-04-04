<?php

namespace Search;

use MapasCulturais\App;

class Controller extends \MapasCulturais\Controller
{
    
    function GET_index() {
        $this->render('index', []);
    }

    function GET_agents() {
        $app = App::i();

        $initial_pseudo_query = [];

        $app->applyHookBoundTo($this, 'search-agents-initial-pseudo-query', [&$initial_pseudo_query]);

        $this->render('agent', ['initial_pseudo_query' => $initial_pseudo_query]);
    }
    
    function GET_events() {
        $app = App::i();
        $initial_pseudo_query = [
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', strtotime($app->config['search.events.to']))
        ];

        $app->applyHookBoundTo($this, 'search-events-initial-pseudo-query', [&$initial_pseudo_query]);

        $this->render('event', ['initial_pseudo_query' => $initial_pseudo_query]);
    }

    function GET_projects() {
        $app = App::i();

        $initial_pseudo_query = [];

        $app->applyHookBoundTo($this, 'search-projects-initial-pseudo-query', [&$initial_pseudo_query]);

        $this->render('project', ['initial_pseudo_query' => $initial_pseudo_query]);
    }

    function GET_opportunities() {
        $app = App::i();

        $initial_pseudo_query = [];

        $app->applyHookBoundTo($this, 'search-opportunities-initial-pseudo-query', [&$initial_pseudo_query]);

        $this->render('opportunity', ['initial_pseudo_query' => $initial_pseudo_query]);
    }

    function GET_spaces() {
        $app = App::i();

        $initial_pseudo_query = [];

        $app->applyHookBoundTo($this, 'search-spaces-initial-pseudo-query', [&$initial_pseudo_query]);

        $this->render('space', ['initial_pseudo_query' => $initial_pseudo_query]);
    }
}

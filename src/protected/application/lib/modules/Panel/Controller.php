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
        $app = App::i();
        $count = new \stdClass();

        $count->spaces          = $app->controller('space')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->agents          = $app->controller('agent')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->events          = $app->controller('event')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->projects        = $app->controller('project')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->opportunities   = $app->controller('opportunity')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);
        $count->subsite         = $app->controller('subsite')->apiQuery(['@count'=>1]);
        $count->seals           = $app->controller('seal')->apiQuery(['@count'=>1, 'user' => 'EQ(' . $app->user->id . ')']);

        $this->render('index', ['count'=>$count]);
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
}

<?php
namespace MapasCulturais\Controllers;

use \MapasCulturais\App;

/**
 * User Panel Controller
 *
 * By default this controller is registered with the id 'panel'.
 *
 */
class Panel extends \MapasCulturais\Controller {

    /**
     * Render the user panel.
     *
     * This method requires authentication and renders the template 'panel/index'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel');
     * </code>
     *
     */
    function GET_index(){
        $this->requireAuthentication();
        $count = new \stdClass();
        $count->agents = 0;//App::i()->user->agents->count();
        $count->spaces = 0;//count(App::i()->user->spaces);
        $count->events = 0;//count(App::i()->user->events);
        foreach($count as $entity=>$c){
            $count->$entity = str_pad(count(App::i()->user->$entity),2,'0', STR_PAD_LEFT);
        }
        $this->render('index', array('count'=>$count));
    }

    /**
     * Render the agent list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/agents'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'agents');
     * </code>
     *
     */
    function GET_agents(){
        $this->requireAuthentication();
        $entityList = App::i()->user->agents;

        $this->render('agents', array('entityList' => $entityList));
    }


    /**
     * Render the space list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/spaces'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'spaces');
     * </code>
     *
     */
    function GET_spaces(){
        $this->requireAuthentication();
        $this->render('spaces',   array('entityList' => App::i()->user->spaces));
    }

    /**
     * Render the event list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/events'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'events');
     * </code>
     *
     */
    function GET_events(){
        $this->requireAuthentication();
        $this->render('events',   array('entityList' => App::i()->user->events));
    }

    /**
     * Render the project list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/projects'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'projects');
     * </code>
     *
     */
    function GET_projects(){
        $this->requireAuthentication();
        $this->render('projects', array('entityList' => App::i()->user->projects));
    }

    /**
     * Render the contract list of the user panel.
     *
     * This method requires authentication and renders the template 'panel/contract'
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('panel', 'contracts');
     * </code>
     *
     */
    function GET_contracts(){
        $this->requireAuthentication();
        $entityList = App::i()->user->contracts;
        $this->render('contracts', array('entityList' => $entityList));
    }

}
<?php
namespace MapasCulturais\Controllers;

/**
 * Site Controller
 *
 * By default this is the default controller and is registered with the id 'site'
 *
 * By default the home page of the MapasCulturais is the index action of this controller.
 *
 */
class Site extends \MapasCulturais\Controller {

    /**
     * Default action.
     *
     * This action renders the template 'index' of this controller.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('site');
     * </code>
     *
     */
    function GET_index(){
        $this->render('index');
    }

    function GET_search() {
        $this->render('search');
    }

    function GET_page() {
        $this->render('page');
    }
}
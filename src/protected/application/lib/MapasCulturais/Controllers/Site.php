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
        $app = \MapasCulturais\App::i();
        if(key_exists(0, $this->data))
            $page_name = $this->data[0];
        else
            $app->pass();
        
        $filename = ACTIVE_THEME_PATH . 'pages/' . $page_name . '.md';
        
        if(file_exists($filename)){
            $content = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($filename));
            $this->render('page', array('content' => $content));
            return ;
            
            preg_match('#(\<%left(?<left>.*)left%\>)?[[:blank:]]*(\<%right(?<right>.*)right%\>)?(?<content>.*)#s', file_get_contents($filename), $matches);
            die(var_dump($matches));
            
                
                $content = \Michelf\MarkdownExtra::defaultTransform();
                $left = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($content));
                $right = \Michelf\MarkdownExtra::defaultTransform(file_get_contents($content));
            
                $this->render('page', array('content' => $content));
        }else{
            $app->pass();
        }
    }
}
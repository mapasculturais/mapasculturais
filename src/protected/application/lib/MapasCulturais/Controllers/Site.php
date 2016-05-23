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
    use \MapasCulturais\Traits\ControllerAPI;

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
        $view = $app->view;
        
        if(key_exists(0, $this->data))
            $page_name = $this->data[0];
        else
            $app->pass();
        
        
        
        $filename = $app->view->resolveFilename('pages', $page_name . '.md');
        
        if(file_exists($filename)){
            $left_filename = $app->view->resolveFilename('pages', '_left.md');
            $right_filename = $app->view->resolveFilename('pages', '_right.md');
            $left = $view->renderMarkdown(file_get_contents($left_filename));
            $right = $view->renderMarkdown(file_get_contents($right_filename));
            
            $content = '';
            
            $file_content = file_get_contents($filename);
            
            if(preg_match('#\<%left(:after|:before)?(.*)left%\>#s', $file_content, $matches_left)){
                $page_left = $view->renderMarkdown($matches_left[2]);
                
                if($matches_left[1] == ':after')
                    $left =  $left . $page_left;
                elseif($matches_left[1] == ':before')
                    $left =  $page_left . $left;
                else
                    $left = $page_left;
                        
                $file_content = str_replace($matches_left[0], '', $file_content);
            }
            
            if(preg_match('#\<%right(:after|:before)?(.*)right%\>#s', $file_content, $matches_right)){
                $page_right = $view->renderMarkdown($matches_right[2]);
                
                if($matches_right[1] == ':after')
                    $right =  $right . $page_right;
                elseif($matches_right[1] == ':before')
                    $right =  $page_right . $right;
                else
                    $right = $page_right;
                        
                $file_content = str_replace($matches_right[0], '', $file_content);
            }
            
            $content = $view->renderMarkdown($file_content);

            $attrs = ['content' => $content, 'left' => $left, 'right' => $right];
            
            $this->render('page', $attrs);
        }else{
            $app->pass();
        }
    }
}
<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Traits;

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

    function ALL_clearCache() {
        $app = App::i();
        echo '<pre>';
        if ($app->user->is('superAdmin')) {
            i::_e('Deletando cache do site... ');
            $app->cache->flushAll();
            i::_e("ok\n");
        }

        if ($app->user->is('saasSuperAdmin')) {
            i::_e('Deletado cache do multisite...');
            $app->mscache->flushAll();
            i::_e("ok\n");
        }        
        echo '</pre>';
    }
    
    function ALL_error() {
        $app = \MapasCulturais\App::i();

        $status = $this->data['code'];

        if($app->config['app.mode'] !== 'production'){
            if(isset($this->data['e'])){
                throw $this->data['e'];
            }
        }
        if($app->request()->isAjax()){
            $this->errorJson($this->data['e']->getMessage(), $status);
        } else{
            $app->response->setStatus($status);
            $this->render('error-' . $status, $this->data);
        }
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
            $content = $view->renderMarkdown($content);
            $version = $app->getVersion();
            $content .= "<div class='version'>" . sprintf('v%s',$version) . "</div>";
            $attrs = ['content' => $content, 'left' => $left, 'right' => $right];
            
            $this->render('page', $attrs);
        }else{
            $app->pass();
        }
    }

    /**
     * @api {GET} /api/site/version Versão Mapas Culturais 
     * @apiDescription Obtem a versão do Mapas Culturais
     * @apiGroup SITE
     * @apiName getVersion
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "name":"Mapas Culturais",
     *          "version":"v4.0.2",
     *          "git-info":{
     *              "tag":"4.0.2",
     *              "commit hash":"2b4d4e3",
     *              "commit date":"2018-04-25 16:04:04",
     *              "branch":"master"
     *          }
     *      }
     * @apiExample {curl} Exemplo de utilização:
     *   curl -i http://localhost/api/site/version
     */
    function API_version() {
        $app = App::i();
        $data = [];
        $data['name'] = $app->view->dict('site: name', false);
        $data['version'] = $app->getVersion();

        $tagVersion = trim(exec('git describe --tags --abbrev=0'));
        if ($tagVersion != "") {
            $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
            $commitBranch = trim(exec('git rev-parse --abbrev-ref HEAD'));
            $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
            $commitDate->setTimezone(new \DateTimeZone('UTC'));
            $data['git-info'] = ['tag'=>$tagVersion, 'commit hash'=>$commitHash, 'commit date' => $commitDate->format('Y-m-d H:m:s'), 'branch' => $commitBranch];
        }

        $this->json($data);
    }

    function API_info(){
        $app = App::i();
        
        if(!($info = $app->cache->fetch(__METHOD__))){
            $info = [];
            $info['name'] = $app->view->dict('site: name', false);
            $info['description'] = $app->view->dict('site: description', false);
            $info['version'] = $app->getVersion();
    
            $info['timezone'] = date_default_timezone_get();
    
            // $info['plugins'] = array_keys($app->getPlugins());
            // $info['modules'] = array_keys($app->getModules());
    
            $info['agents_count'] = $app->controller('agent')->apiQuery(['@count' => 1]);
            $info['spaces_count'] = $app->controller('space')->apiQuery(['@count' => 1]);
            $info['events_count'] = $app->controller('event')->apiQuery(['@count' => 1]);
            $info['projects_count'] = $app->controller('project')->apiQuery(['@count' => 1]);
            $info['opportunities_count'] = $app->controller('opportunity')->apiQuery(['@count' => 1]);

            $app->cache->save(__METHOD__, $info, 60);
        }    
        $this->json($info);
    }
}

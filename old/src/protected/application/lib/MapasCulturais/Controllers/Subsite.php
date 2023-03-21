<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;
use MapasCulturais\i;

/**
 * Subsite Controller
 *
 * By default this controller is registered with the id 'subsite'.
 *
 *  @property-read \MapasCulturais\Entities\Subsite $requestedEntity The Requested Entity
 *
 */
class Subsite extends EntityController {

    use Traits\ControllerUploads,
        Traits\ControllerTypes,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerSubSiteAdmin,
        Traits\ControllerAPI;

    protected function __construct() {
        parent::__construct();

        $app = App::i();

        $app->hook('entity(Subsite).new', function(){
            $this->entidades_habilitadas = 'Agents;Projects;Spaces;Events;Opportunities';
        });

        $app->hook('PUT(subsite.single):data, POST(subsite.index):data', function(&$data){
            $_dict = [];
            foreach($data as $key => $val){
                if(strpos($key, 'dict:') === 0){
                    unset($data[$key]);
                    $skey = str_replace('+',' ',substr($key, 5));
                    $_dict[$skey] = $val;
                }
            }
            $data['dict'] = $_dict;

        });

        $app->hook('mapasculturais.head', function() use($app){
            $cache_id = "SaaS:themesNamespaces";
            $themes = [];
            if($app->cache->contains($cache_id )){
                $themes = $app->cache->fetch($cache_id);

            } else {
                foreach (scandir(THEMES_PATH) as $ff) {
                    if ($ff != '.' && $ff != '..') {
                        $theme_folder = THEMES_PATH . $ff;
                        if (is_dir($theme_folder) && file_exists($theme_folder . '/Theme.php')){
                            $content = file_get_contents($theme_folder . '/Theme.php');

                            if(preg_match('#namespace +([a-z0-9\\\]+) *;#i', $content, $matches)){
                                $namespace = $matches[1];
                                if(!in_array($namespace, $themes) && class_exists($namespace . "\Theme")){
                                    $themes[] = $namespace;
                                }
                            }
                        }
                    }
                }
                $app->cache->save($cache_id, $themes);
            }

            $themes_order = ['Subsite'];

            foreach($themes as $theme){
                if(!in_array($theme, $themes_order)){
                    $themes_order[] = $theme;
                }
            }

            $themes = [];
            foreach($themes_order as $theme){
                $themes[$theme] = $theme === 'Subsite' ? i::__('Tema Personalizável') : $theme;
            }


            $app->view->jsObject['entity']['definition']['namespace']['type'] = 'select';
            $app->view->jsObject['entity']['definition']['namespace']['options'] = $themes;
            $app->view->jsObject['entity']['definition']['namespace']['optionsOrder'] = $themes_order;
            $app->view->jsObject['entity']['definition']['namespace']['isMetadata'] = true;

            foreach($app->view->_dict() as $key => $def){
                $key = str_replace(' ', '+', $key);
                $app->view->jsObject['entity']['definition']["dict:" . $key] = [
                    'type' => 'text',
                    'isMetadata' => 'true'
                ];
            }


        },1000);
    }

    /**
     * Creates a new Subsite
     *
     * This action requires authentication and outputs the json with the new event or with an array of errors.
     *
     * <code>
     * // creates the url to this action
     * $url = $app->createUrl('subsite');
     * </code>
     */
    public function POST_index($data = null) {
        $app = App::i();

        $app->hook('entity(subsite).insert:before', function() use($app) {
            $this->owner = $app->user->profile;
        });

        parent::POST_index($data);
    }

   
    function GET_single(){
        $app = App::i();
        $app->view->editable = true;
        parent::GET_edit();
    }
    
    function ALL_deleteCache(){
        $this->requireAuthentication();
        
        $subsite = $this->requestedEntity;
        
        $subsite->clearCache();
    }

}

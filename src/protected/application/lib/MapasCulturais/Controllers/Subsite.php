<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities;

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
        Traits\ControllerVerifiable,
        Traits\ControllerSoftDelete,
        Traits\ControllerDraft,
        Traits\ControllerArchive,
        Traits\ControllerAPI;
    
    protected function __construct() {
        parent::__construct();
        
        $app = App::i();
        
        $app->hook('mapasculturais.head', function() use($app){
            $cache_id = "SaaS:themesNamespaces";
            $themes = [];
            if($app->cache->contains($cache_id )){
                $themes = $app->cache->fetch($cache_id);
                
            } else {
                foreach (scandir(THEMES_PATH) as $ff) {
                    if ($ff != '.' && $ff != '..') {
                        $theme_folder = THEMES_PATH . $ff;
                        if (is_dir($theme_folder)){
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
                $themes[$theme] = $theme === 'Subsite' ? 'Tema Personalizável' : $theme;
            }
            
            
            $app->view->jsObject['entity']['definition']['namespace']['type'] = 'select';
            $app->view->jsObject['entity']['definition']['namespace']['options'] = $themes;
            $app->view->jsObject['entity']['definition']['namespace']['optionsOrder'] = $themes_order;
            $app->view->jsObject['entity']['definition']['namespace']['isMetadata'] = true;
            
//            die(var_dump($this->jsObject['entity']['definition']));
            
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
    function POST_index() {
        $app = App::i();

        $app->hook('entity(subsite).insert:before', function() use($app) {
            $this->owner = $app->user->profile;
        });

        parent::POST_index();
    }

}

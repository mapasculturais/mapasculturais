<?php
namespace SubsiteDomainSufix;

use MapasCulturais\App;


class Plugin extends \MapasCulturais\Plugin{
    function getSufix(){
        $sufix = $this->_config['sufix'];
        if (is_callable($sufix)) {
            $sufix = $sufix();
        }
        return $sufix;
    }
    public function _init() {
        $app = App::i();
        
        $sufix = $this->getSufix();
        
        if($sufix[0] !== '.'){
            $sufix = '.' . $sufix;
        }
        $app->hook('entity(Subsite).save:before', function() use($sufix) {
            if(substr($this->url, - strlen($sufix)) != $sufix){
                $this->url = $this->url . $sufix;
            }
        });
        
        $app->hook('view.partial(singles/subsite-header--domains).params', function(&$params) use($sufix){
            $entity = $params['entity'];
            $params['entity_url'] = substr($entity->url,0,strlen($entity->url) - strlen($sufix));
            $params['domain_sufix'] = $sufix;
        });

    }
    
    public function register() { }
    
}
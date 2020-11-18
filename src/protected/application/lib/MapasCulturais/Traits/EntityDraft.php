<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityDraft{

    /**
     * This entity uses Draft
     *
     * @return bool true
     */
    public static function usesDraft(){
        return true;
    }

    function getPublishUrl(){
        return App::i()->createUrl($this->controllerId, 'publish', [$this->id]);
    }

    function getUnpublishUrl(){
        return App::i()->createUrl($this->controllerId, 'unpublish', [$this->id]);
    }
    
    function publish($flush = false){
        $this->checkPermission('publish');
        
        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').publish:before');

        $this->setStatus(self::STATUS_ENABLED);

        $this->save($flush);
        
        if($this->usesFiles()){
            $this->makeFilesPublic();
        }
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').publish:after');
                
    }
    
    function unpublish($flush = false){
        $this->checkPermission('unpublish');
        
        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').unpublish:before');

        $this->setStatus(self::STATUS_DRAFT);

        $this->save($flush);
        
        if($this->usesFiles()){
            $this->makeFilesPrivate();
        }
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').unpublish:after');
                
    }
}
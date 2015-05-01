<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntitySoftDelete{

    /**
     * This entity uses Soft Delete
     *
     * @return bool true
     */
    static function usesSoftDelete(){
        return true;
    }

    function delete($flush = false){
        $this->checkPermission('remove');
        
        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').delete:before');
        
        $entity_class = $this->getClassName();

        $this->status = $entity_class::STATUS_TRASH;
        
        $this->save($flush);
        
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').delete:after');
    }

    function undelete($flush = false){
        $this->checkPermission('undelete');

        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').undelete:before');
        
        $entity_class = $this->getClassName();
        
        $this->status = $entity_class::STATUS_ENABLED;

        $this->save($flush);
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').undelete:after');
    }

    function destroy($flush = false){
        $this->checkPermission('destroy');
        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').destroy:before');
        
        parent::delete($flush);
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').destroy:after');
    }

    function getUndeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'undelete', [$this->id]);
    }
    
    function getDestroyUrl(){
        return App::i()->createUrl($this->controllerId, 'destroy', [$this->id]);
    }
    
    protected function canUserDestroy($user){
        return $user->is('superAdmin');
    }
}
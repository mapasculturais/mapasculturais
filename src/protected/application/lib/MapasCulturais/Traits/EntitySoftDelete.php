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
        
        $em = App::i()->em;
        $em->persist($this);
        if($flush)
            $em->flush();
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').delete:after');
    }

    function undelete($flush = false){
        $this->checkPermission('undelete');

        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').undelete:before');
        
        $entity_class = $this->getClassName();
        
        $this->status = $entity_class::STATUS_ENABLED;

        $em = App::i()->em;
        $em->persist($this);
        if($flush)
            $em->flush();
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').undelete:after');
    }

    function destroy($flush = false){
        parent::delete($flush);
    }

    function getUndeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'undelete', array($this->id));
    }
}
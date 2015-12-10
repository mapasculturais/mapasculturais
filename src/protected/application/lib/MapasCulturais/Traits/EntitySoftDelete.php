<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Implements a soft delete behavior for entities by replacing the delete method and adding an undelete and a destroy method.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\Entity** with property **status**.
 * 
 * @property-read string $undeleteUrl
 * @property-read string $destroyUrl
 * 
 * @hook entity({ENTITY}).delete:before
 * @hook entity({ENTITY}).delete:after
 * @hook entity({ENTITY}).undelete:before
 * @hook entity({ENTITY}).undelete:after
 * @hook entity({ENTITY}).destroy:before
 * @hook entity({ENTITY}).destroy:after
 */
trait EntitySoftDelete{

    /**
     * This entity uses Soft Delete
     *
     * @return bool true
     */
    public static function usesSoftDelete(){
        return true;
    }

    /**
     * Set status to self::STATUS_TRASH
     * 
     * @param bool $flush
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied
     * 
     * @hook **entity({ENTITY}).delete:before**
     * @hook **entity({ENTITY}).delete:after**
     */
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

    /**
     * Set status to self::STATUS_ENABLE
     * 
     * @param bool $flush
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied
     * 
     * @hook **entity({ENTITY}).undelete:before**
     * @hook **entity({ENTITY}).undelete:after**
     */
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

    /**
     * Permanently destroy the entity
     * 
     * @param bool $flush
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied
     * 
     * @hook **entity({ENTITY}).destroy:before**
     * @hook **entity({ENTITY}).destroy:after**
     */
    function destroy($flush = false){
        $this->checkPermission('destroy');
        $hook_class_path = $this->getHookClassPath();
        
        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').destroy:before');
        
        parent::delete($flush);
        
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').destroy:after');
    }

    /**
     * Returns the undelete url
     * 
     * @return string
     */
    function getUndeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'undelete', [$this->id]);
    }
    
    /**
     * Returns the destroy url
     * 
     * @return string
     */
    function getDestroyUrl(){
        return App::i()->createUrl($this->controllerId, 'destroy', [$this->id]);
    }
    
    /**
     * Checks if the user can destroy this entity
     * @param type $user
     * @return type
     */
    protected function canUserDestroy($user){
        return $user->is('superAdmin');
    }
}
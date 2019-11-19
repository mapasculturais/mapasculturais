<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityArchive{

    /**
     * This entity uses Archive
     *
     * @return bool true
     */
    public static function usesArchive(){
        return true;
    }

    function getArchiveUrl(){
        return App::i()->createUrl($this->controllerId, 'archive', [$this->id]);
    }

    function getUnarchiveUrl(){
        return App::i()->createUrl($this->controllerId, 'unarchive', [$this->id]);
    }

    function archive($flush = true){
        $this->checkPermission('archive');
        
        $hook_class_path = $this->getHookClassPath();

        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').archive:before');

        $this->status = self::STATUS_ARCHIVED;

        $this->save($flush);

        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').archive:before');
    }

    function unarchive($flush = true){
        $this->checkPermission('unarchive');
        
        $hook_class_path = $this->getHookClassPath();

        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').unarchive:before');

        $this->status = $this->usesDraft() ? self::STATUS_DRAFT : self::STATUS_ENABLED;

        $this->save($flush);

        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').unarchive:before');
    }
}

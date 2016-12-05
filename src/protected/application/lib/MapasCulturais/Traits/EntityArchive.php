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

        $app = App::i();

        $app->disableAccessControl();

        $this->status = self::STATUS_ARCHIVED;

        $this->save($flush);

        $app->enableAccessControl();
    }

    function unarchive($flush = true){
        $this->checkPermission('unarchive');

        $app = App::i();

        $app->disableAccessControl();

        $this->status = self::STATUS_DRAFT;

        $this->save($flush);

        $app->enableAccessControl();
    }
}

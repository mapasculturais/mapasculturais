<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityDraft{

    /**
     * This entity uses Draft
     *
     * @return bool true
     */
    static function usesDraft(){
        return true;
    }

    function getPublishUrl(){
        return App::i()->createUrl($this->controllerId, 'publish', [$this->id]);
    }

    function getUnpublishUrl(){
        return App::i()->createUrl($this->controllerId, 'unpublish', [$this->id]);
    }
}
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
}
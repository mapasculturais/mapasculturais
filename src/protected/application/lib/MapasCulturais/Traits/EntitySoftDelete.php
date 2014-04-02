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

        $entity_class = $this->getClassName();

        //muda o status para 2, temporariamente
        $this->status = $entity_class::STATUS_TRASH;

        $this->save($flush);

    }

    function undelete($flush = false){
        $this->checkPermission('undelete');

        $entity_class = $this->getClassName();

        //muda o status para 2, temporariamente
        $this->status = $entity_class::STATUS_ENABLED;

        $this->save($flush);
    }

    function destroy($flush = false){
        parent::delete($flush);
    }

    function getUndeleteUrl(){
        return App::i()->createUrl($this->controllerId, 'undelete', array($this->id));
    }
}
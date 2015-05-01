<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

trait EntityNested{

    /**
     * This entity has Nested Objects
     *
     * @return bool true
     */
    static function usesNested(){
        return true;
    }

    function getChildren(){
        $class = get_called_class();
        return $this->fetchByStatus($this->_children, $class::STATUS_ENABLED);
    }

    function setParentId($parent_id){
        if($parent_id)
            $parent = $this->repo()->find($parent_id);
        else
            $parent = null;

        $this->setParent($parent);
    }

    protected $_newParent = false;

    function getParent(){
        if($this->_newParent !== false){
            return $this->_newParent;
        }else{
            return $this->parent;
        }

    }

    function setParent(\MapasCulturais\Entity $parent = null){
        if(is_object($this->parent) && is_object($parent) && $this->parent->equals($parent))
            return;


        $error1 = App::txt('O pai não pode ser o filho.');
        $error2 = App::txt('O pai deve ser do mesmo tipo que o filho.');

        if(!key_exists('parent', $this->_validationErrors))
            $this->_validationErrors['parent'] = [];

        if($parent && $parent->id === $this->id){
            $this->_validationErrors['parent'][] = $error1;
        }elseif(key_exists('parent', $this->_validationErrors) && in_array($error1, $this->_validationErrors['parent'])){
            $key = array_search($error, $this->_validationErrors['parent']);
            unset($this->_validationErrors['parent'][$key]);
        }

        if($parent && $parent->className !== $this->className){
            $this->_validationErrors['parent'][] = $error2;
        }elseif(key_exists('parent', $this->_validationErrors) && in_array($error2, $this->_validationErrors['parent'])){
            $key = array_search($error, $this->_validationErrors['parent']);
            unset($this->_validationErrors['parent'][$key]);
        }

        if(!$this->_validationErrors['parent'])
            unset($this->_validationErrors['parent']);

        $this->_newParent = $parent;
    }

    /**
     * @return array of ids
     */
    public function getChildrenIds(){
        $result = [];
        foreach($this->getChildren() as $child){
            $result[] = $child->id;
            $result = array_merge($result, $child->getChildrenIds());
        }

        return $result;
    }


    protected function _saveNested(){
        if($this->_newParent !== false){
            try{
                if($this->_newParent)
                    $this->_newParent->checkPermission('createChild');

                $this->parent = $this->_newParent;

            }catch(\MapasCulturais\Exceptions\PermissionDenied $e){
                if(!App::i()->isWorkflowEnabled())
                    throw $e;

                $request = new \MapasCulturais\Entities\RequestChildEntity;
                $request->origin = $this;
                $request->destination = $this->_newParent;
                $this->_newParent = false;

                throw new \MapasCulturais\Exceptions\WorkflowRequestTransport($request);
            }
        }
    }
}
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
        $class = $this->getClassName();
        return $this->fetchByStatus($this->_children, $class::STATUS_ENABLED, ['name' => 'ASC']);
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

    function setParent(\MapasCulturais\Entity $parent = null) {
        if (is_object($this->parent) && is_object($parent) && $this->parent->equals($parent))
            return;

        $error_diff_type = App::txt('The parent entity must be of the same type');
        $error_same_obj = App::txt('The parent and the child are the same object');
        $error_circ_ref = App::txt('Circular reference');

        $is_object = $parent && is_object($parent);

        if (!key_exists('parent', $this->_validationErrors)) {
            $this->_validationErrors['parent'] = [];
        }

        if ($is_object) {

            if ($parent->equals($this)) {
                $this->_validationErrors['parent'][] = $error_same_obj;
            } else if ($parent->getClassName() !== $this->getClassName()) {
                $this->_validationErrors['parent'][] = $error_diff_type;
            } else {
                $_parent = $parent;
                while ($_parent = $_parent->getParent()) {
                    if ($_parent->equals($this)) {
                        $this->_validationErrors['parent'][] = $error_circ_ref;
                        continue;
                    }
                }
            }
        }

        if (!$this->_validationErrors['parent'])
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
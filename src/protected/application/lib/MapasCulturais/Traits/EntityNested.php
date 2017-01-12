<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Defines that the entity is nested.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\Entity** with a *many to one* relation **parent** and a *one to many* relation **_children**.
 */
trait EntityNested{

    /**
     * This entity has Nested Objects
     *
     * @return bool true
     */
    static function usesNested(){
        return true;
    }

    /**
     * Returns the children entities.
     * 
     * @return \MapasCulturais\Entities[]
     */
    function getChildren(){
        $class = $this->getClassName();
        return $this->fetchByStatus($this->_children, $class::STATUS_ENABLED, ['name' => 'ASC']);
    }

    /**
     * Set the parent entity by providing the parent id.
     * 
     * @param \MapasCulturais\Entity $parent_id
     */
    function setParentId($parent_id){
        if($parent_id)
            $parent = $this->repo()->find($parent_id);
        else
            $parent = null;

        $this->setParent($parent);
    }

    /**
     * Temporary parent entity.
     * 
     * @var \MapasCulturais\Entity 
     */
    protected $_newParent = false;

    /**
     * Returns the parent entity.
     * 
     * @return \MapasCulturais\Entity
     */
    function getParent(){
        if($this->_newParent !== false){
            return $this->_newParent;
        }else{
            return $this->parent;
        }

    }


    /**
     * Set the parent entity.
     * 
     * @param \MapasCulturais\Entity $parent
     */
    function setParent(\MapasCulturais\Entity $parent = null) {
        if (is_object($this->parent) && is_object($parent) && $this->parent->equals($parent))
            return;

        $error_diff_type = \MapasCulturais\i::__('A entidade mãe deve ser do mesmo tipo');
        $error_same_obj = \MapasCulturais\i::__('As entidades mãe e filha são o mesmo objeto');
        $error_circ_ref = \MapasCulturais\i::__('Referência circular');

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
     * Returns the children ids.
     * 
     * @return array
     */
    public function getChildrenIds(){
        $result = [];
        foreach($this->getChildren() as $child){
            $result[] = $child->id;
            $result = array_merge($result, $child->getChildrenIds());
        }

        return $result;
    }


    /**
     * Tries to replace the parent by the new parent.
     * 
     * If the logged in user can not perform the operation a new RequestChildEntity object is created.
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied
     * @throws \MapasCulturais\Exceptions\WorkflowRequestTransport
     * 
     * @workflow RequestChildEntity
     */
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

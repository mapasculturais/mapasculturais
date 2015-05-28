<?php
namespace MapasCulturais\Traits;

use \MapasCulturais\App;

/**
 * Defines that the entity has types.
 *
 * Use this trait only in subclasses of **\MapasCulturais\Entity** with property **_type** and have types (\MapasCulturais\Definitions\EntityType) registered in App.
 *
 * @property \MapasCulturais\Definitions\EntityType $type The Entity Type.
 * @property-read \MapasCulturais\Definitions\EntityType|null $typeGroup The Entity Type Group if exists.
 *
 */
trait EntityTypes{

    /**
     * This class uses Types
     * 
     * @return true
     */
    public static function usesTypes(){
        return true;
    }

    /**
     * Returns the type of this entity.
     *
     * This method returns the Entity Type Definitions object with the id of the _type property of this entity.
     *
     * @see \MapasCulturais\Definitions\EntityType
     *
     * @return \MapasCulturais\Definitions\EntityType The Entity Type Definitions object
     */
    public function getType(){
        return App::i()->getRegisteredEntityTypeById($this, $this->_type);
    }

    /**
     * Returns the Entity Type Group of this entity if this entity uses group of types.
     *
     * @return \MapasCulturais\Definitions\EntityTypeGroup|null The Entity Type Group object
     */
    public function getTypeGroup(){
        return App::i()->getRegisteredEntityTypeGroupByTypeId($this, $this->_type);
    }

    /**
     * Sets the type of the entity.
     *
     * This methods accepts an interger or an \MapasCulturais\Definitions\EntityType object.
     *
     * @param \MapasCulturais\Definitions\EntityType|int $type Entity Type Definition object or the id of the Entity Type Definiton.
     *
     * @see \MapasCulturais\Definitions\EntityType
     */
    public function setType($type){
        if(is_object($type) && $type instanceof \MapasCulturais\Definitions\EntityType){
                $this->_type = $type->id;

        } else {
            $this->_type = $type;
        }
    }

    public function validateType(){
        return is_numeric($this->_type) && (bool) App::i()->getRegisteredEntityTypeById($this, $this->_type);
    }
}
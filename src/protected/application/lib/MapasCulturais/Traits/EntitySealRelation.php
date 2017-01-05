<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App,
    MapasCulturais\Entities\Seal;


/**
 * Defines that this entity has agents related to it.
 *
 * @property-read \MapasCulturais\Entities\SealRelation[] $relatedSeals The seals related to this entity
 *
 */
trait EntitySealRelation {

    public static function usesSealRelation(){
        return true;
    }

    static function getSealRelationEntityClassName(){
        return self::getClassName() . 'SealRelation';
    }

    function getSealRelations($include_pending_relations = false){
        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $statuses = $include_pending_relations ? [$relation_class::STATUS_ENABLED, $relation_class::STATUS_PENDING] : [$relation_class::STATUS_ENABLED];
        $seal_statuses = [Seal::STATUS_ENABLED, Seal::STATUS_RELATED];
        $relations = [];

        $__relations = $this->_sealRelations;

        if(is_null($__relations)){
            $__relations = App::i()->repo($this->getSealRelationEntityClassName())->findBy(['owner' => $this]);
        }

        foreach($__relations as $ar){
            if(in_array($ar->status, $statuses) && in_array($ar->seal->status, $seal_statuses)){
                $relations[] = $ar;
            }
        }

        return $relations;
    }

    /**
     * Returns the seals related to this entity.
     *
     * @return \MapasCulturais\Entities\Seal[]|\MapasCulturais\Entities\SealRelation[] The Seals related to this entity.
     */
    function getRelatedSeals($return_relations = false, $include_pending_relations = false){
        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $result = [];

        foreach ($this->getSealRelations($include_pending_relations) as $sealRelation)
            $result[] = $return_relations ? $sealRelation : $sealRelation->seal;

        ksort($result);

		return $result;

    }

    function createSealRelation(\MapasCulturais\Entities\Seal $seal, $save = true, $flush = true){
        $app = App::i();
        $relation_class = $this->getSealRelationEntityClassName();
        $relation = new $relation_class;
        $relation->seal = $seal;
        $relation->owner = $this;
        $relation->agent = $app->user->profile;

        if($save)
            $relation->save($flush);

        $this->refresh();
        return $relation;
    }

    function removeSealRelation(\MapasCulturais\Entities\Seal $seal, $flush = true){
        $relation_class = $this->getSealRelationEntityClassName();
        $repo = App::i()->repo($relation_class);
        $relation = $repo->findOneBy(['seal' => $seal, 'owner' => $this]);
        if($relation){
            $relation->delete($flush);
        }

        $this->refresh();
    }

    protected function canUserCreateSealRelation($user){
        $result = $user->is('admin');
        return $result;
    }

    function canUserRemoveSealRelation($user){
        $result = $user->is('admin');
        return $result;
    }
}

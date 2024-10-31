<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App,
    MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Exceptions\PermissionDenied;

/**
 * Defines that this entity has seals related to it.
 *
 * @property-read \MapasCulturais\Entities\Seal[] $relatedSeals The seals related to this entity
 * @property-read \MapasCulturais\Entities\SealRelation[] $sealRelations
 * @property-read string $sealRelationEntityClassNamerelatedSeals
 * 
 */
trait EntitySealRelation {

    public static function usesSealRelation(){
        return true;
    }

    static function getSealRelationEntityClassName(){
        return self::getClassName() . 'SealRelation';
    }

    function getLockedFields() {
        /** @var \MapasCulturais\Entity $this */

        $app = App::i();

        $cache_id = "{$this}:lockedFields";

        if($app->rcache->contains($cache_id)) {
            return $app->rcache->fetch($cache_id);
        }

        $lockedFields = [];

        foreach($this->sealRelations as $seal_relation) {
            $seal = $seal_relation->seal;
            foreach($seal->lockedFields ?: [] as $entity_field) {
                if(preg_match("#{$this->controllerId}\.(.*)#", $entity_field, $match)) {
                    $lockedFields[] = $match[1];
                }
            }
        }

        $app->applyHookBoundTo($this, "{$this->hookPrefix}.lockedFields", [&$lockedFields]);
    
        $app->rcache->save($cache_id, $lockedFields);

        return $lockedFields;
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
        $app = App::i();

        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $result = [];
       
        foreach ($this->getSealRelations($include_pending_relations) as $sealRelation) {
            $result[$sealRelation->id] = $return_relations ? $sealRelation : $sealRelation->seal;
        }
        
        rsort($result);
        return $result;
    }

    function createSealRelation(\MapasCulturais\Entities\Seal $seal, $save = true, $flush = true, Agent $agent = null){
        $app = App::i();
        
        $seal->checkPermission('@control');
        
        $relation_class = $this->getSealRelationEntityClassName();
        $relation = new $relation_class;
        $relation->seal = $seal;
        $relation->owner = $this;
        $relation->agent = $agent ?: $app->user->profile->refreshed();

        if($save){
            $relation->save($flush);
        }
        
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
        $result = $this->canUser('@control', $user);
        try {
            $user_seals = $user->hasControlSeals;
        } catch (PermissionDenied $th) {
            $user_seals = [];
        }

        return $user->is('admin') || $result && $user_seals;
    }

    function canUserRemoveSealRelation($user){
        if ($user->is('admin')) {
            return true;
        }
        
        $result = false;
        if($this->canUser('@control', $user)){
            if($entity_seals = $this->relatedSeals){

                try {
                    $user_seals = $user->hasControlSeals;
                } catch (PermissionDenied $th) {
                    $user_seals = [];
                }

                foreach($user_seals as $seal) {
                    if(array_search($seal, $entity_seals) !== false) {
                        $result = true;
                    }
                }
            }
        }
        return $result;
    }

    function getRequestSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'requestsealrelation', [$idRelation]);
    }

    function getRenewSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'renewsealrelation', [$idRelation]);
    }
}

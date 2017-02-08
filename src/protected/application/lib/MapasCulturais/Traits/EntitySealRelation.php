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
        $app = App::i();

        if(!$this->id)
            return [];

        $relation_class = $this->getSealRelationEntityClassName();
        if(!class_exists($relation_class))
            return [];

        $result = [];

        $now = new \DateTime;
        $diff = 0;
        foreach ($this->getSealRelations($include_pending_relations) as $sealRelation) {
            $result[$sealRelation->id] = $return_relations ? $sealRelation : $sealRelation->seal;
            if(isset($result[$sealRelation->id]->validateDate) && $result[$sealRelation->id]->validateDate) {
                $diff = ($result[$sealRelation->id]->validateDate->format("U") - $now->format("U"))/86400;
                $result[$sealRelation->id]->validateDate = $result[$sealRelation->id]->validateDate->format("d/m/Y");
                if($diff <= 0) { // Expired
                    $result[$sealRelation->id]->{'toExpire'} = 0;
                    $result[$sealRelation->id]->{'requestSealRelationUrl'} = $this->getRequestSealrelationUrl($sealRelation->id);
                    $result[$sealRelation->id]->{'renewSealRelationUrl'} = $this->getRenewSealRelationUrl($sealRelation->id);
                }elseif($diff <= $app->config['notifications.seal.toExpire']) { // To Expire
                    $result[$sealRelation->id]->{'toExpire'} = 1;
                    $result[$sealRelation->id]->{'requestSealRelationUrl'} = $this->getRequestSealRelationUrl($sealRelation->id);
                    $result[$sealRelation->id]->{'renewSealRelationUrl'} = $this->getRenewSealRelationUrl($sealRelation->id);
                } else {
                    $result[$sealRelation->id]->{'toExpire'} = 2; // Not To Expired
                }
            } else {
                $result[$sealRelation->id]->{'toExpire'} = 2; // Not To Expired
            }

            $result[$sealRelation->id]->ownerSealUserId = $sealRelation->seal->owner->userId; 

            if(is_null($result[$sealRelation->id]->renovation_request)) {
                $result[$sealRelation->id]->renovation_request = false;    
            }
        }

        rsort($result);

        return $result;

    }

    function createSealRelation(\MapasCulturais\Entities\Seal $seal, $save = true, $flush = true){
        $app = App::i();
        
        $seal->checkPermission('@control');
        
        $relation_class = $this->getSealRelationEntityClassName();
        $relation = new $relation_class;
        $relation->seal = $seal;
        $relation->owner = $this;
        $relation->agent = $app->user->profile;

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
        return $result;
    }

    function canUserRemoveSealRelation($user){
        $this->canUser('@control', $user);
        return $result;
    }

    function getRequestSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'requestsealrelation', [$idRelation]);
    }
    function getRenewSealRelationUrl($idRelation){
        return App::i()->createUrl($this->controllerId, 'renewsealrelation', [$idRelation]);
    }
}

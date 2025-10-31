<?php
namespace MapasCulturais\Traits;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Entities\Opportunity;
/**
 * Defines that the entity has related opportunities.
 *
 * @property-read \MapasCulturais\Entities\Opportunity[] $opportunities
 * @property-read string $opportunityClassName
 */
trait EntityOpportunities {
    
    #[ORM\OneToMany(targetEntity: self::class . "Opportunity", mappedBy: "ownerEntity", cascade: ["remove"], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "object_id", onDelete: "CASCADE")]
    protected $_relatedOpportunities;

    /**
     * This entity has related opportunities.
     * 
     * @return true
     */
    public static function usesOpportunities(){
        return true;
    }
    
    static function getOpportunityClassName() {
        return self::getClassName() . 'Opportunity';
    }
    
    public function getOpportunities($status = Opportunity::STATUS_ENABLED){
        $result = [];
        
        if(!$this->_relatedOpportunities){
            return [];
        }
        
        foreach($this->_relatedOpportunities as $opp){
            if($opp->status >= $status){
                $result[] = $opp;
            }
        }
        
        return $result;
    }
    
    public function getActiveOpportunities(){
        
    }
}
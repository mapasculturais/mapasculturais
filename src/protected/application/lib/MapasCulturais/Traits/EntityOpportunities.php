<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
/**
 * Defines that the entity has related opportunities.
 *
 */
trait EntityOpportunities{


    /**
     * This entity has related opportunities.
     * 
     * @return true
     */
    public static function usesOpportunities(){
        return true;
    }
    
    public function getOpportunityClassName() {
        return $this->getClassName() . 'Opportunity';
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
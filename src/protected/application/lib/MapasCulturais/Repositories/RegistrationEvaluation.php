<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class RegistrationEvaluation extends \MapasCulturais\Repository{
    /**
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @param \MapasCulturais\Entities\User $user
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByOpportunityAndUser(\MapasCulturais\Entities\Opportunity $opportunity, $user){
        if($user->is('guest') || !$opportunity->id){
            return [];
        }

        $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\RegistrationEvaluation e
                JOIN e.registration r
            WHERE
                r.opportunity = :opportunity AND
                e.user = :user";

        $q = $this->_em->createQuery($dql);

        $q->setParameters([
            'opportunity' => $opportunity,
            'user' => $user
        ]);

        return $q->getResult();
    }

    /**
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByOpportunity(\MapasCulturais\Entities\Opportunity $opportunity){
        $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\RegistrationEvaluation e
                JOIN e.registration r
            WHERE
                r.opportunity = :opportunity";

        $q = $this->_em->createQuery($dql);

        $q->setParameters([
            'opportunity' => $opportunity
        ]);

        return $q->getResult();
    }
}

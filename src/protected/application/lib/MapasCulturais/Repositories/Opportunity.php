<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;
use MapasCulturais\Entities\RegistrationEvaluation;

class Opportunity extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    public function findAlreadyEvaluatedBy( $user) {
        $dql ="            
            SELECT o
            FROM MapasCulturais\Entities\Opportunity o
            WHERE
            o.id IN  ( SELECT op.id
                        FROM MapasCulturais\Entities\RegistrationEvaluation re
                        JOIN re.registration r
                        JOIN r.opportunity op
                        WHERE re.user = :user AND re.status =  :status )
        ";       

        $q = $this->_em->createQuery($dql);
        $q->setParameters(['user' => $user,  'status' => RegistrationEvaluation::STATUS_SENT]);
        return $q->getResult();
    }
}


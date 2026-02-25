<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class RegistrationEvaluation extends \MapasCulturais\Repository{
    /**
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @param \MapasCulturais\Entities\User $user
     * @param string $committee
     * @return \MapasCulturais\Entities\RegistrationEvaluation[]
     */
    function findByOpportunityAndUser(\MapasCulturais\Entities\Opportunity $opportunity, $user, ?string $committee = null){
        if($user->is('guest') || !$opportunity->id){
            return [];
        }

        $params = [
            'opportunity' => $opportunity, 
            'user' => $user
        ];

        if($committee){
            $committee_where = " AND e.committee = :committee";
            $params['committee'] = $committee;
        }

        $dql = "
            SELECT
                e
            FROM
                MapasCulturais\Entities\RegistrationEvaluation e
                JOIN e.registration r
            WHERE
                r.opportunity = :opportunity AND
                e.user = :user {$committee_where}";

        $q = $this->_em->createQuery($dql);

        $q->setParameters($params);

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

    /**
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @param array $users
     * @param array $status
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByOpportunityAndUsersAndStatus(\MapasCulturais\Entities\Opportunity $opportunity, $users = null, $status = null){

        $dql = "  SELECT e 
                  FROM MapasCulturais\Entities\RegistrationEvaluation e
                  JOIN e.registration r
                  WHERE r.opportunity = :opportunity ";

        $params = ['opportunity' => $opportunity];

        if (!is_null($users)){
            $dql .= "AND e.user IN (:uids)";
            $params['uids'] =  $users;
        }

        if (!is_null($status)){
            $dql .= "AND e.status IN (:status)";
            $params['status'] =  $status;
        }

        $query = $this->_em->createQuery($dql);

        $query->setParameters($params);

        return $query->getResult();
    }

    /**
     *
     * @param \MapasCulturais\Entities\Registration $registration
     * @param array $users
     * @param array $status
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByRegistrationAndUsersAndStatus(\MapasCulturais\Entities\Registration $registration, $users = null, $status = null){

        $dql = "  SELECT e 
                  FROM MapasCulturais\Entities\RegistrationEvaluation e 
                  WHERE e.registration = :registration ";

        $params = ['registration' => $registration];

        if (!is_null($users)){
            $dql .= "AND e.user IN (:uids)";
            $params['uids'] =  $users;
        }

        if (!is_null($status)){
            $dql .= "AND e.status IN (:status)";
            $params['status'] =  $status;
        }

        $query = $this->_em->createQuery($dql);

        $query->setParameters($params);

        return $query->getResult();
    }
}

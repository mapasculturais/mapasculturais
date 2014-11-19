<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\Traits;

class Registration extends \MapasCulturais\Repository{
    /**
     *
     * @param \MapasCulturais\Entities\Project $project
     * @param \MapasCulturais\Entities\User $user
     * @return \MapasCulturais\Entities\Registration[]
     */
    function findByProjectAndUser(\MapasCulturais\Entities\Project $project, $user){
        if($user->is('guest')){
            return array();
        }

        $dql = "
            SELECT
                r
            FROM
                MapasCulturais\Entities\Registration r
                LEFT JOIN  MapasCulturais\Entities\RegistrationAgentRelation rar WITH rar.owner = r
            WHERE
                r.project = :project AND
                (
                    r.owner IN (:agents) OR
                    rar.agent IN (:agents)
                )";

        $q = $this->_em->createQuery($dql);

        $q->setParameters(array(
            'project' => $project,
            'agents' => $user->agents ? $user->agents->toArray() : array(-1)
        ));

        return $q->getResult();
    }
}
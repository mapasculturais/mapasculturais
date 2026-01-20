<?php
namespace MapasCulturais\Repositories;

use Doctrine\ORM\Query;
use MapasCulturais\App;
use MapasCulturais\DateTime;
use MapasCulturais\Entities\Project;
use MapasCulturais\Traits;

class Opportunity extends \MapasCulturais\Repository{
    use Traits\RepositoryKeyword,
        Traits\RepositoryAgentRelation;

    function findOpportunitiesWithDateByIds($opportunitiesIds, $open = false) {
        $today = new DateTime('now');
        $params = [
            'opportunitiesIds' => $opportunitiesIds,
           
        ];
        $extraQuery = "";
        if ($open){
            $params['today'] = $today;
            $extraQuery = " AND op.registrationFrom <= :today AND 
            op.registrationTo >= :today";
        }
        $query = $this->_em->createQuery("
        SELECT 
            op.id, op.name, op.registrationFrom, op.registrationTo
        FROM
            MapasCulturais\Entities\Opportunity op
        WHERE 
            op.id in (:opportunitiesIds) AND
            op.status = 1 ". $extraQuery
        );
    
       

        $query->setParameters($params);

        return $query->getArrayResult();
    }

    function findByProjectAndOpportunityMeta(Project $project, $key, $value, $status = 1) {
        $projectsIds = $project->getChildrenIds();
        $projectsIds[] = $project->id;

        $query = $this->_em->createQuery("
        SELECT 
            po
        FROM
            MapasCulturais\Entities\OpportunityMeta om
            JOIN MapasCulturais\Entities\ProjectOpportunity po WITH om.owner=po
            JOIN po.ownerEntity oe
        WHERE 
            po.status = :status AND 
            om.key=:key AND 
            om.value=:value AND 
            oe.id in (:projectsIds)
        ");
    
        $params = [
            'status' => $status,
            'projectsIds' => $projectsIds,
            'key'=>$key,
            'value'=>$value,
        ];

        $query->setParameters($params);

        return $query->getResult();
    }

    /**
     * Retornar as oportunidades que o avaliador pode avaliar
     * 
     * @param int $valuer_user_id 
     * @return Opportunity[]|array
     */
    function findValuerOpportunities(int $valuer_user_id, $only_ids = false, $hydration_mode = Query::HYDRATE_OBJECT): array {
        $app = App::i();

        $conn = $app->em->getConnection();

        $opportunity_ids = $conn->fetchFirstColumn("
            select distinct(opportunity_id) from evaluations where valuer_user_id = :valuer_user_id;
        ", [
            'valuer_user_id' => $valuer_user_id,
        ]);
        if (empty($opportunity_ids)) {
            return [];
        }

        if($only_ids) {
            return $opportunity_ids;
        }
        
        $query = $app->em->createQuery("
            SELECT 
                o
            FROM
                MapasCulturais\Entities\Opportunity o
            WHERE 
                o.id in (:opportunity_ids)
                AND o.status = 1 OR o.status = -1"

        );

        $query->setParameters([
            'opportunity_ids' => $opportunity_ids,
        ]);

        return $query->getResult($hydration_mode);
    }
}


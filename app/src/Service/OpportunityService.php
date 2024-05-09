<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\OpportunityRepository;
use MapasCulturais\Entities\Opportunity;

class OpportunityService
{
    protected OpportunityRepository $repository;

    public const FILE_TYPES = '/src/conf/opportunity-types.php';

    public function __construct()
    {
        $this->repository = new OpportunityRepository();
    }

    public function getTypes(): array
    {
        $typesFromConf = (require dirname(__DIR__, 3).'/src/conf/opportunity-types.php')['items'] ?? [];

        return array_map(
            fn ($key, $item) => ['id' => $key, 'name' => $item['name']],
            array_keys($typesFromConf),
            $typesFromConf
        );
    }

    public function create($data): Opportunity
    {
        $opportunity = new Opportunity();

        $opportunity->setType($data['opportunityType']);
        $opportunity->setName($data['title']);
        $opportunity->terms['area'] = $data['terms']['area'];


        if (isset($data['project'])) {
            $opportunity->setObjectType("MapasCulturais\Entities\Project");
            $opportunity->setProject($data['project']);
        }
        if (isset($data['event'])) {
            $opportunity->setObjectType("MapasCulturais\Entities\Event");
            $opportunity->setEvent($data['event']);
        }
        if (isset($data['space'])) {
            $opportunity->setObjectType("MapasCulturais\Entities\Space");
            $opportunity->setSpace($data['space']);
        }
        if (isset($data['agent'])) {
            $opportunity->setObjectType("MapasCulturais\Entities\Agent");
            $opportunity->setAgent($data['agent']);
        }

        $this->repository->save($opportunity);

        return $opportunity;
    }
}

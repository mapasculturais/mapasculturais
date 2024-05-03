<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AgentRepository;
use MapasCulturais\Entities\Agent;

class AgentService
{
    protected AgentRepository $repository;
    public const FILE_TYPES = '/src/conf/agent-types.php';

    public function __construct()
    {
        $this->repository = new AgentRepository();
    }

    public function getTypes(): array
    {
        $typesFromConf = (require dirname(__DIR__, 3).self::FILE_TYPES)['items'] ?? [];

        return array_map(
            fn ($key, $item) => ['id' => $key, 'name' => $item['name']],
            array_keys($typesFromConf),
            $typesFromConf
        );
    }

    public function create($data): Agent
    {
        $agent = new Agent();
        $agent->setName($data->name);
        $agent->setShortDescription($data->shortDescription);
        $agent->setType($data->type);
        $agent->terms['area'] = $data->terms['area'];
        $agent->saveTerms();

        $this->repository->save($agent);

        return $agent;
    }
}

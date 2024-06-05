<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\EntityStatusEnum;
use App\Exception\ResourceNotFoundException;
use App\Repository\AgentRepository;
use MapasCulturais\Entities\Agent;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AgentService
{
    protected AgentRepository $repository;
    private SerializerInterface $serializer;
    public const FILE_TYPES = '/src/conf/agent-types.php';

    public function __construct()
    {
        $this->repository = new AgentRepository();
        $this->serializer = new Serializer([new ObjectNormalizer()]);
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

    /**
     * @throws ResourceNotFoundException
     */
    public function update(int $id, object $data): Agent
    {
        $agentFromDB = $this->repository->find($id);

        if (null === $agentFromDB || EntityStatusEnum::TRASH->getValue() === $agentFromDB->status) {
            throw new ResourceNotFoundException('Agent not found');
        }

        $agentUpdated = $this->serializer->denormalize(
            data: $data,
            type: Agent::class,
            context: ['object_to_populate' => $agentFromDB]
        );

        $agentUpdated->saveTerms();
        $this->repository->save($agentUpdated);

        return $agentUpdated;
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

    /**
     * @throws ResourceNotFoundException
     */
    public function discard(int $id): void
    {
        $agent = $this->repository->find($id);

        if (null === $agent || EntityStatusEnum::TRASH->getValue() === $agent->status) {
            throw new ResourceNotFoundException('Agent not found');
        }

        $this->repository->softDelete($agent);
    }
}

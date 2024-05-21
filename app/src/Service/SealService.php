<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\EntityStatusEnum;
use App\Exception\ResourceNotFoundException;
use App\Repository\AgentRepository;
use App\Repository\ProjectRepository;
use App\Repository\SealRepository;
use MapasCulturais\Entities\Seal;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SealService extends AbstractService
{
    private SealRepository $sealRepository;
    private AgentRepository $agentRepository;
    protected ProjectRepository $projectRepository;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
        $this->serializer = new Serializer([new ObjectNormalizer()]);
        $this->sealRepository = new SealRepository();
        $this->agentRepository = new AgentRepository();
    }

    public function create(array $data): mixed
    {
        $seal = $this->serializer->denormalize($data, Seal::class);
        $this->setProperty($seal, 'owner', $this->agentRepository->find(1));
        $this->sealRepository->save($seal);

        return $seal;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function delete(int $id): true
    {
        $seal = $this->sealRepository->find($id);

        if (null === $seal || EntityStatusEnum::TRASH->getValue() === $seal->status) {
            throw new ResourceNotFoundException('Seal not found');
        }

        $this->sealRepository->softDelete($seal);

        return true;
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function update(int $id, object $data): Seal
    {
        $sealFromDB = $this->sealRepository->find($id);

        if (null === $sealFromDB || EntityStatusEnum::TRASH->getValue() === $sealFromDB->status) {
            throw new ResourceNotFoundException('Seal not found');
        }

        $sealUpdated = $this->serializer->denormalize(
            data: $data,
            type: Seal::class,
            context: ['object_to_populate' => $sealFromDB]
        );

        $this->sealRepository->save($sealUpdated);

        return $sealUpdated;
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\SpaceRepository;
use MapasCulturais\Entities\Space;

class SpaceService
{
    protected SpaceRepository $repository;
    public const FILE_TYPES = '/src/conf/space-types.php';

    public function __construct()
    {
        $this->repository = new SpaceRepository();
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

    public function create($data): Space
    {
        $space = new Space();

        $space->setName($data->name);
        $space->setShortDescription($data->shortDescription);
        $space->setType($data->type);
        $space->terms['area'] = $data->terms['area'];
        $space->saveTerms();

        $this->repository->save($space);

        return $space;
    }
}

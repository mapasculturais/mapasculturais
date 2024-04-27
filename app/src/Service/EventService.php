<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\EventRepository;
use MapasCulturais\Entities\Event;

class EventService
{
    protected EventRepository $repository;
    public const FILE_TYPES = '/src/conf/event-types.php';

    public function __construct()
    {
        $this->repository = new EventRepository();
    }

    public function getTypes(): array
    {
        $typesFromConf = (require dirname(__DIR__, 3).self::FILE_TYPES)['items'] ?? [];

        return array_map(fn ($key, $item) => ['id' => $key, 'name' => $item['name']], array_keys($typesFromConf), $typesFromConf);
    }

    public function create($data): Event
    {
        $event = new Event();

        $event->setName($data->name);
        $event->setShortDescription($data->shortDescription);
        $event->setLongDescription($data->longDescription);

        $event->setMetadata('classificacaoEtaria', $data->classificacaoEtaria);

        $event->terms['linguagem'] = $data->terms['linguagem'];

        $this->repository->save($event);

        return $event;
    }
}

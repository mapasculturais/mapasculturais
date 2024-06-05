<?php

declare(strict_types=1);

namespace App\Request;

use App\Enum\EntityStatusEnum;
use App\Repository\EventRepository;
use Exception;
use MapasCulturais\Entities\Event;
use Symfony\Component\HttpFoundation\Request;

class EventRequest
{
    protected Request $request;
    private $repository;

    public function __construct()
    {
        $this->request = new Request();
        $this->repository = new EventRepository();
    }

    public function validatePost(): array
    {
        $jsonData = $this->request->getContent();
        $data = json_decode($jsonData, true);

        $requiredFields = ['name', 'shortDescription', 'classificacaoEtaria', 'terms'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception(ucfirst($field).' is required.');
            }
        }

        if (!isset($data['terms']['linguagem']) || !is_array($data['terms']) || !$data['terms']['linguagem']) {
            throw new Exception('The "terms" field must be an object with a property "linguagem" which is an array.');
        }

        return $data;
    }

    public function validateEventExistent(array $params): Event
    {
        $event = $this->repository->find((int) $params['id']);

        if (!$event || EntityStatusEnum::TRASH->getValue() === $event->status) {
            throw new Exception('Event not found or already deleted.');
        }

        return $event;
    }

    public function validateUpdate(): array
    {
        $jsonData = $this->request->getContent();
        $data = json_decode($jsonData, true);

        return $data;
    }
}

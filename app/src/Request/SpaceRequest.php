<?php

declare(strict_types=1);

namespace App\Request;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class SpaceRequest
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    public function validatePost(): array
    {
        $jsonData = $this->request->getContent();
        $data = json_decode($jsonData, true);

        $requiredFields = ['name', 'terms', 'type', 'shortDescription'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception(ucfirst($field).' is required');
            }
        }

        if (!is_array($data['terms']) || !is_array($data['terms']['area'])) {
            throw new Exception('the terms field must be an object with a property "area" which is an array');
        }

        return $data;
    }

    public function validateUpdate(): array
    {
        $jsonData = $this->request->getContent();
        $data = json_decode($jsonData, true);

        return $data;
    }
}

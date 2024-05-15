<?php

declare(strict_types=1);

namespace App\Request;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class AgentRequest
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

        $requiredFields = ['type', 'name', 'shortDescription', 'terms'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception(ucfirst($field).' is required');
            }
        }

        if (!isset($data['type']) || !is_array($data['terms']) || !is_array($data['terms']['area'])) {
            throw new Exception('The "terms" field must be an object with a property "area" which is an array.');
        }

        return $data;
    }

    public function validateUpdate(): array
    {
        return json_decode(
            json: $this->request->getContent(),
            associative: true
        );
    }
}

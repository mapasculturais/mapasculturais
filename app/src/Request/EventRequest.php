<?php

declare(strict_types=1);

namespace App\Request;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class EventRequest
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

        $requiredFields = ['name', 'shortDescription', 'longDescription', 'classificacaoEtaria', 'terms'];
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
}

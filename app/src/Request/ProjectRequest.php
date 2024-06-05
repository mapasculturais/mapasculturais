<?php

declare(strict_types=1);

namespace App\Request;

use Exception;
use Symfony\Component\HttpFoundation\Request;

class ProjectRequest
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

        $requiredFields = ['name', 'shortDescription', 'type'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception(ucfirst($field).' is required');
            }
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

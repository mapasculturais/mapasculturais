<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class SealRequest
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

        return $data;
    }

    public function validatePatch(): array
    {
        return json_decode(
            json: $this->request->getContent(),
            associative: true
        );
    }
}

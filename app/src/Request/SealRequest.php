<?php

declare(strict_types=1);

namespace App\Request;

use App\DTO\SealDto;
use App\Exception\ValidatorException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;

class SealRequest
{
    protected Request $request;

    protected Serializer $serializer;

    public function __construct()
    {
        $this->request = new Request();
        $this->serializer = new Serializer([new ObjectNormalizer()]);
    }

    public function validatePost(): array
    {
        $data = json_decode(
            json: $this->request->getContent(),
            associative: true
        );

        $seal = $this->serializer->denormalize($data, SealDto::class);

        $validation = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validation->validate($seal, groups: ['post']);

        if (0 < count($violations)) {
            throw new ValidatorException(violations: $violations);
        }

        return $data;
    }

    public function validatePatch(): array
    {
        $data = json_decode(
            json: $this->request->getContent(),
            associative: true
        );

        $seal = $this->serializer->denormalize($data, SealDto::class);

        $validation = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validation->validate($seal, groups: ['patch']);

        if (0 < count($violations)) {
            throw new ValidatorException(violations: $violations);
        }

        return $data;
    }
}

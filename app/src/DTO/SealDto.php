<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Sequentially;
use Symfony\Component\Validator\Constraints\Type;

final class SealDto
{
    #[Sequentially([new NotBlank(), new Type('string')], groups: ['post'])]
    #[Type('string', groups: ['patch'])]
    public mixed $name;

    #[Sequentially([new NotBlank(), new Type('string')], groups: ['post'])]
    #[Type('string', groups: ['patch'])]
    public mixed $shortDescription;

    #[Sequentially([new NotBlank(), new Type('string')], groups: ['post'])]
    #[Type('string', groups: ['patch'])]
    public mixed $longDescription;

    #[Sequentially([new NotBlank(), new Type('integer')], groups: ['post'])]
    #[Type('integer', groups: ['patch'])]
    public mixed $validPeriod;
}

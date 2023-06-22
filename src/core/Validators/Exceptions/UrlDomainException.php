<?php
namespace MapasCulturais\Validators\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

final class UrlDomainExeption extends ValidationException {
    
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid url',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a valid url',
        ],
    ];
}
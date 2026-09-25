<?php
namespace MapasCulturais\Validators\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Exceção lançada quando a validação de URL de domínio falha
 * 
 * Esta exceção é usada pela regra UrlDomain para indicar
 * que uma URL não pertence ao domínio especificado.
 * 
 * @package MapasCulturais\Validators\Exceptions
 */
final class UrlDomainExeption extends ValidationException {
    
    /**
     * @var array Templates de mensagens de erro
     * @access protected
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid url',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a valid url',
        ],
    ];
}
<?php
namespace MapasCulturais\Validators\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Exceção lançada quando a validação de telefone brasileiro falha
 * 
 * Esta exceção é usada pela regra BrPhone para indicar
 * que um valor não está no formato correto de telefone brasileiro.
 * 
 * @package MapasCulturais\Validators\Exceptions
 */
final class BrPhoneException extends ValidationException {
    
    /**
     * @var array Templates de mensagens de erro
     * @access protected
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid brazilian telephone number',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a valid brazilian telephone number',
        ],
    ];
}
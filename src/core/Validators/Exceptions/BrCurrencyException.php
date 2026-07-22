<?php
namespace MapasCulturais\Validators\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

/**
 * Exceção lançada quando a validação de moeda brasileira falha
 * 
 * Esta exceção é usada pela regra BrCurrency para indicar
 * que um valor não está no formato correto de moeda brasileira.
 * 
 * @package MapasCulturais\Validators\Exceptions
 */
final class BrCurrencyException extends ValidationException {
    
    /**
     * @var array Templates de mensagens de erro
     * @access protected
     */
    protected $defaultTemplates = [
        self::MODE_DEFAULT => [
            self::STANDARD => '{{name}} must be a valid brazilian currency',
        ],
        self::MODE_NEGATIVE => [
            self::STANDARD => '{{name}} must not be a valid brazilian currency',
        ],
    ];
}
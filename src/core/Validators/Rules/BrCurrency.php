<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Regra de validação para moeda brasileira (Real)
 * 
 * Esta regra valida se um valor está no formato correto de moeda brasileira,
 * aceitando tanto números quanto strings formatadas como R$.
 * 
 * @package MapasCulturais\Validators\Rules
 */
final class BrCurrency extends AbstractRule {
    
    /**
     * Valida se o input está no formato de moeda brasileira
     * 
     * Aceita:
     * - Números (inteiros ou decimais)
     * - Strings no formato brasileiro: 1.234,56
     * 
     * @param mixed $input Valor a ser validado
     * @return bool True se válido, False caso contrário
     */
    public function validate($input): bool {
        if(is_numeric($input)) {
            return true;
        } 

        return (bool) preg_match("#^((([1-9]\d?\d?)(\.\d{3})*)|([1-9]\d*)|0),(\d{2})$#", trim((string) $input));
    }
}
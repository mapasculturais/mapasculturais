<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Regra de validação para telefone brasileiro
 * 
 * Esta regra valida se um valor está no formato correto de telefone brasileiro,
 * aceitando diferentes formatos comuns no Brasil.
 * 
 * @package MapasCulturais\Validators\Rules
 */
final class BrPhone extends AbstractRule {
    
    /**
     * Valida se o input está no formato de telefone brasileiro
     * 
     * Aceita formatos como:
     * - (11) 9999-9999
     * - 11 9999-9999
     * - 1199999999
     * - (11) 99999-9999 (celular)
     * 
     * @param mixed $input Valor a ser validado
     * @return bool True se válido, False caso contrário
     */
    public function validate($input): bool {
        return (bool) preg_match("#^\(?\d{2}\)?[ ]*\d{4,5}-?\d{4}$#", trim((string) $input));
    }
}
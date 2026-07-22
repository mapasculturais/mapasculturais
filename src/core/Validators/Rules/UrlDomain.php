<?php
namespace MapasCulturais\Validators\Rules;

use Respect\Validation\Rules\AbstractRule;

/**
 * Regra de validação para URLs de domínio específico
 * 
 * Esta regra valida se uma URL pertence a um domínio específico.
 * Útil para garantir que URLs apontem para domínios permitidos.
 * 
 * @package MapasCulturais\Validators\Rules
 */
final class UrlDomain extends AbstractRule {

    /**
     * @var string Domínio a ser validado
     * @access private
     */
    private string $domain;

    /**
     * Construtor da regra
     * 
     * @param string $domain Domínio a ser validado
     */
    public function __construct(string $domain) {
        $this->domain = $domain;
    }

    /**
     * Valida se o input é uma URL do domínio especificado
     * 
     * @param mixed $input Valor a ser validado
     * @return bool True se válido, False caso contrário
     */
    public function validate($input): bool {
        return (bool) preg_match("#^https?://[^/]*{$this->domain}/?#i", trim((string) $input));
    }
}
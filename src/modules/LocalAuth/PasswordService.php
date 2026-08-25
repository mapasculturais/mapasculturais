<?php

namespace LocalAuth;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use MapasCulturais\i;

/**
 * Serviço de senha do login local.
 *
 * @internal Detalhe de implementação do módulo LocalAuth — não é API pública.
 *
 * Invariantes de compatibilidade com o estoque legado do MultipleLocalAuth:
 *  - Criação PINADA em PASSWORD_BCRYPT (formato idêntico ao estoque legado —
 *    nunca PASSWORD_DEFAULT, que pode derivar entre versões do PHP);
 *  - Verificação por password_verify() byte-a-byte (agnóstica: aceita $2y$10,
 *    $2y$12, $2a$ legados);
 *  - REHASH PROIBIDO: nenhuma reescrita transparente de hash no login;
 *  - Mesma metadata: localAuthenticationPassword (user_meta).
 *
 * Política de senha server-side com regex/flags compat-exatas com o plugin
 * legado (verifyPassowrds do Provider.php) — única fonte de verdade.
 */
class PasswordService
{
    public const PASS_META = 'localAuthenticationPassword';

    /**
     * Hash dummy usado no caminho de usuário inexistente (tempo uniforme).
     * Cost 12, par com a criação pinada — um dummy com cost menor que o das
     * contas criadas aqui criaria um oracle de timing (resposta
     * visivelmente mais rápida para usuário inexistente).
     */
    private const DUMMY_HASH = '$2y$12$fcNdM1kqFJ1x0EU2DNK0Vu2FJBxIv08qpv43zf2kxErvKOW7AnAy.';

    public function __construct(
        /** Política de senha (flags AUTH_PASS_* já resolvidas pelo módulo). */
        private array $policy,
        private ?\Closure $now = null,
        /**
         * Seam de teste (hasher spy): verificador injetável; default =
         * password_verify puro. Não altera a semântica de produção
         * (verificação byte-a-byte).
         * @param callable(string, string): bool $verifier
         */
        private ?\Closure $verifier = null,
    ) {
        $this->now ??= fn (): int => time();
    }

    /**
     * Verificação byte-a-byte — delega ao verificador configurado.
     * Hash nulo/lixo retorna false sem exceção (usuário social sem senha local).
     */
    public function verify(string $plain, ?string $storedHash): bool
    {
        if (!is_string($storedHash) || $storedHash === '') {
            return false;
        }
        if ($this->verifier !== null) {
            return (bool) (($this->verifier)($plain, $storedHash));
        }
        return password_verify($plain, $storedHash);
    }

    /**
     * Cria um hash bcrypt PINADO. Nunca PASSWORD_DEFAULT.
     * Cost 12 explícito: determinístico entre versões do PHP (o default do
     * bcrypt variou — 10 até o 8.3, 12 a partir do 8.4); password_verify
     * continua aceitando o estoque legado de qualquer cost (byte-a-byte).
     */
    public function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verificação com trabalho constante: quando o usuário não existe,
     * executa o mesmo bcrypt contra um hash dummy — timing indistinguível do
     * caminho real.
     */
    public function verifyDummy(string $plain): void
    {
        password_verify($plain, self::DUMMY_HASH);
    }

    /**
     * Política de senha — compat-exata com verifyPassowrds do plugin
     * (Provider.php:590-620): mesma regex de caracteres especiais, mesmas flags,
     * mesmas mensagens (i18n). Retorna lista de erros (vazio = válida).
     *
     * @return string[]
     */
    public function validatePolicy(string $plain, string $confirm): array
    {
        $p = $this->policy;
        $errors = [];

        if ($plain !== '') {
            if (strlen($plain) < (int) ($p['minimumPasswordLength'] ?? 6)) {
                $errors[] = i::__('Sua senha deve conter pelo menos ' . $p['minimumPasswordLength'] . ' dígitos!', 'local-auth');
            }
            if (!empty($p['passwordMustHaveNumbers']) && !preg_match('#[0-9]+#', $plain)) {
                $errors[] = i::__('Sua senha deve conter pelo menos 1 número!', 'local-auth');
            }
            if (!empty($p['passwordMustHaveCapitalLetters']) && !preg_match('#[A-Z]+#', $plain)) {
                $errors[] = i::__('Sua senha deve conter pelo menos 1 letra maiúscula!', 'local-auth');
            }
            if (!empty($p['passwordMustHaveLowercaseLetters']) && !preg_match('#[a-z]+#', $plain)) {
                $errors[] = i::__('Sua senha deve conter pelo menos 1 letra minúscula!', 'local-auth');
            }
            if (!empty($p['passwordMustHaveSpecialCharacters'])
                && !preg_match('/[\'^£$%&*()}{@#~?><>,|=_"!¨+`´\[\].;:\/-]/', $plain)) {
                $errors[] = i::__('Sua senha deve conter pelo menos 1 caractere especial!', 'local-auth');
            }
            // teto explícito (bcrypt trunca silenciosamente em 72 bytes)
            if (strlen($plain) > 1024) {
                $errors[] = i::__('Sua senha deve ter no máximo 1024 caracteres.', 'local-auth');
            }
        } else {
            $errors[] = i::__('Por favor, insira sua senha.', 'local-auth');
        }

        if ($plain !== $confirm) {
            $errors[] = i::__('As senhas não conferem.', 'local-auth');
        }

        return $errors;
    }

    /** Regras de senha para o frontend (endpoint passwordvalidationinfos). */
    public function rulesForJs(): array
    {
        $p = $this->policy;
        return [
            'passwordMustHaveCapitalLetters' => (bool) ($p['passwordMustHaveCapitalLetters'] ?? true),
            'passwordMustHaveLowercaseLetters' => (bool) ($p['passwordMustHaveLowercaseLetters'] ?? true),
            'passwordMustHaveSpecialCharacters' => (bool) ($p['passwordMustHaveSpecialCharacters'] ?? true),
            'passwordMustHaveNumbers' => (bool) ($p['passwordMustHaveNumbers'] ?? true),
            'minimumPasswordLength' => (int) ($p['minimumPasswordLength'] ?? 6),
        ];
    }

    /**
     * Token de recuperação/verificação: CSPRNG, 256 bits (64 hex), sem
     * truncagem. O lookup continua por igualdade exata em metadata; tokens
     * legados de 20 chars continuam válidos até o uso/expiração (validação
     * é por valor).
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /** Grava o hash na metadata localAuthenticationPassword (mesma chave do plugin). */
    public function storeHash(User $user, string $plain): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->setMetadata(self::PASS_META, $this->hash($plain));
        $user->saveMetadata(true);
        $app->enableAccessControl();
    }
}

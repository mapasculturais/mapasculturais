<?php

namespace LocalAuth;

use MapasCulturais\App;
use MapasCulturais\Entities\User;

/**
 * Serviço de bloqueio por tentativas de login.
 *
 * @internal Detalhe de implementação do módulo LocalAuth — não é API pública.
 *
 * Contrato N+1:
 *  - Contagem sobre o USUÁRIO RESOLVIDO (e-mail OU CPF — o caminho CPF do
 *    plugin legado não contava tentativas);
 *  - Contrato: AUTH_NUMBER_ATTEMPTS=N tentativas são permitidas; a falha de
 *    nº N GRAVA o ban; a (N+1)-ésima tentativa (mesmo com senha correta) é
 *    RECUSADA enquanto o ban vigora (default N=5 ⇒ 5 falhas banem; a 6ª
 *    tentativa é recusada) — corrige o off-by-one do plugin (ban na 7ª);
 *  - Login bem-sucedido zera contador e ban;
 *  - Redefinição de senha remove o ban (comportamento do plugin preservado);
 *  - `Closure $now` injetável (testabilidade).
 *
 * Metadatas (mesmas chaves do plugin — compatibilidade):
 *  - loginAttemp (contador), timeBlockedloginAttemp (timestamp de fim do ban).
 */
class LoginAttemptsService
{
    public const ATTEMPT_META = 'loginAttemp';
    public const BLOCKED_META = 'timeBlockedloginAttemp';

    public function __construct(
        /** Config: numberloginAttemp (AUTH_NUMBER_ATTEMPTS) e timeBlockedloginAttemp (AUTH_BLOCK_TIME). */
        private array $config,
        private ?\Closure $now = null,
    ) {
        $this->now ??= fn (): int => time();
    }

    private function time(): int
    {
        return (int) ($this->now)();
    }

    public function limit(): int
    {
        return max(1, (int) ($this->config['numberloginAttemp'] ?? 5));
    }

    public function blockSeconds(): int
    {
        return max(1, (int) ($this->config['timeBlockedloginAttemp'] ?? 900));
    }

    public function isBlocked(User $user): bool
    {
        $until = (int) ($user->getMetadata(self::BLOCKED_META) ?? 0);
        return $until > $this->time();
    }

    /** Segundos restantes de bloqueio (para mensagem de suporte). */
    public function blockedFor(User $user): int
    {
        return max(0, (int) ($user->getMetadata(self::BLOCKED_META) ?? 0) - $this->time());
    }

    /**
     * Registra uma tentativa FALHA e aplica o bloqueio quando o limite é
     * atingido (contrato N+1 da r2: AUTH_NUMBER_ATTEMPTS=N tentativas são
     * permitidas; a falha de nº N GRAVA o ban; a (N+1)-ésima tentativa —
     * mesmo com senha correta — é recusada enquanto o ban vigora).
     *
     * Retorna true quando ESTA falha gravou o ban.
     */
    public function registerFailure(User $user): bool
    {
        $app = App::i();
        $count = (int) ($user->getMetadata(self::ATTEMPT_META) ?? 0) + 1;

        $app->disableAccessControl();
        if ($count >= $this->limit()) {
            // a N-ésima falha (ou renovação em ban já vencido) grava o ban e zera o contador
            $user->setMetadata(self::BLOCKED_META, $this->time() + $this->blockSeconds());
            $user->setMetadata(self::ATTEMPT_META, 0);
        } else {
            $user->setMetadata(self::ATTEMPT_META, $count);
        }
        $user->saveMetadata(true);
        $app->enableAccessControl();

        return $count >= $this->limit();
    }

    /** Login bem-sucedido / redefinição de senha: zera contador e remove o ban. */
    public function reset(User $user): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->setMetadata(self::ATTEMPT_META, 0);
        $user->setMetadata(self::BLOCKED_META, 0);
        $user->saveMetadata(true);
        $app->enableAccessControl();
    }
}

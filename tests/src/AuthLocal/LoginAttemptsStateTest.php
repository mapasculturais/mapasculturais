<?php

namespace Tests\AuthLocal;

use LocalAuth\LoginAttemptsService;
use MapasCulturais\Entities\User;
use PHPUnit\Framework\TestCase;

/**
 * Bloqueio (unidade, relógio injetável — zero sleeps): o serviço entregue
 * opera sobre entidades User; os casos abaixo usam MOCK de User para exercitar
 * isBlocked/blockedFor/limit/blockSeconds com o relógio congelado, sem app/DB.
 *
 * O contrato N+1 COMPLETO (contador/ban por falha) é exercitado na integração
 * (LoginLockoutIntegrationTest) — registerFailure persiste metadata via App.
 *
 * NOTA: a semântica de registerFailure grava o ban na
 * falha N+1 (6ª), não na N-ésima — os testes de integração travam o contrato
 * (5ª falha bane; 6ª tentativa recusada) e FICAM VERMELHOS até
 * o Backend alinhar (ver relatório).
 */
class LoginAttemptsStateTest extends TestCase
{
    use RequiresLocalAuthModule;

    private function service(int $limit = 5, int $blockSeconds = 900, ?\Closure $now = null): LoginAttemptsService
    {
        return new LoginAttemptsService(
            ['numberloginAttemp' => $limit, 'timeBlockedloginAttemp' => $blockSeconds],
            $now
        );
    }

    private function mockUser(int $blockedUntil): User
    {
        $user = $this->createMock(User::class);
        $user->method('getMetadata')->willReturnCallback(
            fn(string $key) => $key === LoginAttemptsService::BLOCKED_META ? (string) $blockedUntil : '0'
        );
        return $user;
    }

    public function testDefaultsFromConfig(): void
    {
        $svc = $this->service(5, 900);
        $this->assertSame(5, $svc->limit());
        $this->assertSame(900, $svc->blockSeconds());
    }

    public function testConfigClampsToMinimums(): void
    {
        $svc = $this->service(0, 0);
        $this->assertGreaterThanOrEqual(1, $svc->limit(), 'limite mínimo 1');
        $this->assertGreaterThanOrEqual(1, $svc->blockSeconds(), 'bloqueio mínimo 1s');
    }

    public function testIsBlockedBoundaryWithFrozenClock(): void
    {
        $t = 1000000;
        $svc = $this->service(now: fn(): int => $t);

        $this->assertTrue($svc->isBlocked($this->mockUser($t + 1)), 'blocked_until futuro: bloqueado');
        $this->assertFalse($svc->isBlocked($this->mockUser($t)), 'blocked_until == now: liberado (comparação estrita)');
        $this->assertFalse($svc->isBlocked($this->mockUser(0)), 'sem bloqueio registrado');
    }

    public function testBlockedForReportsRemainingSeconds(): void
    {
        $t = 1000000;
        $svc = $this->service(now: fn(): int => $t);
        $this->assertSame(900, $svc->blockedFor($this->mockUser($t + 900)));
        $this->assertSame(0, $svc->blockedFor($this->mockUser($t - 500)), 'expirado: 0 restante');
    }

    public function testClockIsInjectable(): void
    {
        $frozen = 500000000;
        $svc = $this->service(now: fn(): int => $frozen);
        $this->assertTrue($svc->isBlocked($this->mockUser($frozen + 60)), 'relógio congelado via Closure $now ');
    }

    /**
     * Regressão permanente do contrato N+1 contra o serviço REAL,
     * com mock stateful de User (sem app/DB): a 5ª falha (N=5) grava o ban
     * (now+900) e zera o contador; a 6ª tentativa encontra bloqueio ativo.
     * Este é o tripwire unit do F2; os 3 tripwires de integração
     * rodam no CI/docker.
     */
    public function testNthFailureBansAndSixthAttemptIsBlockedAgainstRealService(): void
    {
        require_once __DIR__ . '/../AuthSecurityAppMock.php';
        \Tests\AuthSecurityAppMock::install();

        $user = new class extends \MapasCulturais\Entities\User {
            public array $metaStore = [];
            public function getMetadata($meta_key = null, $return_metadata_object = false) { return $this->metaStore[$meta_key] ?? null; }
            public function setMetadata($key, $value) { $this->metaStore[$key] = $value; return $this; }
            public function saveMetadata($flush = true) { return $this; }
            public function __construct() {}
        };

        $t = 1000000;
        $svc = new LoginAttemptsService(['numberloginAttemp' => 5, 'timeBlockedloginAttemp' => 900], fn(): int => $t);

        for ($i = 1; $i <= 4; $i++) {
            $this->assertFalse($svc->registerFailure($user), "falha {$i} não bane");
            $this->assertSame($i, (int) $user->metaStore[LoginAttemptsService::ATTEMPT_META]);
        }

        $this->assertTrue($svc->registerFailure($user), 'a 5ª falha (N) GRAVA o ban (contrato N+1)');
        $this->assertSame(0, (int) $user->metaStore[LoginAttemptsService::ATTEMPT_META], 'contador zera ao banir');
        $this->assertSame($t + 900, (int) $user->metaStore[LoginAttemptsService::BLOCKED_META], 'ban vale 900s');
        $this->assertTrue($svc->isBlocked($user), 'a 6ª tentativa encontra bloqueio ativo');
    }
}

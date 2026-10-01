<?php

namespace Tests\Doubles;

use MapasCulturais\App;
use MapasCulturais\AuthProviders\Test as TestAuthProvider;
use MapasCulturais\Entities\User;

/**
 * Provedor que implementa o contrato de validação de conta e registra as chamadas
 * recebidas, para que o endpoint de reenvio possa ser exercitado sem depender de
 * um plugin.
 *
 * Cada propriedade pública controla um desfecho do reenvio: sucesso, falha por
 * retorno falso, falha por exceção e resposta emitida pelo próprio provedor.
 */
class ResendAccountValidationAuthProvider extends TestAuthProvider
{
    public bool $supports = true;
    public bool $validated = false;
    public bool $sendResult = true;
    public ?\Throwable $throwOnSend = null;
    public ?int $haltWithStatus = null;
    public array $resendCalls = [];

    function supportsAccountValidation()
    {
        return $this->supports;
    }

    function isAccountValidated(User $user)
    {
        return $this->validated;
    }

    function resendAccountValidationEmail(User $user)
    {
        $this->resendCalls[] = $user->id;

        if ($this->haltWithStatus) {
            App::i()->halt($this->haltWithStatus);
        }

        if ($this->throwOnSend) {
            throw $this->throwOnSend;
        }

        return $this->sendResult;
    }
}

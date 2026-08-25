<?php

/**
 * Ativação do módulo LocalAuth no ambiente de teste.
 *
 * O módulo core LocalAuth (src/modules/LocalAuth) é ortogonal ao
 * `auth.provider`: a suíte de login local roda com o
 * provider de teste padrão (`tests/config.d/auth.php` → 'Test') e o módulo
 * habilitado por config + env — sem TEST_AUTH_PROVIDER.
 *
 * AVISO: 'plugins' => [] é ESSENCIAL — o config base do repo
 * (config/*.php) lista MultipleLocalAuth como plugin ativo, o que dispara o
 * STAND-DOWN do módulo core (comportamento correto em produção, mas no
 * ambiente de teste as rotas do core precisam existir). Sem plugins ativos,
 * o cenário de teste é o pós-migração (instância que removeu o MLA) — o
 * mesmo cenário dos testes de serialização private. Os plugins do
 * config base não carregam no container de testes de qualquer forma
 * (src/plugins não é consumido pelo scanner de config).
 *
 * A suíte de toggle-OFF (tests/src/LocalAuthToggleOffTest.php) roda em
 * processo separado com AUTH_LOCAL_LOGIN_ENABLED=false (job próprio no CI).
 */

return [
    // cenário pós-migração: nenhum plugin de autenticação ativo
    'plugins' => [],

    'module.LocalAuth' => [
        // defaults de teste — espelham os defaults de produção do módulo,
        // com tempos curtos para determinismo (nenhum sleep; ver plano r2 §4)
        'numberloginAttemp' => 5,
        'timeBlockedloginAttemp' => 900,
        'minimumPasswordLength' => 6,
        'enableLoginByCPF' => true,
        'requireCpf' => true,
        'userMustConfirmEmailToUseTheSystem' => true,
        'loginOnRegister' => false,
    ],
];

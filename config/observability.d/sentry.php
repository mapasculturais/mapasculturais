<?php
/**
 * Integração com o Sentry.
 *
 * Inicializa o SDK o mais cedo possível: este arquivo é carregado pelo loader
 * de configuração (src/conf/config.php), antes do App->init(). Sem SENTRY_DSN a
 * integração fica desligada — o init() não é chamado e o handler `sentry` do
 * Monolog (registrado em App::_initLogger) não é adicionado, por não haver client.
 *
 * Cobertura de captura:
 * - fatais e exceções não tratadas: integrações default do SDK;
 * - exceções tratadas pelo Slim: ErrorHandler -> $app->log->critical(..., ['exception' => $e]);
 * - demais logs de erro do app: handler `sentry` em monolog.handlers.
 */

$dsn = env('SENTRY_DSN', null);

if ($dsn) {
    \Sentry\init([
        'dsn' => $dsn,
        'enable_logs' => (bool) env('SENTRY_ENABLE_LOGS', true),
    ]);
}

return [];

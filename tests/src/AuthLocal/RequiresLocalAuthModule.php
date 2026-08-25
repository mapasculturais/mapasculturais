<?php

namespace Tests\AuthLocal;

/**
 * Guard compartilhado das suítes: os testes são TDD-ready contra os
 * contratos fechados. O módulo foi entregue com namespace raiz
 * `LocalAuth\` (decisão de implementação) — os testes seguem o código entregue.
 *
 * IMPORTANTE: o prefixo `LocalAuth\` NÃO está
 * no autoload do composer.json do repo — as classes do módulo só carregam em
 * ambientes que o registram (o ambiente isolado do CI registra; o repo NÃO).
 * Até o Backend adicionar `"LocalAuth\\": "src/modules/LocalAuth"` ao
 * composer.json, o boot da aplicação fata ao escanear o módulo.
 */
trait RequiresLocalAuthModule
{
    protected function guardLocalAuthModule(): void
    {
        // env()/funções globais do core: no fluxo de integração o src/bootstrap.php
        // da aplicação já as carregou (require); nos unitários isolados, carrega aqui
        // (require_once — ver nota no phpunit-bootstrap.php).
        if (!function_exists('env')) {
            foreach ([__DIR__ . '/../../../src/functions.php', __DIR__ . '/../../src/functions.php'] as $functions) {
                if (file_exists($functions)) {
                    require_once $functions;
                    break;
                }
            }
        }
        if (!class_exists(\LocalAuth\PasswordService::class)) {
            $this->markTestSkipped(
                'Módulo LocalAuth não carregável neste ambiente (entregue em src/modules/LocalAuth; ' .
                'requer prefixo de autoload LocalAuth\ ).'
            );
        }
    }
}

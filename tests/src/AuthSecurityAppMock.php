<?php

/**
 * Stub mínimo de App para os testes de unidade do
 * OAuth2ClientHelper que não exigem a aplicação completa (banco/tema).
 *
 * O OAuth2ClientHelper usa App::i() apenas para: config (app.mode), log,
 * cache (JWKS) e getBaseUrl(). O mock cria uma instância de App sem rodar o
 * construtor (newInstanceWithoutConstructor) e injeta somente o necessário.
 *
 * A detecção de "aplicação real já booted" lê
 * _instances DIRETAMENTE (sem chamar App::i(), que INSTANCIA a app real
 * quando as deps do vendor estão presentes — o construtor completa com
 * config vazia e o mock desistia, deixando log/cache uninitialized).
 * Critério: instância existente COM config contendo 'app.mode' = app
 * realmente inicializada (suíte de integração) → não intercepta; qualquer
 * outro estado → o mock assume (unitários).
 *
 * Este arquivo NÃO faz parte da API do core — é infraestrutura de teste.
 */

namespace Tests;

use MapasCulturais\App;

class AuthSecurityAppMock
{
    /** Modo de app simulado (development por padrão nos testes). */
    public static string $appMode = 'development';

    public static function install(): void
    {
        $ref = new \ReflectionClass(App::class);

        // lê _instances SEM instanciar a aplicação real
        $propInstance = $ref->getProperty('_instances');
        $propInstance->setAccessible(true);
        $instances = $propInstance->getValue() ?? [];

        $current = $instances['web'] ?? null;
        if ($current instanceof App) {
            try {
                $config = $current->config;
            } catch (\Throwable $e) {
                $config = null; // typed uninitialized — instância não inicializada
            }
            if (is_array($config) && isset($config['__auth_security_mock'])) {
                // já é o nosso mock — apenas sincroniza o modo
                $current->config['app.mode'] = self::$appMode;
                return;
            }
            if (is_array($config) && isset($config['app.mode'])) {
                return; // aplicação REAL inicializada (suíte de integração) — não intercepta
            }
            // instância real não-inicializada (construtor parcial): substitui pelo mock
        }

        $app = $ref->newInstanceWithoutConstructor();

        // config mínima (prop pública)
        $app->config = [
            '__auth_security_mock' => true,
            'app.mode' => self::$appMode,
            'base.url' => 'https://mapas.test/',
        ];

        // log nulo
        if (class_exists(\Monolog\Logger::class) && class_exists(\Monolog\Handler\NullHandler::class)) {
            $logger = new \Monolog\Logger('test');
            $logger->pushHandler(new \Monolog\Handler\NullHandler());
        } else {
            $logger = new class {
                public function __call($name, $args) {}
            };
        }
        $app->log = $logger;

        // cache real do core sobre ArrayAdapter do Symfony (em memória)
        if (class_exists(\Symfony\Component\Cache\Adapter\ArrayAdapter::class)) {
            $app->cache = new \MapasCulturais\Cache(new \Symfony\Component\Cache\Adapter\ArrayAdapter());
        } else {
            $app->cache = new class {
                public function __call($name, $args) {}
            };
        }

        // registra a instância no estático de instâncias de App
        $propInstance->setValue(null, ['web' => $app]);
    }
}

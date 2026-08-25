<?php

/**
 * Bootstrap do phpunit para as suítes de autenticação (unitárias) — auto-descoberta
 * de autoload nos três contextos de execução:
 *
 *  1. Container de testes (cwd=/var/www/tests ← tests/src): ../../vendor = /var/www/vendor
 *  2. Host a partir da raiz do repo: tests/src/../../vendor = ./vendor
 *  3. Ambiente isolado de unitários (CI job auth-unit / verificação local):
 *     vendor do diretório de trabalho onde o phpunit foi invocado
 *
 * Os testes de integração (Tests\Abstract\TestCase) fazem o próprio boot da
 * aplicação via tests/src/bootstrap.php — este arquivo é inócuo para eles
 * (apenas garante o autoload das classes doPHPUnit/Helpers).
 */

foreach (
    [
        __DIR__ . '/../vendor/autoload.php',     // container: /var/www/tests/../vendor (mount ./src)
        __DIR__ . '/../../vendor/autoload.php',  // host: tests/src/../../vendor (raiz do repo)
        getcwd() . '/vendor/autoload.php',       // ambiente isolado (cwd do phpunit)
    ] as $autoload
) {
    if (file_exists($autoload)) {
        require $autoload;
        break;
    }
}

// Constantes de caminho que o i18n do core (i::__) eventualmente acessa em
// unitários isolados — apontam para o diretório real quando existir, senão
// para um diretório vazio (o NOOP translations não lê nada; replaces() só
// precisa de um caminho válido).
//
// NOTA: env()/functions.php NÃO é incluído aqui — src/bootstrap.php
// faz `require` (não once) de functions.php, e incluí-lo antes fatala o boot
// da aplicação nas suítes de integração. Nos unitários, o trait
// RequiresLocalAuthModule carrega functions.php sob demanda (require_once).
if (!defined('LANGUAGES_PATH')) {
    $langs = __DIR__ . '/../../translations/';            // host: repo/translations
    if (!is_dir($langs)) {
        $langs = __DIR__ . '/../translations/';           // container: /var/www/translations
    }
    define('LANGUAGES_PATH', is_dir($langs) ? $langs : (sys_get_temp_dir() . '/'));
}

// Cache::save() do core usa DAY_IN_SECONDS (definida no src/bootstrap.php da
// aplicação — ausente no fluxo unitário sem boot)
if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

if (!class_exists(\PHPUnit\Framework\TestCase::class)) {
    fwrite(STDERR, "phpunit-bootstrap: autoload do PHPUnit não encontrado — invoque via vendor/bin/phpunit\n");
    exit(1);
}

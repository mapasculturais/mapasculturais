<?php

/**********************************
 * This is a debug script for phpdbg tool.
 * Usage:
 *
 * php5dbg dbg.php http://mapas.culturais.domain/busca/
 *
 */

// Assert this will never be served through a real HTTP request
if (isset($_SERVER['REQUEST_URI'])) {
    header("HTTP/1.1 404 Not Found");
}

// Extract Domain, URI and Query String from URL provided as first argument
$url = $argv[1];
$parts = split('://', $url);
if (sizeof($parts) > 1) {
    $url = $parts[1];
}
$parts = split('/', $url, 2);
$domain = $parts[0];
$uri = '/' . $parts[1];
$qs = '';
if (strpos($uri, '?') >= 0) {
    $parts = split('\?', $uri);
    $uri = $parts[0];
    $qs = $parts[1];
}

/**
* Bootstrapping ...
*/
if (!defined('PHPDBG_BOOTSTRAPPED'))
{
    /* define these once */
    define("PHPDBG_BOOTPATH", "/opt/php-zts/htdocs");
    define("PHPDBG_BOOTSTRAP", "index.php");
    define("PHPDBG_BOOTSTRAPPED", sprintf(
        "/%s", PHPDBG_BOOTSTRAP));
}

/*
* Superglobals are JIT, phpdbg will not over-write 
* whatever is set during bootstrap
*/

$_SERVER = array
(
  'HTTP_HOST' => $domain,
  'HTTP_CONNECTION' => 'keep-alive',
  'HTTP_ACCEPT' => '...',
  'HTTP_USER_AGENT' => '...',
  'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
  'HTTP_COOKIE' => '...',
  'PATH' => '/usr/local/bin:/usr/bin:/bin',
  'SERVER_SIGNATURE' => '...',
  'SERVER_SOFTWARE' => '...',
  'SERVER_NAME' => $domain,
  'SERVER_ADDR' => '127.0.0.1',
  'SERVER_PORT' => '80',
  'REMOTE_ADDR' => '127.0.0.1',
  'DOCUMENT_ROOT' => __DIR__,
  'REQUEST_SCHEME' => 'http',
  'CONTEXT_PREFIX' => '',
  'CONTEXT_DOCUMENT_ROOT' => __DIR__,
  'SERVER_ADMIN' => '[no address given]',
  'SCRIPT_FILENAME' => sprintf(
    '%s/%s', __DIR__, PHPDBG_BOOTSTRAP
  ),
  'REMOTE_PORT' => '47931',
  'GATEWAY_INTERFACE' => 'CGI/1.1',
  'SERVER_PROTOCOL' => 'HTTP/1.1',
  'REQUEST_METHOD' => 'GET',
  'QUERY_STRING' => $qs,
  'REQUEST_URI' => $uri . ($qs ? '?' . $qs : ''),
  'SCRIPT_NAME' => '/index.php',
  'PHP_SELF' => '/index.php' . $uri,
  'REQUEST_TIME' => time(),
);

$_GET = array();
$_REQUEST = array();
$_POST = array();
$_COOKIE = array();
$_FILES = array();

include __DIR__.'/index.php';

?>
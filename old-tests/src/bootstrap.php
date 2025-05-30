<?php
use MapasCulturais\App;


//
// Unit Test Bootstrap and Slim PHP Testing Framework
// =============================================================================
//
// SlimpPHP is a little hard to test - but with this harness we can load our
// routes into our own `$app` container for unit testing, and then `run()` and
// hand a reference to the `$app` to our tests so that they have access to the
// dependency injection container and such.
//
// * Author: [Craig Davis](craig@there4development.com)
// * Since: 10/2/2013
//
// -----------------------------------------------------------------------------

date_default_timezone_set('America/Sao_Paulo');

require __DIR__ . "/../src/protected/application/bootstrap-common.php";
require_once __DIR__ . "/../src/protected/vendor/autoload.php";

 // Prepare a mock environment
\Slim\Environment::mock(array_merge(array(
    'REQUEST_METHOD' => 'get',
    'PATH_INFO'      => '/',
    'SERVER_NAME'    => 'local.dev',
)));


$config = include __DIR__ . '/../src/protected/application/conf/conf-test.php';

if(isset($_ENV['MAPASCULTURAIS_CONFIG_FILE'])){
    $config = include __DIR__ . '/../src/protected/application/conf/'. $_ENV['MAPASCULTURAIS_CONFIG_FILE'];    
}else if(isset($_SERVER['MAPASCULTURAIS_CONFIG_FILE'])){
    $config = include __DIR__ . '/../src/protected/application/conf/' . $_SERVER['MAPASCULTURAIS_CONFIG_FILE'];
}

// create the App instance
$app = App::i()->init($config);
$app->register();

require __DIR__ . '/classes/TestCase.php';
require __DIR__ . '/classes/TestFactory.php';

/* End of file bootstrap.php */

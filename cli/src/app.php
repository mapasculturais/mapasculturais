<?php
$_SERVER['REQUEST_METHOD'] = 'CLI';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '8080';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require __DIR__ . '../../../src/protected/application/bootstrap.php';

require __DIR__ . '/McCli.php';


$params = array_slice($argv, 1);

McCli::exec($params);
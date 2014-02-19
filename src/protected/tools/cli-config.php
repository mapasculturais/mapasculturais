<?php
require_once __DIR__ . '/../vendor/autoload.php';
MapasCulturais\Types\DoctrineMap\Point::register();
MapasCulturais\Types\DoctrineMap\Geography::register();
MapasCulturais\Types\DoctrineMap\Geometry::register();
MapasCulturais\Types\DoctrineMap\Frequency::register();

$entities_path = array('../application/lib/MapasCulturais/Entities/');

$classLoader = new \Doctrine\Common\ClassLoader('Entities', __DIR__);
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Proxies', __DIR__);
$classLoader->register();

$config = Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($entities_path, true);

$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);

$config->setProxyDir(__DIR__ . '/Proxies');
$config->setProxyNamespace('Proxies');


$connectionOptions = array(
    'dbname' => 'mapasculturais',
    'user' => 'mapasculturais',
    'password' => 'mapasculturais',
    'host' => 'localhost',
    'driver' => 'pdo_pgsql',
);
//die(var_dump($config));
$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('point', 'point');
$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('geography', 'geography');
$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('geometry', 'geometry');
$em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('frequency', 'frequency');

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));





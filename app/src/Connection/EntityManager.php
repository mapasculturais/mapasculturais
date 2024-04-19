<?php

declare(strict_types=1);

namespace App\Connection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager as DoctrineEntityManager;
use Doctrine\ORM\ORMSetup;
use MapasCulturais\Cache;

class EntityManager extends DoctrineEntityManager
{
    private Configuration $configuration;
    private array $configParams = [];

    public function __construct()
    {
        $this->configParams = require dirname(__DIR__, 3).'/src/conf/config.php';
        $this->configuration = $this->makeConfiguration();

        $connection = $this->makeConnection();

        parent::__construct($connection, $this->configuration);
    }

    private function makeConfiguration(): Configuration
    {
        $cache = new Cache($this->configParams['app.cache']);

        return ORMSetup::createAttributeMetadataConfiguration(
            paths: [dirname(__DIR__).'/Entity/'],
            isDevMode: (bool) $this->configParams['doctrine.isDev'],
            cache: $cache->adapter
        );
    }

    private function makeConnection(): Connection
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'dbname' => $this->configParams['db.dbname'],
            'user' => $this->configParams['db.user'],
            'password' => $this->configParams['db.password'],
            'host' => $this->configParams['db.host'],
        ], $this->configuration);
    }
}

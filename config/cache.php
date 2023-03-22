<?php
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$_cache_namespace = env('CACHE_NAMESPACE', md5(@$_SERVER['HTTP_HOST']));

if (env('REDIS_CACHE', false)) {
    
    $redis = new \Redis();
    $redis->connect(env('REDIS_CACHE'));
    
    $redis = new \Redis();
    $redis->connect(env('REDIS_CACHE'));


    $_cache = new RedisAdapter($redis, $_cache_namespace);
    $_mscache = new RedisAdapter($redis, "ms.$_cache_namespace");
} else {
    try {
        $_cache = new ApcuAdapter($_cache_namespace);
        $_mscache = new ApcuAdapter("ms.$_cache_namespace");
    } catch (\Exception $e) {
        $_cache = new FilesystemAdapter($_cache_namespace);
        $_mscache = new FilesystemAdapter("ms.$_cache_namespace");
    }
}

return [
    'app.cache' => $_cache,
    'app.mscache' => $_mscache,
        
    'app.cache.namespace' => env('CACHE_NAMESPACE', @$_SERVER['HTTP_HOST']),

    'app.useRegisteredAutoloadCache' => __env_not_false('CACHE_AUTOLOAD'),
    'app.useAssetsUrlCache'          => __env_not_false('CACHE_ASSETS_URL'),
    'app.useFileUrlCache'            => __env_not_false('CACHE_FILE_URL'),
    'app.useEventsCache'             => __env_not_false('CACHE_EVENTS'),
    'app.useSubsiteIdsCache'         => __env_not_false('CACHE_SUBSITE_ID'),
    'app.usePermissionsCache'        => __env_not_false('CACHE_PERMISSIONS'),
    'app.useRegisterCache'           => __env_not_false('CACHE_REGISTER'),
    'app.useApiCache'                => __env_not_false('CACHE_API'),


    'app.registeredAutoloadCache.lifetime'  => env('CACHE_AUTOLOAD', 0),
    'app.assetsUrlCache.lifetime'           => env('CACHE_ASSETS_URL', 0),
    'app.fileUrlCache.lifetime'             => env('CACHE_FILE_URL', 604800),
    'app.eventsCache.lifetime'              => env('CACHE_EVENTS', 600),
    'app.subsiteIdsCache.lifetime'          => env('CACHE_SUBSITE_ID', 120),
    'app.permissionsCache.lifetime'         => env('CACHE_PERMISSIONS', 120),
    'app.registerCache.lifeTime'            => env('CACHE_REGISTER', 600),
    'app.apiCache.lifetime'                 => env('CACHE_API', 30),

    'app.apiCache.lifetimeByController' => [
        'notification'  => env('CACHE_API_NOTIFICATION', 10)
    ],
];
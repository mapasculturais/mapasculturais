<?php
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

if (env('REDIS_CACHE')) {
    $redis = new \Redis();
    $redis->connect(env('REDIS_CACHE'));
    $_cache = new RedisAdapter($redis);

    $redis = new \Redis();
    $redis->connect(env('REDIS_CACHE'));
    $_mscache = new RedisAdapter($redis, "ms");
} else {
    try {
        $_cache = new ApcuAdapter();
        $_mscache = new ApcuAdapter("ms");
    } catch (\Exception $e) {
        $_cache = new FilesystemAdapter();
        $_mscache = new FilesystemAdapter("ms");
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
    'app.usePermissionsCache'        => (bool) env('CACHE_PERMISSIONS', env('REDIS_CACHE', false)),
    'app.useRegisterCache'           => __env_not_false('CACHE_REGISTER'),
    'app.useApiCache'                => env('CACHE_API', false),
    'app.useQuotasCache'             => env('CACHE_QUOTAS', false),
    'app.useOpportunitySummaryCache' => __env_not_false('CACHE_OPPORTUNITY_SUMARY'),


    'app.registeredAutoloadCache.lifetime'  => env('CACHE_AUTOLOAD', YEAR_IN_SECONDS),
    'app.assetsUrlCache.lifetime'           => env('CACHE_ASSETS_URL', YEAR_IN_SECONDS),
    'app.fileUrlCache.lifetime'             => env('CACHE_FILE_URL', 604800),
    'app.eventsCache.lifetime'              => env('CACHE_EVENTS', 600),
    'app.subsiteIdsCache.lifetime'          => env('CACHE_SUBSITE_ID', 120),
    'app.permissionsCache.lifetime'         => env('CACHE_PERMISSIONS', 30),
    'app.registerCache.lifeTime'            => env('CACHE_REGISTER', 600),
    'app.apiCache.lifetime'                 => env('CACHE_API', 30),
    'app.quotasCache.lifetime'              => env('CACHE_QUOTAS', 300),
    'app.opportunitySummaryCache.lifetime'  => env('CACHE_OPPORTUNITY_SUMARY', 30 * MINUTE_IN_SECONDS),

    'app.apiCache.lifetimeByController' => [
        'notification'  => env('CACHE_API_NOTIFICATION', 10)
    ],
];
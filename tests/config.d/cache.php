<?php 
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
return [
    'app.cache' => new NullAdapter(),
    'app.mscache' => new NullAdapter(),
    'app.useMagicGetterCache'        => false,
    'app.useRegisteredAutoloadCache' => false,
    'app.useAssetsUrlCache'          => false,
    'app.useFileUrlCache'            => false,
    'app.useEventsCache'             => false,
    'app.useSubsiteIdsCache'         => false,
    'app.usePermissionsCache'        => false,
    'app.useRegisterCache'           => false,
    'app.useApiCache'                => false,
];
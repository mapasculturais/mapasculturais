<?php 
use Symfony\Component\Cache\Adapter\ArrayAdapter;

return [
    'app.cache' => new ArrayAdapter(),
    'app.mscache' => new ArrayAdapter(),
    'app.useRegisteredAutoloadCache' => false,
    'app.useAssetsUrlCache'          => false,
    'app.useFileUrlCache'            => false,
    'app.useEventsCache'             => false,
    'app.useSubsiteIdsCache'         => false,
    'app.usePermissionsCache'        => false,
    'app.useRegisterCache'           => false,
    'app.useApiCache'                => false,
];
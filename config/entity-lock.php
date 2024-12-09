<?php

return [
    'entity.lock.timeout' => env('ENTITY_LOCK_TIMEOUT', 60),
    'entity.lock.renewInterval' => env('ENTITY_LOCK_RENEW_INTERVAL', 45)
];
<?php

return [
    'notifications.entities.new'    => (bool) env('NOTIFICATIONS_ENTITY_NEW', false), // Send notification when a entity is included
    'notifications.entities.update' =>  (int) env('NOTIFICATIONS_ENTITY_UPDATE', 90),  // days
    'notifications.user.access'     =>  (int) env('NOTIFICATIONS_USER_ACCESS', 90),  // days
    'notifications.seal.toExpire'   =>  (int) env('NOTIFICATIONS_SEAL_EXPIRATION', 1),  // days
 
    'notifications.interval'        =>  (int) env('NOTIFICATIONS_REFRESH_INTERVAL', 60),  // seconds
];
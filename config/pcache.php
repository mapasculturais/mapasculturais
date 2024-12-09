<?php
return [
    /*
    Define o número de entidades que podem ser processadas por processo.
    */
    'pcache.maxEntitiesPerProcess' => env('PCACHE_NUM_ENTITIES_PER_PROCESS', 25),

    /*
    Define se o processo deve processar todos os usuários enfileirados
    para a mesma entidade simultaneamente ou se deve processar um de cada vez
    */
    'pcache.groupUsers' => env('PCACHE_GROUP_USERS', true),

    /*
    Define se o processo deve enfileirar as entidades extras um próximo provesso
    ou se deve processá-las
    */
    'pcache.enqueueExtraEntities' => env('PCACHE_GROUP_USERS', true),

];
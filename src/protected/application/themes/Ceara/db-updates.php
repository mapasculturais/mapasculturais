<?php

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\MetaList;

$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return array( 
    'change type of evaluation menthods configurations' => function() use ($conn){
        $ids = "220, 334, 335, 336, 338, 339, 340, 337, 341, 342";
        $conn->executeQuery("UPDATE evaluation_method_configuration SET type = 'documentary' WHERE opportunity_id IN ({$ids})");
    }
);

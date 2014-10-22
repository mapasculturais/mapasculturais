<?php
$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return array(
    'migrate sp_distrito sp_subprefeitura sp_regiao to new geo_divisions metakeys' => function() use ($conn){
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoZona' WHERE key = 'sp_regiao'");
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoSubprefeitura' WHERE key = 'sp_subprefeitura'");
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoDistrito' WHERE key = 'sp_distrito'");

        $conn->executeQuery("UPDATE space_meta SET key = 'geoZona' WHERE key = 'sp_regiao'");
        $conn->executeQuery("UPDATE space_meta SET key = 'geoSubprefeitura' WHERE key = 'sp_subprefeitura'");
        $conn->executeQuery("UPDATE space_meta SET key = 'geoDistrito' WHERE key = 'sp_distrito'");
    }
);
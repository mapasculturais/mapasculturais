<?php
$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return array(
    'migrate sp geo data to geo_division table - teste 1' => function () use($conn) {
        foreach($conn->fetchAll("SELECT * FROM sp_regiao ORDER BY cod_reg_8 ASC") as $item){
            $cod  = addslashes("zona-" . $item['cod_reg_8']);
            $name = addslashes($item['nome']);
            $geom = addslashes($item['the_geom']);

            echo "\n zona $name\n";

            $conn->executeQuery("
                INSERT INTO geo_division
                    ( type, cod, name, geom )
                VALUES
                    ( 'zona', :cod, :name, :geom );", array('cod' => $cod, 'name' => $name, 'geom' => $geom));
        }

        foreach($conn->fetchAll("SELECT * FROM sp_subprefeitura") as $item){
            $cod  = addslashes("subprefeitura-" . $item['cod_subpre']);
            $name = addslashes($item['nome']);
            $geom = addslashes($item['the_geom']);

            echo "\n inserindo subprefeitura $name\n";

            $conn->executeQuery("
                INSERT INTO geo_division
                    ( type, cod, name, geom )
                VALUES
                    ( 'subprefeitura', :cod, :name, :geom );", array('cod' => $cod, 'name' => $name, 'geom' => $geom));
        }


        foreach($conn->fetchAll("SELECT * FROM sp_distrito") as $item){
            $cod  = addslashes("distrito-" . $item['cod_distri']);
            $name = addslashes($item['nome_distr']);
            $geom = addslashes($item['the_geom']);

            echo "\n inserindo distrito $name\n";
            $conn->executeQuery("
                INSERT INTO geo_division
                    ( type, cod, name, geom )
                VALUES
                    ( 'distrito', :cod, :name, :geom );", array('cod' => $cod, 'name' => $name, 'geom' => $geom));
        }
    },

    'migrate sp_distrito sp_subprefeitura sp_regiao to new geo_divisions metakeys' => function() use ($conn){
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoZona' WHERE key = 'sp_regiao'");
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoSubprefeitura' WHERE key = 'sp_subprefeitura'");
        $conn->executeQuery("UPDATE agent_meta SET key = 'geoDistrito' WHERE key = 'sp_distrito'");

        $conn->executeQuery("UPDATE space_meta SET key = 'geoZona' WHERE key = 'sp_regiao'");
        $conn->executeQuery("UPDATE space_meta SET key = 'geoSubprefeitura' WHERE key = 'sp_subprefeitura'");
        $conn->executeQuery("UPDATE space_meta SET key = 'geoDistrito' WHERE key = 'sp_distrito'");
    }
);
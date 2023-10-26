<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();

return [
    'define metadado isDataCollection = 0 nas fases sem campos configurados' => function () use($conn) {
        $sql = "
            SELECT id 
            FROM opportunity
            WHERE 
                parent_id IS NOT NULL AND
                id NOT IN (SELECT opportunity_id FROM registration_field_configuration) AND
                id NOT IN (SELECT opportunity_id FROM registration_file_configuration)";

        $ids = $conn->fetchAll($sql);
        foreach($ids as $id) {
            $id = $id['id'];

            $conn->exec("INSERT INTO opportunity_meta (object_id, key, value) VALUES ({$id}, 'isDataCollection', '')");
        }
    }
];
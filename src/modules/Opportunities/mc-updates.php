<?php
use MapasCulturais\i;

return [
    'Adiciona o step_id nos campos e anexos dos formulários nas oportunidades' => function () {

        $app = \MapasCulturais\App::i();

        DB_UPDATE::enqueue('Opportunity', '1=1', function(MapasCulturais\Entities\Opportunity $opportunity) use ($app) {
            $conn = $app->em->getConnection();
            $fields = $conn->fetchAll("SELECT id FROM registration_field_configuration WHERE opportunity_id = {$opportunity->id}");
            $files = $conn->fetchAll("SELECT id FROM registration_file_configuration WHERE opportunity_id = {$opportunity->id}");

            if (!empty($fields) || !empty($files)) {
                $datetime = new DateTime();
                $datetime = $datetime->format('Y-m-d H:i:s');
                $conn->executeQuery("INSERT INTO registration_step (name, display_order, opportunity_id, create_timestamp, update_timestamp) VALUES ('', 0, {$opportunity->id}, '{$datetime}', '{$datetime}');");
                $stepId = $conn->lastInsertId();

                if (!empty($fields)) {
                    foreach ($fields as $field) {
                        $conn->executeQuery("UPDATE registration_field_configuration SET step_id = {$stepId} WHERE id = {$field['id']}");
                    }
                }

                if (!empty($files)) {
                    foreach ($files as $file) {
                        $conn->executeQuery("UPDATE registration_file_configuration SET step_id = {$stepId} WHERE id = {$file['id']}");
                    }
                }
            }
        });
    }
];
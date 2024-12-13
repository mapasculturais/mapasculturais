<?php
namespace MapasCulturais;

use function MapasCulturais\__exec;
use function MapasCulturais\__try;

return [
    'Cria a tabela da entidade RegistrationStep' => function () {
        $app = App::i();
        $em = $app->em;

        $conn = $em->getConnection();

        if (!__table_exists('registration_step')) {
            if (!__sequence_exists('registration_step_seq')) {
                $conn->executeQuery("CREATE SEQUENCE registration_step_seq START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;");
            }

            __exec("CREATE TABLE registration_step (
                    id INT NOT NULL DEFAULT nextval('registration_step_seq'),
                    name VARCHAR DEFAULT NULL,
                    display_order INT NOT NULL DEFAULT 0,
                    opportunity_id INT NOT NULL,
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(id)
                );"
            );

            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_step__step_id ON registration_step (id);");
            __try("ALTER TABLE registration_step ADD CONSTRAINT FK_registration_step__opportunity FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_step__opportunity_id ON registration_step (opportunity_id);");
        }

        if (!__column_exists('registration_field_configuration', "step_id")) {
            __exec("ALTER TABLE registration_field_configuration ADD COLUMN step_id INT NULL;");
            __try("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_registration_field_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_field_configuration__step_id ON registration_field_configuration (step_id);");
        }

        if (!__column_exists('registration_file_configuration', "step_id")) {
            __exec("ALTER TABLE registration_file_configuration ADD COLUMN step_id INT NULL;");
            __try("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_registration_file_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_file_configuration__step_id ON registration_file_configuration (step_id);");
        }
    },
    
    'Adiciona coluna de metadados na tabela da entidade RegistrationStep' => function () {
        if (!__column_exists('registration_step', 'metadata')) {
            __try("ALTER TABLE registration_step ADD COLUMN metadata json DEFAULT '{}'::json NOT NULL");
        }
    },
];
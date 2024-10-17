<?php

use function MapasCulturais\__exec;
use function MapasCulturais\__try;

return [
    'Criação das tabelas da entidade RegistrationStep' => function () {
        
        __exec("CREATE TABLE IF NOT EXISTS registration_step (
                    id INT NOT NULL, 
                    name INT DEFAULT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                    PRIMARY KEY(id));
        ");

        __exec("ALTER TABLE registration_field_configuration ADD COLUMN step_id INT NULL;");
        __exec("ALTER TABLE registration_file_configuration ADD COLUMN step_id INT NULL;");

        __try("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_registration_field_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_registration_file_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");

        __exec("CREATE INDEX IDX_registration_step__step_id ON registration_step (id);");
        __exec("CREATE INDEX IDX_registration_field_configuration__step_id ON registration_field_configuration (step_id);");
        __exec("CREATE INDEX IDX_registration_file_configuration__step_id ON registration_file_configuration (step_id);");

        return true;
    },
];
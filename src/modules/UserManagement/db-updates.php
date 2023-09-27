<?php

use function MapasCulturais\__exec;

return [
    'create table system_role' => function () {
        __exec("CREATE SEQUENCE system_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("CREATE TABLE system_role (
                    id INT NOT NULL, 
                    slug VARCHAR(64) NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    subsite_context BOOLEAN NOT NULL, 
                    permissions JSON DEFAULT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                    status SMALLINT NOT NULL, 
                    PRIMARY KEY(id));");
        __exec("COMMENT ON COLUMN system_role.permissions IS '(DC2Type:json_array)';");
    },

    'alter system_role.permissions comment' => function () {
        __exec("COMMENT ON COLUMN system_role.permissions IS '(DC2Type:json)';");
    },
];
<?php

use function MapasCulturais\__exec;

return [
    'create personal_access_token table' => function () {
        __exec("
            CREATE TABLE IF NOT EXISTS personal_access_token (
                id SERIAL PRIMARY KEY,
                user_id INTEGER NOT NULL REFERENCES \"usr\"(id) ON DELETE CASCADE,
                name VARCHAR(255) NOT NULL,
                token_hash VARCHAR(128) NOT NULL,
                token_prefix VARCHAR(16) NOT NULL,
                permissions JSON NOT NULL DEFAULT '[]',
                last_used_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
                update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                status SMALLINT NOT NULL DEFAULT 1
            )
        ");

        __exec("
            CREATE INDEX IF NOT EXISTS idx_pat_token_hash ON personal_access_token (token_hash)
        ");

        __exec("
            CREATE INDEX IF NOT EXISTS idx_pat_user_id ON personal_access_token (user_id)
        ");

        __exec("
            CREATE INDEX IF NOT EXISTS idx_pat_status ON personal_access_token (status)
        ");
    },
];

<?php

use function MapasCulturais\__exec;
use function MapasCulturais\__sequence_exists;
use function MapasCulturais\__table_exists;
use function MapasCulturais\__try;

return [
    'site settings: create table setting' => function () {
        if (!__table_exists('setting')) {
            __exec("
                CREATE TABLE setting (
                    id INT NOT NULL,
                    status SMALLINT NOT NULL,
                    metadata JSON DEFAULT '{}' NOT NULL,
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    subsite_id SMALLINT NULL,
                PRIMARY KEY(id))
            ");
        }
        if (!__table_exists('setting_meta')) {
            __exec("
                CREATE TABLE setting_meta (
                    object_id integer NOT NULL,
                    key character varying(32) NOT NULL,
                    value text,
                    id integer NOT NULL
                );
            ");
        }
    },

    'site settings: create sequences' => function () {
        if (!__sequence_exists('oc_setting_id_seq')) {
            __exec('CREATE SEQUENCE oc_setting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        }
        if (!__sequence_exists('setting_meta_id_seq')) {
            __exec('CREATE SEQUENCE setting_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        }
    },

    'site settings: insert default values' => function () {
        __exec("INSERT INTO setting (id, status, metadata, create_timestamp, update_timestamp, subsite_id) VALUES (nextval('oc_setting_id_seq'::regclass), 1, '{}', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, null)");

        __exec("INSERT INTO setting_meta (id, key, value, object_id) VALUES (nextval('setting_meta_id_seq'::regclass), 'mailer_email', 'sysadmin@localhost', 1)");
        __exec("INSERT INTO setting_meta (id, key, value, object_id) VALUES (nextval('setting_meta_id_seq'::regclass), 'mailer_host', 'mailhog', 1)");
        __exec("INSERT INTO setting_meta (id, key, value, object_id) VALUES (nextval('setting_meta_id_seq'::regclass), 'mailer_protocol', 'LOCAL', 1)");

        __exec("INSERT INTO setting_meta (id, key, value, object_id) VALUES (nextval('setting_meta_id_seq'::regclass), 'recaptcha_secret', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe', 1)");
        __exec("INSERT INTO setting_meta (id, key, value, object_id) VALUES (nextval('setting_meta_id_seq'::regclass), 'recaptcha_sitekey', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI', 1)");
        __exec("INSERT INTO setting_meta (object_id, \"key\", value, id) VALUES
            (1, 'logoDefaultTitle', 'Mapas', nextval('setting_meta_id_seq'::regclass)),
            (1, 'logoDefaultSubTitle', 'Culturais', nextval('setting_meta_id_seq'::regclass)),
            (1, 'primaryColor', '#117c83', nextval('setting_meta_id_seq'::regclass)),
            (1, 'secondaryColor', '#d14526', nextval('setting_meta_id_seq'::regclass)),
            (1, 'opportunitiesColor', '#d14426', nextval('setting_meta_id_seq'::regclass)),
            (1, 'agentsColor', '#ef7b45', nextval('setting_meta_id_seq'::regclass)),
            (1, 'eventsColor', '#9c4ec7', nextval('setting_meta_id_seq'::regclass)),
            (1, 'spacesColor', '#538d08', nextval('setting_meta_id_seq'::regclass)),
            (1, 'projectsColor', '#107c83', nextval('setting_meta_id_seq'::regclass)),
            (1, 'sealsColor', '#471363', nextval('setting_meta_id_seq'::regclass)),
            (1, 'logoColorPart1', '#2fd9e4', nextval('setting_meta_id_seq'::regclass)),
            (1, 'logoColorPart2', '#107c83', nextval('setting_meta_id_seq'::regclass)),
            (1, 'logoColorPart3', '#ea9e8c', nextval('setting_meta_id_seq'::regclass)),
            (1, 'logoColorPart4', '#d14426', nextval('setting_meta_id_seq'::regclass)),
            (1, 'zoom_default', '5', nextval('setting_meta_id_seq'::regclass)),
            (1, 'zoom_max', '22', nextval('setting_meta_id_seq'::regclass)),
            (1, 'zoom_min', '0', nextval('setting_meta_id_seq'::regclass))
        ");
    },
];

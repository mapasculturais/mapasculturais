<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();


return array(
    'alter table user add column profile_id' => function() use ($app, $conn){
        if($conn->fetchAll("SELECT column_name FROM information_schema.columns WHERE table_name = 'usr' AND column_name = 'profile_id'")){
            return true;
        }


        echo "adicionando coluna profile_id à tabela de usuários\n";

        $conn->executeQuery('ALTER TABLE usr ADD COLUMN profile_id INTEGER;');

        echo "criando user_profile_fk\n";
        $conn->executeQuery('ALTER TABLE ONLY usr ADD CONSTRAINT user_profile_fk FOREIGN KEY (profile_id) REFERENCES agent(id);');

        $agents = $conn->fetchAll("SELECT id, user_id FROM agent WHERE is_user_profile = TRUE");

        foreach($agents as $agent){
            echo "setando o user profile do usuário {$agent['user_id']} como o agente de id {$agent['id']}\n";
            $conn->executeQuery('UPDATE usr SET profile_id = ' . $agent['id'] . ' WHERE id = ' . $agent['user_id']);
        }

        echo "removendo a coluna is_user_profile da tabela agent\n";
        $conn->executeQuery('ALTER TABLE agent DROP COLUMN is_user_profile;');
    },

    'create table registration teste' => function() use ($conn){

        if($conn->fetchAll("SELECT tablename from pg_catalog.pg_tables WHERE tablename = 'registration' AND schemaname = 'public'")){
            return true;
        }

        echo "criando tabela registration\n";

        $conn->executeQuery("
            CREATE TABLE registration (
                id integer NOT NULL,
                project_id integer NOT NULL,
                category varchar(255),
                agent_id integer NOT NULL,
                create_timespamp timestamp without time zone DEFAULT now() NOT NULL,
                sent_timestamp timestamp without time zone,
                status integer NOT NULL
            );");

        echo "criando sequencia registration_id_seq\n";
        $conn->executeQuery("
            CREATE SEQUENCE registration_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;");

        echo "setando valor default do id\n";
        $conn->executeQuery("ALTER TABLE ONLY registration ALTER COLUMN id SET DEFAULT nextval('registration_id_seq'::regclass);");

        echo "criando chave primária\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_pkey PRIMARY KEY (id);");


        echo "criando agent FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_agent_id_fk FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE SET NULL;");


        echo "criando project FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_project_fk FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE;");


    },

    'create table registration_meta' => function() use($conn){
        if($conn->fetchAll("SELECT tablename from pg_catalog.pg_tables WHERE tablename = 'registration_meta' AND schemaname = 'public'")){
            return true;
        }

        echo "create table registration_meta\n";
        $conn->executeQuery("CREATE TABLE registration_meta (
                                object_id integer NOT NULL,
                                key character varying(32) NOT NULL,
                                value text
                            );");

        echo "create registration meta primary key\n";
        $conn->executeQuery("ALTER TABLE ONLY registration_meta
                                ADD CONSTRAINT registration_meta_pkey PRIMARY KEY (object_id, key);");

        echo "create registration_meta key value index\n";
        $conn->executeQuery("CREATE INDEX registration_meta_key_value_index ON registration_meta USING btree (key, value);");
    },

    'create table registration' => function() use ($conn){
        if($conn->fetchAll("SELECT tablename from pg_catalog.pg_tables WHERE tablename = 'registration_file_configuration' AND schemaname = 'public'")){
            return true;
        }

        echo "criando tabela registration\n";

        echo "create table registration_file_configuration\n";
        $conn->executeQuery("CREATE TABLE registration_file_configuration (
                                id SERIAL PRIMARY KEY,
                                project_id integer NOT NULL,
                                title character varying(255) NOT NULL,
                                description text,
                                required boolean NOT NULL DEFAULT false
                            );");

        echo "criando registration_file_configuration to project FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration_file_configuration
                                ADD CONSTRAINT registration_meta_project_fk FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE SET NULL;");
    },

    'alter table registration add column registration_categories' => function() use($conn){
        $conn->executeQuery('ALTER TABLE project ADD COLUMN registration_categories text;');
    }
);

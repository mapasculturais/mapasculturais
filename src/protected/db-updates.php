<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();


return array(
    'alter table user add column profile_id' => function() use ($app, $conn){
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
            
    'create table registration' => function() use ($conn){
        
        echo "criando tabela registration\n";
        
        $conn->executeQuery("
            CREATE TABLE registration (
                id integer NOT NULL,
                project_id integer NOT NULL,
                agent1_id integer NOT NULL,
                agent2_id integer,
                agent3_id integer,
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
        
        
        echo "criando agent1 FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_agent1_fk FOREIGN KEY (agent1_id) REFERENCES agent(id) ON DELETE SET NULL;");
        
        
        echo "criando agent2 FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_agent2_fk FOREIGN KEY (agent2_id) REFERENCES agent(id) ON DELETE SET NULL;");
        
        
        echo "criando agent3 FK\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_agent3_fk FOREIGN KEY (agent3_id) REFERENCES agent(id) ON DELETE SET NULL;");
        
        
        echo "\n";
        $conn->executeQuery("ALTER TABLE ONLY registration
                                ADD CONSTRAINT registration_project_fk FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE;");
        
        
        
    }
);

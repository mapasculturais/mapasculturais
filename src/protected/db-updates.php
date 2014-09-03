<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();
return array(
    'alter table space add column public' => function() use ($conn){
        $conn->executeQuery('ALTER TABLE space ADD COLUMN public BOOLEAN NOT NULL DEFAULT false;');
    },

    'alter table agent add column parent_id' => function() use ($conn){
        $conn->executeQuery('ALTER TABLE agent ADD COLUMN parent_id INTEGER;');
        $conn->executeQuery('ALTER TABLE ONLY agent ADD CONSTRAINT agent_agent_fk FOREIGN KEY (parent_id) REFERENCES agent(id);');
    },

    'alter occurrence fk' => function() use($conn) {
        $conn->executeQuery("
            ALTER TABLE public.event_occurrence_cancellation
                DROP CONSTRAINT event_occurrence_fk,
                ADD CONSTRAINT event_occurrence_fk
                   FOREIGN KEY (event_occurrence_id)
                   REFERENCES event_occurrence(id)
                   ON DELETE CASCADE");

        $conn->executeQuery("
            ALTER TABLE public.event_occurrence_recurrence
                DROP CONSTRAINT event_occurrence_fk,
                ADD CONSTRAINT event_occurrence_fk
                   FOREIGN KEY (event_occurrence_id)
                   REFERENCES event_occurrence(id)
                   ON DELETE CASCADE");
    },

    'alter tables to change CHAR to VARCHAR' => function() use ($conn){
        $conn->executeQuery('ALTER TABLE ONLY agent_meta ALTER COLUMN key TYPE character varying(32);');
        $conn->executeQuery('ALTER TABLE ONLY event_meta ALTER COLUMN key TYPE character varying(32);');
        $conn->executeQuery('ALTER TABLE ONLY space_meta ALTER COLUMN key TYPE character varying(32);');
        $conn->executeQuery('ALTER TABLE ONLY project_meta ALTER COLUMN key TYPE character varying(32);');
        $conn->executeQuery('ALTER TABLE ONLY metadata ALTER COLUMN key TYPE character varying(32);');
        $conn->executeQuery('ALTER TABLE ONLY file ALTER COLUMN grp TYPE character varying(32);');
    },

    'change owner of verified spaces of type Biblioteca Publica to agent SMB (id 592)' => function () use($app){

        $smb = $app->repo('Agent')->find(592);

        $spaces = $app->controller('space')->apiQuery([
            '@select' => 'id,name,singleUrl',
            //'isVerified' => 'EQ(true)',
            'owner' => 'IN(@Agent:425)', // 425 é o id do agente da secretaria
            'type' => 'EQ(20)' // id do tipo Biblioteca Publica
        ]);

        foreach ($spaces as $i => $space){
            echo ($i + 1) . ' - ' . $space['name'] . "... ";
            $b = $app->repo('Space')->find($space['id']);
            $b->owner = $smb;
            $b->save(true);
            echo "OK\n";
        }
    },

    'rename taxonomy term "Livre e Literatura" to "Livro e Literatura"' => function() use($app) {
        $term = $app->repo('Term')->findOneBy(array('term' => "Livre e Literatura"));
        $term->dump();
        $term->term = 'Livro e Literatura';
        $term->save(true);
    },


    // workflow

    'create authoriaztion_request schema' => function() use($app, $conn){
        $conn->beginTransaction();

        echo "creating sequence request_id_seq\n";
        $conn->executeQuery("
            CREATE SEQUENCE request_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;");

        echo "creating table request\n";
        $conn->executeQuery("
            CREATE TABLE request(
                id integer DEFAULT nextval('request_id_seq'::regclass) NOT NULL,
                requester_user_id integer NOT NULL,
                object_type character varying(255) NOT NULL,
                object_id integer NOT NULL,
                metadata text,
                type character varying(255) NOT NULL,
                create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
                action_timestamp timestamp without time zone DEFAULT NULL,
                status smallint NOT NULL
            )");

        echo "creating primary key\n";
        $conn->executeQuery("
            ALTER TABLE ONLY request
                ADD CONSTRAINT request_pk PRIMARY KEY (id);");


        echo "creating fk requester_user_fk\n";
        $conn->executeQuery("
            ALTER TABLE ONLY request
                ADD CONSTRAINT requester_user_fk FOREIGN KEY (requester_user_id) REFERENCES usr(id);");

        echo "creating index requester_user_index\n";
        $conn->executeQuery("
            CREATE INDEX requester_user_index
                ON request USING btree (requester_user_id, object_type, object_id);");

        echo "drop table authority_request\n";
        $conn->executeQuery("DROP TABLE authority_request");

        $conn->commit();
    },

    'create table notification' => function() use ($conn){
        $conn->beginTransaction();
        echo "creating sequence notification_id_seq\n";
        $conn->executeQuery("
            CREATE SEQUENCE notification_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;");

        echo "creating table notification\n";
        $conn->executeQuery("
            CREATE TABLE notification(
                id integer DEFAULT nextval('notification_id_seq'::regclass) NOT NULL,
                user_id integer NOT NULL,
                request_id integer DEFAULT NULL,
                message text NOT NULL,
                create_timestamp timestamp without time zone DEFAULT now() NOT NULL,
                action_timestamp timestamp without time zone DEFAULT NULL,
                status smallint NOT NULL
            );");

        echo "creating primary key\n";
        $conn->executeQuery("
            ALTER TABLE ONLY notification
                ADD CONSTRAINT notification_pk PRIMARY KEY (id);");

        echo "creating fk notification_user_fk\n";
        $conn->executeQuery("
            ALTER TABLE ONLY notification
                ADD CONSTRAINT notification_user_fk FOREIGN KEY (user_id) REFERENCES usr(id);");

        echo "creating fk notification_request_fk\n";
        $conn->executeQuery("
            ALTER TABLE ONLY notification
                ADD CONSTRAINT notification_request_fk FOREIGN KEY (request_id) REFERENCES request(id);");

        $conn->commit();
    },

    'Update Addresses of Children of Parent Spaces' => function() use ($app){
        $parentSpaces = $app->em->createQuery('SELECT s FROM \MapasCulturais\Entities\Space s WHERE s.parent IS NULL')->getResult();
        //echo count($parentSpaces);
        //return false;


        function dumpChildren($parent) {
            $children = $parent->children;
            echo "\n\n".'Atualizando endereço do espaço '.$parent->id.' '.$parent->name.': "'.$parent->endereco.'" e seus '.count($children).' filhos';
            foreach($children as $child){
                echo "\n".'---- '.$child->id.' '.$child->name.': "'.$child->endereco.'"';
                if(count($child->children)>0){
                    dumpChildren($child);
                }
            }
        }

        foreach($parentSpaces as $s){
            $children = $s->children;
            if(count($children)>0 && $s->endereco && !strpos($s->endereco, ' #UPDATING#')){
                //echo "\n".'Atualizando endereço do espaço '.$s->name.': '.$s->endereco;
                dumpChildren($s);
                $s->endereco = $s->endereco.' #UPDATING#';
                $s->name = $s->name.' #UPDATING#';
                $s->save(true);
            }
        }

        echo "\n\n".'Limpando dados temporários...';
        $cleanQuery = $app->em->createNativeQuery(
            "UPDATE space_meta SET value = REPLACE(value, ' #UPDATING#', '') WHERE key = 'endereco'", new \Doctrine\ORM\Query\ResultSetMapping()
        )->getOneOrNullResult();
        //$cleanQuery = $app->em->createQuery('SELECT s FROM \MapasCulturais\Entities\Space s WHERE s.name LIKE \'% #UPDATING#%\'')->getResult();
        
        return false;

    }
);

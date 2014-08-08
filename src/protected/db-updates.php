<?php
namespace MapasCulturais;

$app = App::i();
return array(
    'create authoriaztion_request schema' => function() use($app){
        $conn = $app->em->getConnection();
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
                requested_user_id integer NOT NULL,
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
        
        echo "creating fk requested_user_fk\n";
        $conn->executeQuery("
            ALTER TABLE ONLY request
                ADD CONSTRAINT requested_user_fk FOREIGN KEY (requested_user_id) REFERENCES usr(id);");
        
        echo "creating fk requester_user_fk\n";
        $conn->executeQuery("
            ALTER TABLE ONLY request
                ADD CONSTRAINT requester_user_fk FOREIGN KEY (requester_user_id) REFERENCES usr(id);");
        
        echo "creating index requested_user_index\n";
        $conn->executeQuery("
            CREATE INDEX requested_user_index 
                ON request USING btree (requested_user_id, object_type, object_id);");
        
        echo "creating index requester_user_index\n";
        $conn->executeQuery("
            CREATE INDEX requester_user_index 
                ON request USING btree (requester_user_id, object_type, object_id);");
        
        echo "drop table authority_request\n";
        $conn->executeQuery("DROP TABLE authority_request");
        
        $conn->commit();
    },

    'Virada 2014 - set events is verified' => function() use($app){
        $virada = $app->repo('Project')->find(4);

        $events = $app->repo('Event')->findBy(array('project' => $virada));

        $i = 0;
        foreach($events as $event){
            $i++;
            echo "$i - Definindo o evento \"{$event->name}\" como oficial.\n";
            $event->isVerified = true;
            $event->save();
        }

        $app->em->flush();
    },

    'Transform rule.duration to minutes and save the endsAt data' => function() use($app){
        $occurrences = $app->repo('EventOccurrence')->findAll();

        foreach($occurrences as $o){
            $value = (array) $o->rule;
            if(!empty($value['duration'])){
                @list($hours, $minutes) = explode('h', $value['duration']);
                $value['duration'] = intval($minutes) + intval($hours) * 60;
            }
            $value['endsAt'] = $o->endsAt->format('H:i');
            $o->rule = $value;
            echo "ev occurence start: {$value['startsAt']} for: {$value['duration']} minutes ends: {$value['endsAt']}\n";
            $o->save();
        }
    },

);

<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();

return array(
    'create table geo_division' => function() use($conn){
        echo 'creating geo_division_id_seq';
        $conn->executeQuery("
            CREATE SEQUENCE geo_division_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;
        ");

        echo 'creating table geo_division';

        $conn->executeQuery("
            CREATE TABLE geo_division (
                id integer DEFAULT nextval('geo_division_id_seq'::regclass) NOT NULL,
                parent_id integer,
                type character varying(32) NOT NULL,
                cod character varying(32),
                name character varying(128) NOT NULL,
                geom geometry,
                CONSTRAINT enforce_dims_geom CHECK ((st_ndims(geom) = 2)),
                CONSTRAINT enforce_geotype_geom CHECK (((geometrytype(geom) = 'MULTIPOLYGON'::text) OR (geom IS NULL))),
                CONSTRAINT enforce_srid_geom CHECK ((st_srid(geom) = 4326))
            );
        ");

        echo 'creating geo_division primary key';
        $conn->executeQuery("
            ALTER TABLE ONLY geo_division
                ADD CONSTRAINT geo_divisions_pkey PRIMARY KEY (id);
        ");

        echo 'creating index geo_division';
        $conn->executeQuery("CREATE INDEX geo_divisions_geom_idx ON geo_division USING gist (geom);");

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

//        return false;

    },

    'Fix Merge of "Update Addresses of Children of Parent Spaces" #UPDATING#'=>function () use ($app) {
        echo 'Fixing using query:';
        echo "\nUPDATE space SET name = REPLACE(name, ' #UPDATING#', '') WHERE name ILIKE '%#UPDATING#%'";
        $cleanQuery = $app->em->createNativeQuery(
            "UPDATE space SET name = REPLACE(name, ' #UPDATING#', '') WHERE name ILIKE '%#UPDATING#%'", new \Doctrine\ORM\Query\ResultSetMapping()
        )->getOneOrNullResult();
    },

    'alter table event_occurrence add column status' => function () use ($conn){
        $conn->executeQuery('ALTER TABLE event_occurrence ADD COLUMN status integer NOT NULL DEFAULT 1;');
        $conn->executeQuery("CREATE INDEX event_occurrence_status_index ON event_occurrence USING btree (status);");

        $conn->executeQuery("
            CREATE OR REPLACE FUNCTION recurring_event_occurrence_for(
              range_start TIMESTAMP,
              range_end  TIMESTAMP,
              time_zone CHARACTER VARYING,
              event_occurrence_limit INT
            )
              RETURNS SETOF event_occurrence
              LANGUAGE plpgsql STABLE
              AS \$BODY$
            DECLARE
              event event_occurrence;
              original_date DATE;
              original_date_in_zone DATE;
              start_time TIME;
              start_time_in_zone TIME;
              next_date DATE;
              next_time_in_zone TIME;
              duration INTERVAL;
              time_offset INTERVAL;
              r_start DATE := (timezone('UTC', range_start) AT TIME ZONE time_zone)::DATE;
              r_end DATE := (timezone('UTC', range_end) AT TIME ZONE time_zone)::DATE;

              recurrences_start DATE := CASE WHEN r_start < range_start THEN r_start ELSE range_start END;
              recurrences_end DATE := CASE WHEN r_end > range_end THEN r_end ELSE range_end END;

              inc_interval INTERVAL := '2 hours'::INTERVAL;

              ext_start TIMESTAMP := range_start::TIMESTAMP - inc_interval;
              ext_end   TIMESTAMP := range_end::TIMESTAMP   + inc_interval;
            BEGIN
              FOR event IN
                SELECT *
                  FROM event_occurrence
                  WHERE
                    status > 0
                    AND
                    (
                      (frequency = 'once' AND
                      ((starts_on IS NOT NULL AND ends_on IS NOT NULL AND starts_on <= r_end AND ends_on >= r_start) OR
                       (starts_on IS NOT NULL AND starts_on <= r_end AND starts_on >= r_start) OR
                       (starts_at <= range_end AND ends_at >= range_start)))

                      OR

                      (
                        frequency <> 'once' AND
                        (
                          ( starts_on IS NOT NULL AND starts_on <= ext_end ) OR
                          ( starts_at IS NOT NULL AND starts_at <= ext_end )
                        ) AND (
                          (until IS NULL AND ends_at IS NULL AND ends_on IS NULL) OR
                          (until IS NOT NULL AND until >= ext_start) OR
                          (ends_on IS NOT NULL AND ends_on >= ext_start) OR
                          (ends_at IS NOT NULL AND ends_at >= ext_start)
                        )
                      )
                    )

              LOOP
                IF event.frequency = 'once' THEN
                  RETURN NEXT event;
                  CONTINUE;
                END IF;

                -- All-day event
                IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
                  original_date := event.starts_on;
                  duration := '1 day'::interval;
                -- Multi-day event
                ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
                  original_date := event.starts_on;
                  duration := timezone(time_zone, event.ends_on) - timezone(time_zone, event.starts_on);
                -- Timespan event
                ELSE
                  original_date := event.starts_at::date;
                  original_date_in_zone := (timezone('UTC', event.starts_at) AT TIME ZONE event.timezone_name)::date;
                  start_time := event.starts_at::time;
                  start_time_in_zone := (timezone('UTC', event.starts_at) AT time ZONE event.timezone_name)::time;
                  duration := event.ends_at - event.starts_at;
                END IF;

                IF event.count IS NOT NULL THEN
                  recurrences_start := original_date;
                END IF;

                FOR next_date IN
                  SELECT occurrence
                    FROM (
                      SELECT * FROM recurrences_for(event, recurrences_start, recurrences_end) AS occurrence
                      UNION SELECT original_date
                      LIMIT event.count
                    ) AS occurrences
                    WHERE
                      occurrence::date <= recurrences_end AND
                      (occurrence + duration)::date >= recurrences_start AND
                      occurrence NOT IN (SELECT date FROM event_occurrence_cancellation WHERE event_occurrence_id = event.id)
                    LIMIT event_occurrence_limit
                LOOP
                  -- All-day event
                  IF event.starts_on IS NOT NULL AND event.ends_on IS NULL THEN
                    CONTINUE WHEN next_date < r_start OR next_date > r_end;
                    event.starts_on := next_date;

                  -- Multi-day event
                  ELSIF event.starts_on IS NOT NULL AND event.ends_on IS NOT NULL THEN
                    event.starts_on := next_date;
                    CONTINUE WHEN event.starts_on > r_end;
                    event.ends_on := next_date + duration;
                    CONTINUE WHEN event.ends_on < r_start;

                  -- Timespan event
                  ELSE
                    next_time_in_zone := (timezone('UTC', (next_date + start_time)) at time zone event.timezone_name)::time;
                    time_offset := (original_date_in_zone + next_time_in_zone) - (original_date_in_zone + start_time_in_zone);
                    event.starts_at := next_date + start_time - time_offset;

                    CONTINUE WHEN event.starts_at > range_end;
                    event.ends_at := event.starts_at + duration;
                    CONTINUE WHEN event.ends_at < range_start;
                  END IF;

                  RETURN NEXT event;
                END LOOP;
              END LOOP;
              RETURN;
            END;
            \$BODY$;
            ");

    }
);

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
);

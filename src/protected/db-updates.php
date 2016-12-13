<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();


function __table_exists($table_name) {
    $app = App::i();
    $em = $app->em;
    $conn = $em->getConnection();
    
    if($conn->fetchAll("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = '$table_name';")){
        return true;
    } else {
        return false;
    }
}

function __column_exists($table_name, $column_name) {
    $app = App::i();
    $em = $app->em;
    $conn = $em->getConnection();
    
    if($conn->fetchAll("SELECT column_name FROM information_schema.columns WHERE table_name='$table_name' and column_name='$column_name'")){
        return true;
    } else {
        return false;
    }
}

return [
    'new random id generator' => function () use ($conn) {
        $conn->executeQuery("
            CREATE SEQUENCE pseudo_random_id_seq
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;");

        $conn->executeQuery("
            CREATE OR REPLACE FUNCTION pseudo_random_id_generator() returns int AS $$
                DECLARE
                    l1 int;
                    l2 int;
                    r1 int;
                    r2 int;
                    VALUE int;
                    i int:=0;
                BEGIN
                    VALUE:= nextval('pseudo_random_id_seq');
                    l1:= (VALUE >> 16) & 65535;
                    r1:= VALUE & 65535;
                    WHILE i < 3 LOOP
                        l2 := r1;
                        r2 := l1 # ((((1366 * r1 + 150889) % 714025) / 714025.0) * 32767)::int;
                        l1 := l2;
                        r1 := r2;
                        i := i + 1;
                    END LOOP;
                    RETURN ((r1 << 16) + l1);
                END;
            $$ LANGUAGE plpgsql strict immutable;");
    },

    'migrate gender' => function() use ($conn) {
        $conn->executeQuery("UPDATE agent_meta SET value='Homem' WHERE key='genero' AND value='Masculino'");
        $conn->executeQuery("UPDATE agent_meta SET value='Mulher' WHERE key='genero' AND value='Feminino'");
    },


    'remove circular references again... ;)' => function() use ($conn) {
        $conn->executeQuery("UPDATE agent SET parent_id = null WHERE id = parent_id");

        $conn->executeQuery("UPDATE agent SET parent_id = null WHERE id IN (SELECT profile_id FROM usr)");

        return false; // executa todas as vezes só para garantir...
    },

    'create table user apps' => function() use ($conn) {
        if(__table_exists('user_app')){
            echo "TABLE user_app ALREADY EXISTS";
            return true;
        }
        
        $conn->executeQuery("CREATE TABLE user_app (
                                public_key character varying(64) NOT NULL,
                                private_key character varying(128) NOT NULL,
                                user_id integer NOT NULL,
                                name text NOT NULL,
                                status integer NOT NULL,
                                create_timestamp timestamp NOT NULL
                                );");

        $conn->executeQuery("ALTER TABLE ONLY user_app ADD CONSTRAINT user_app_pk PRIMARY KEY (public_key);");

        $conn->executeQuery("ALTER TABLE ONLY user_app ADD CONSTRAINT usr_user_app_fk FOREIGN KEY (user_id) REFERENCES usr(id);");

    },


    'create table user_meta' => function() use ($conn) {

        if(__table_exists('user_meta')){
            echo "TABLE user_meta ALREADY EXISTS";
            return true;
        }

        $conn->executeQuery("CREATE TABLE user_meta (
                                object_id integer NOT NULL,
                                key character varying(32) NOT NULL,
                                value text,
                                id integer NOT NULL);");

        $conn->executeQuery("CREATE SEQUENCE user_meta_id_seq
                                START WITH 1
                                INCREMENT BY 1
                                NO MINVALUE
                                NO MAXVALUE
                                CACHE 1;");

        $conn->executeQuery("ALTER SEQUENCE user_meta_id_seq OWNED BY user_meta.id;");
        $conn->executeQuery("ALTER TABLE ONLY user_meta ALTER COLUMN id SET DEFAULT nextval('user_meta_id_seq'::regclass);");
        $conn->executeQuery("ALTER TABLE ONLY user_meta ADD CONSTRAINT user_meta_pk PRIMARY KEY (id);");
        $conn->executeQuery("CREATE INDEX user_meta_owner_key_index ON user_meta USING btree (object_id, key);");
        $conn->executeQuery("CREATE INDEX user_meta_owner_key_value_index ON user_meta USING btree (object_id, key, value);");
        $conn->executeQuery("ALTER TABLE ONLY user_meta ADD CONSTRAINT usr_user_meta_fk FOREIGN KEY (object_id) REFERENCES usr(id);");
    },

    'create seal and seal relation tables' => function() use ($conn) {

        if(__table_exists('seal')){
            echo "TABLE seal ALREADY EXISTS";
            return true;
        }

        $conn->executeQuery("CREATE TABLE seal (id INT NOT NULL, agent_id INT NOT NULL, name VARCHAR(255) NOT NULL, short_description TEXT DEFAULT NULL, long_description TEXT DEFAULT NULL, valid_period SMALLINT NOT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status SMALLINT NOT NULL, certificate_text TEXT DEFAULT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE SEQUENCE seal_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("CREATE TABLE seal_relation (id INT NOT NULL, seal_id INT DEFAULT NULL, object_id INT NOT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status SMALLINT DEFAULT NULL, object_type VARCHAR(255) NOT NULL, agent_id INTEGER NOT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE SEQUENCE seal_relation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("CREATE TABLE seal_meta (id INT NOT NULL, object_id INT DEFAULT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE SEQUENCE seal_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER TABLE seal ADD CONSTRAINT seal_fk FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE seal_meta ADD CONSTRAINT seal_meta_fk FOREIGN KEY (object_id) REFERENCES seal (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE ONLY seal_relation ADD CONSTRAINT seal_relation_fk FOREIGN KEY (seal_id) REFERENCES seal(id);");

    },

    'resize entity meta key columns' => function() use($conn) {
        $conn->executeQuery('ALTER TABLE space_meta ALTER COLUMN key TYPE varchar(128)');
        $conn->executeQuery('ALTER TABLE agent_meta ALTER COLUMN key TYPE varchar(128)');
        $conn->executeQuery('ALTER TABLE event_meta ALTER COLUMN key TYPE varchar(128)');
        $conn->executeQuery('ALTER TABLE project_meta ALTER COLUMN key TYPE varchar(128)');
        $conn->executeQuery('ALTER TABLE user_meta ALTER COLUMN key TYPE varchar(128)');
    },


    'create registration field configuration table' => function () use($conn){
        if(__table_exists('registration_field_configuration')){
            echo "TABLE registration_field_configuration ALREADY EXISTS";
            return true;
        }
        $conn->executeQuery("CREATE TABLE registration_field_configuration (id INT NOT NULL, project_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, categories TEXT DEFAULT NULL, required BOOLEAN NOT NULL, field_type VARCHAR(255) NOT NULL, field_options VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE INDEX IDX_60C85CB1166D1F9C ON registration_field_configuration (project_id);");
        $conn->executeQuery("COMMENT ON COLUMN registration_field_configuration.categories IS '(DC2Type:array)';");
        $conn->executeQuery("CREATE SEQUENCE registration_field_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_60C85CB1166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },

    'alter table registration_file_configuration add categories' => function () use($conn){
        if(__column_exists('registration_file_configuration', 'categories')){
            echo "ALREADY APPLIED";
            return true;
        }
        
        $conn->executeQuery("ALTER TABLE registration_file_configuration DROP CONSTRAINT IF EXISTS registration_meta_project_fk;");
        $conn->executeQuery("ALTER TABLE registration_file_configuration ADD categories TEXT DEFAULT NULL;");
        $conn->executeQuery("ALTER TABLE registration_file_configuration ALTER id DROP DEFAULT;");
        $conn->executeQuery("ALTER TABLE registration_file_configuration ALTER project_id DROP NOT NULL;");
        $conn->executeQuery("ALTER TABLE registration_file_configuration ALTER required DROP DEFAULT;");
        $conn->executeQuery("COMMENT ON COLUMN registration_file_configuration.categories IS '(DC2Type:array)';");
        $conn->executeQuery("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_209C792E166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },

    'verified seal migration' => function () use($conn){
        $agent_id = $conn->fetchColumn("select profile_id
                    from usr
                    where id = (
                        select min(usr_id)
                        from role
                        where name = 'superAdmin'
                    )");
	    $conn->executeQuery(
            "INSERT INTO seal VALUES(
                1,
                $agent_id,
                'Selo Mapas',
                'Descrição curta Selo Mapas','Descrição longa Selo Mapas',0,CURRENT_TIMESTAMP,1
            );"
        );
 	    $conn->executeQuery("INSERT INTO seal_relation
            SELECT nextval('seal_relation_id_seq'), 1, id, CURRENT_TIMESTAMP, 1, 'MapasCulturais\Entities\Agent', $agent_id FROM agent WHERE is_verified = 't';");
 	    $conn->executeQuery("INSERT INTO seal_relation SELECT nextval('seal_relation_id_seq'), 1, id, CURRENT_TIMESTAMP, 1, 'MapasCulturais\Entities\Space', $agent_id FROM space WHERE is_verified = 't';");
 	    $conn->executeQuery("INSERT INTO seal_relation SELECT nextval('seal_relation_id_seq'), 1, id, CURRENT_TIMESTAMP, 1, 'MapasCulturais\Entities\Project', $agent_id FROM project WHERE is_verified = 't';");
 	    $conn->executeQuery("INSERT INTO seal_relation SELECT nextval('seal_relation_id_seq'), 1, id, CURRENT_TIMESTAMP, 1, 'MapasCulturais\Entities\Event', $agent_id FROM event WHERE is_verified = 't';");
    },

    'create update timestamp entities' => function () use($conn) {
    	$conn->executeQuery("ALTER TABLE agent ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
    	$conn->executeQuery("ALTER TABLE space ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
    	$conn->executeQuery("ALTER TABLE project ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
    	$conn->executeQuery("ALTER TABLE event ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
    	$conn->executeQuery("ALTER TABLE seal ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
    },
            
    'update entities last_update_timestamp with user last log timestamp' => function () use($conn,$app) {
        $agents = $conn->fetchAll("SELECT a.id, u.last_login_timestamp FROM agent a, usr u WHERE u.id = a.user_id");
        
        foreach($agents as $agent){
            $agent = (object) $agent;
            $conn->executeQuery("UPDATE space SET update_timestamp = '{$agent->last_login_timestamp}' WHERE agent_id = {$agent->id} AND update_timestamp IS NULL");
            $conn->executeQuery("UPDATE event SET update_timestamp = '{$agent->last_login_timestamp}' WHERE agent_id = {$agent->id} AND update_timestamp IS NULL");
            $conn->executeQuery("UPDATE seal SET update_timestamp = '{$agent->last_login_timestamp}' WHERE agent_id = {$agent->id} AND update_timestamp IS NULL");
            $conn->executeQuery("UPDATE project SET update_timestamp = '{$agent->last_login_timestamp}' WHERE agent_id = {$agent->id} AND update_timestamp IS NULL");
        }
        
        $conn->executeQuery("UPDATE agent SET update_timestamp = u.last_login_timestamp FROM (SELECT id, last_login_timestamp FROM usr) AS u WHERE user_id = u.id AND update_timestamp IS NULL");
    },

    'Fix field options field type from registration field configuration' => function () use($conn) {
        $conn->executeQuery("ALTER TABLE registration_field_configuration ALTER COLUMN field_options TYPE text;");
    },

    'Created owner seal relation field' => function () use($conn) {
        $conn->executeQuery("ALTER TABLE seal_relation ADD COLUMN owner_id INTEGER;");
        $agent_id = $conn->fetchColumn("select profile_id
                    from usr
                    where id = (
                        select min(usr_id)
                        from role
                        where name = 'superAdmin'
                    )");
        $conn->executeQuery("UPDATE seal_relation SET owner_id = '$agent_id' WHERE owner_id IS NULL;");
    },

    'Add field for maximum size from registration field configuration' => function () use($conn) {
        $conn->executeQuery("ALTER TABLE registration_field_configuration ADD COLUMN max_size text;");
    },
    
    'create avatar thumbs' => function() use($conn){
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Agent' AND object_id NOT IN (SELECT id FROM agent)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Space' AND object_id NOT IN (SELECT id FROM space)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Project' AND object_id NOT IN (SELECT id FROM project)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Event' AND object_id NOT IN (SELECT id FROM event)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Seal' AND object_id NOT IN (SELECT id FROM seal)");
        
        $files = $this->repo('SealFile')->findBy(['group' => 'avatar']);
        
        foreach($files as $f){
            $f->transform('avatarSmall');
            $f->transform('avatarMedium');
            $f->transform('avatarBig');
        }
        
        $this->disableAccessControl();
    }
];

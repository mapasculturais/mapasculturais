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

function __sequence_exists($sequence_name) {
    $app = App::i();
    $em = $app->em;
    $conn = $em->getConnection();

    if($conn->fetchAll("SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = 'public' AND sequence_name = '$sequence_name';")){
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

function __exec($sql){
    $app = App::i();
    $em = $app->em;
    $conn = $em->getConnection();

    try{
        $conn->executeQuery($sql);
    } catch (Exception $ex) {
        echo "
SQL ========================= 
$sql
-----------------------------
";
        throw $ex;
    }
}

function __try($sql, $cb = null){
    try{
        __exec($sql);
    } catch (\Exception $ex) {
        if($cb){
            $cb($ex, $sql);
        } else {
            $msg = $ex->getMessage();
            echo "
ERROR ==============================
$sql
------------------------------------
$msg
====================================

";
        }
    }
}

$updates = [];
$registered_taxonomies = $this->_register['taxonomies']['by-id'];

foreach($registered_taxonomies as $def){
    $updates['update taxonomy slug ' . $def->slug] = function() use( $conn, $def ) {
        $conn->executeQuery("UPDATE term SET taxonomy = '{$def->slug}' WHERE taxonomy = '{$def->id}'");
    };
}


return [

    'alter tablel term taxonomy type' => function() use ($conn) {
        $conn->executeQuery("ALTER TABLE term ALTER taxonomy TYPE VARCHAR(64);");
        $conn->executeQuery("ALTER TABLE term ALTER taxonomy DROP DEFAULT;");
    },

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

    'remove orphan events again' => function() use($conn){
        $conn->executeQuery("DELETE FROM event_meta WHERE object_id IN (SELECT id FROM event WHERE agent_id IS NULL)");
        $conn->executeQuery("DELETE FROM event WHERE agent_id IS NULL");
        return false;
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

    'create saas tables' => function () use($conn) {
        if(__table_exists('saas')){
            return true;
        }
        $conn->executeQuery("CREATE TABLE saas (id INT NOT NULL, name VARCHAR(255) NOT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status SMALLINT NOT NULL, agent_id INTEGER NOT NULL, PRIMARY KEY(id), url VARCHAR(255) NOT NULL, url_parent VARCHAR(255), slug VARCHAR(50) NOT NULL, namespace VARCHAR(50) NOT NULL);");
        $conn->executeQuery("CREATE SEQUENCE saas_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("CREATE TABLE saas_meta ( object_id integer NOT NULL, key character varying(128) NOT NULL, value text, id integer NOT NULL);");
        $conn->executeQuery("CREATE SEQUENCE saas_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER TABLE ONLY saas_meta ADD CONSTRAINT saas_saas_meta_fk FOREIGN KEY (object_id) REFERENCES saas(id);");
    },

    'rename saas tables to subsite' => function () use($conn) {
        if(__table_exists('subsite')){
            return true;
        }
        $conn->executeQuery("ALTER TABLE saas RENAME TO subsite");
        $conn->executeQuery("ALTER TABLE saas_meta RENAME TO subsite_meta");
        $conn->executeQuery("ALTER SEQUENCE saas_id_seq RENAME TO subsite_id_seq");
        $conn->executeQuery("ALTER SEQUENCE saas_meta_id_seq RENAME TO subsite_meta_id_seq");
    },

    'remove parent_url and add alias_url' => function () use($conn) {
        if(__column_exists('subsite', 'alias_url')){
            return true;
        }
        $conn->executeQuery("ALTER TABLE subsite DROP COLUMN url_parent");
        $conn->executeQuery("ALTER TABLE subsite ADD COLUMN alias_url VARCHAR(255) DEFAULT NULL;");

        $conn->executeQuery("CREATE INDEX url_index ON subsite (url);");
        $conn->executeQuery("CREATE INDEX alias_url_index ON subsite (alias_url);");

    },


    'verified seal migration' => function () use($conn){
        if($id = $conn->fetchColumn("SELECT id FROM seal WHERE id = 1")){
            return true;
        }
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
        if(__column_exists('agent', 'update_timestamp')){
            echo " ALREADY APPLIED update_timestamp FIELD CREATION ON agent TABLE. ";
        } else {
    	    $conn->executeQuery("ALTER TABLE agent ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
        }
        if(__column_exists('space', 'update_timestamp')){
            echo "ALREADY APPLIED update_timestamp FIELD CREATION ON space TABLE. ";
        } else {
    	    $conn->executeQuery("ALTER TABLE space ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
        }
        if(__column_exists('project', 'update_timestamp')){
            echo "ALREADY APPLIED update_timestamp FIELD CREATION ON project TABLE. ";
        } else {
    	    $conn->executeQuery("ALTER TABLE project ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
        }

        if(__column_exists('event', 'update_timestamp')){
            echo "ALREADY APPLIED update_timestamp FIELD CREATION ON event TABLE. ";
        } else {
    	    $conn->executeQuery("ALTER TABLE event ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
        }
        if(__column_exists('seal', 'update_timestamp')){
            echo "ALREADY APPLIED update_timestamp FIELD CREATION ON seal TABLE. ";
        } else {
    	    $conn->executeQuery("ALTER TABLE seal ADD COLUMN update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE;");
        }

    },

    'alter table role add column subsite_id' => function () use($conn) {
        if(__column_exists('role', 'subsite_id')){
            return true;
        }
        
    	$conn->executeQuery("ALTER TABLE role DROP CONSTRAINT IF EXISTS role_user_fk;");
    	$conn->executeQuery("ALTER TABLE role DROP CONSTRAINT IF EXISTS role_unique;");
        $conn->executeQuery("ALTER TABLE role ADD subsite_id INT DEFAULT NULL;");
        $conn->executeQuery("ALTER TABLE role ALTER id DROP DEFAULT;");
        $conn->executeQuery("ALTER TABLE role ALTER usr_id DROP NOT NULL;");
        $conn->executeQuery("ALTER TABLE role ADD CONSTRAINT FK_57698A6AC69D3FB FOREIGN KEY (usr_id) REFERENCES usr (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE role ADD CONSTRAINT FK_57698A6AC79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("CREATE INDEX IDX_57698A6AC79C849A ON role (subsite_id);");
    },

    'Fix field options field type from registration field configuration' => function () use($conn) {
        $conn->executeQuery("ALTER TABLE registration_field_configuration ALTER COLUMN field_options TYPE text;");
    },

    'ADD columns subsite_id' => function () use($conn) {
        if(!__column_exists('space', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE space ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE space ADD CONSTRAINT FK_2972C13AC79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_2972C13AC79C849A ON space (subsite_id);");
        }

        if(!__column_exists('agent', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE agent ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE agent ADD CONSTRAINT FK_268B9C9DC79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_268B9C9DC79C849A ON agent (subsite_id);");
        }

        if(!__column_exists('event', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE event ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7C79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_3BAE0AA7C79C849A ON event (subsite_id);");
        }

        if(!__column_exists('project', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE project ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEC79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_2FB3D0EEC79C849A ON project (subsite_id);");
        }

        if(!__column_exists('seal', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE seal ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE seal ADD CONSTRAINT FK_2E30AE30C79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_2E30AE30C79C849A ON seal (subsite_id);");
        }

        if(!__column_exists('registration', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE registration ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A7C79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_62A8A7A7C79C849A ON registration (subsite_id);");
        }

        if(!__column_exists('user_app', 'subsite_id')){
            $conn->executeQuery("ALTER TABLE user_app ADD subsite_id INT DEFAULT NULL;");
            $conn->executeQuery("ALTER TABLE user_app ADD CONSTRAINT FK_22781144C79C849A FOREIGN KEY (subsite_id) REFERENCES subsite (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("CREATE INDEX IDX_22781144C79C849A ON user_app (subsite_id);");
        }
    },

    'remove subsite slug column' => function () use($conn) {
        if(!__column_exists('subsite', 'slug')){
            return true;
        }
        
        $conn->executeQuery("ALTER TABLE subsite DROP COLUMN slug;");
    },

    'add subsite verified_seals column' => function () use($conn) {
        if(__column_exists('subsite', 'verified_seals')){
            return true;
        }
        $conn->executeQuery("ALTER TABLE subsite ADD verified_seals VARCHAR(512) DEFAULT '[]';");
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
        if(__column_exists('seal_relation', 'owner_id')){
            echo "ALREADY APPLIED";
            return true;
        }

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

    'create table pcache' => function () use($conn) {
        if(__table_exists('pcache')){
            echo 'tabela pcache já foi criada';
            return true;

        }
        $conn->executeQuery("CREATE TABLE pcache (id INT NOT NULL, user_id INT NOT NULL, action VARCHAR(255) NOT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, object_type VARCHAR(255) NOT NULL, object_id INT DEFAULT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE INDEX IDX_3D853098A76ED395 ON pcache (user_id);");
        $conn->executeQuery("CREATE INDEX IDX_3D853098232D562B ON pcache (object_id);");
        $conn->executeQuery("CREATE INDEX pcache_owner_idx ON pcache (object_type, object_id);");
        $conn->executeQuery("CREATE INDEX pcache_permission_idx ON pcache (object_type, object_id, action);");
        $conn->executeQuery("CREATE INDEX pcache_permission_user_idx ON pcache (object_type, object_id, action, user_id);");
        $conn->executeQuery("ALTER TABLE pcache ADD CONSTRAINT FK_3D853098A76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");

    },

    'function create pcache id sequence 2' => function () use ($conn) {
        if(__sequence_exists('pcache_id_seq')){
            echo 'sequencia pcache_id_seq já existe';
            return true;
        }
        $conn->executeQuery("CREATE SEQUENCE pcache_id_seq
                                START WITH 1
                                INCREMENT BY 1
                                NO MINVALUE
                                NO MAXVALUE
                                CACHE 1;");

        $conn->executeQuery("ALTER TABLE ONLY pcache ALTER COLUMN id SET DEFAULT nextval('pcache_id_seq'::regclass);");

    },

    'Add field for maximum size from registration field configuration' => function () use($conn) {
        if(__column_exists('registration_field_configuration', 'max_size')){
            return true;
        }
        $conn->executeQuery("ALTER TABLE registration_field_configuration ADD COLUMN max_size text;");
    },

    'Add notification type for compliant and suggestion messages' => function () use($conn) {
        if(__table_exists('notification_meta')) {
            echo "ALREADY APPLIED";
            return true;
        }
        $conn->executeQuery("CREATE TABLE notification_meta (id INT NOT NULL, object_id INT DEFAULT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE SEQUENCE notification_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER TABLE notification_meta ADD CONSTRAINT notification_meta_fk FOREIGN KEY (object_id) REFERENCES notification (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },

    'create entity revision tables' => function() use($conn) {
        if(__table_exists('entity_revision')) {
            echo "ALREADY APPLIED";
            return true;
        }

        $conn->executeQuery("CREATE SEQUENCE entity_revision_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("CREATE SEQUENCE revision_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("CREATE TABLE entity_revision (id INT NOT NULL, user_id INT DEFAULT NULL, object_id INT NOT NULL, object_type VARCHAR(255) NOT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(255) NOT NULL, message TEXT NOT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("CREATE TABLE entity_revision_revision_data (revision_id INT NOT NULL, revision_data_id INT NOT NULL, PRIMARY KEY(revision_id, revision_data_id));");
        $conn->executeQuery("CREATE TABLE entity_revision_data (id INT NOT NULL, timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id));");
        $conn->executeQuery("ALTER TABLE entity_revision ADD CONSTRAINT entity_revision_usr_fk FOREIGN KEY (user_id) REFERENCES usr (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE entity_revision_revision_data ADD CONSTRAINT revision_data_entity_revision_fk FOREIGN KEY (revision_id) REFERENCES entity_revision (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE entity_revision_revision_data ADD CONSTRAINT revision_data_revision_data_fk FOREIGN KEY (revision_data_id) REFERENCES entity_revision_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE");
    },

    'ALTER TABLE file ADD COLUMN path' => function () use ($conn) {
        if(__column_exists('file', 'path')){
            return true;
        }
        $conn->executeQuery("CREATE INDEX file_owner_index ON file (object_type, object_id);");
        $conn->executeQuery("CREATE INDEX file_group_index ON file (grp);");

        $conn->executeQuery("ALTER TABLE file ADD path VARCHAR(1024) DEFAULT NULL;");

    },

    'create avatar thumbs' => function() use($conn){
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Agent' AND object_id NOT IN (SELECT id FROM agent)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Space' AND object_id NOT IN (SELECT id FROM space)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Project' AND object_id NOT IN (SELECT id FROM project)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Event' AND object_id NOT IN (SELECT id FROM event)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Seal' AND object_id NOT IN (SELECT id FROM seal)");

        $files = $this->repo('SealFile')->findBy(['group' => 'avatar']);
        echo count($files) . " ARQUIVOS\n";
        foreach($files as $f){
            $f->transform('avatarSmall');
            $f->transform('avatarMedium');
            $f->transform('avatarBig');
        }

        $this->disableAccessControl();
    },

    'create avatar thumbs' => function() use($conn){
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Agent' AND object_id NOT IN (SELECT id FROM agent)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Space' AND object_id NOT IN (SELECT id FROM space)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Project' AND object_id NOT IN (SELECT id FROM project)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Event' AND object_id NOT IN (SELECT id FROM event)");
        $conn->executeQuery("DELETE FROM file WHERE object_type = 'MapasCulturais\Entities\Seal' AND object_id NOT IN (SELECT id FROM seal)");

        $files = $this->repo('SealFile')->findBy(['group' => 'avatar']);
        echo count($files) . " ARQUIVOS\n";
        foreach($files as $f){
            $f->transform('avatarSmall');
            $f->transform('avatarMedium');
            $f->transform('avatarBig');
        }

        $this->disableAccessControl();
    },

    '*_meta drop all indexes again' => function () use($conn) {

        foreach(['subsite', 'agent', 'user', 'event', 'space', 'project', 'seal', 'registration', 'notification'] as $prefix){
            $table = "{$prefix}_meta";

            // seleciona todos os indeces exceto PK
            $indexes = $conn->fetchAll("
                SELECT i.relname as indname
                FROM pg_index as idx
                        JOIN pg_class as i ON i.oid = idx.indexrelid
                        JOIN pg_am as am ON i.relam = am.oid
                        JOIN pg_namespace as ns ON
                                ns.oid = i.relnamespace AND ns.nspname = ANY(current_schemas(false))

                WHERE
                        idx.indrelid::regclass::varchar = '{$table}' AND
                        i.relname NOT IN (SELECT constraint_name FROM information_schema.table_constraints);");

            foreach($indexes as $index){
                echo "DROP INDEX {$index['indname']}\n";
                $conn->executeQuery("DROP INDEX {$index['indname']}");
            }
        }
        $conn->executeQuery("ALTER TABLE seal_relation ADD COLUMN validate_date DATE;");
    },
    'recreate *_meta indexes' => function() use($conn) {

        __try("DELETE FROM subsite_meta WHERE object_id NOT IN (SELECT id FROM subsite)");
        __try("DELETE FROM agent_meta WHERE object_id NOT IN (SELECT id FROM agent)");
        __try("DELETE FROM space_meta WHERE object_id NOT IN (SELECT id FROM space)");
        __try("DELETE FROM project_meta WHERE object_id NOT IN (SELECT id FROM project)");
        __try("DELETE FROM event_meta WHERE object_id NOT IN (SELECT id FROM event)");
        __try("DELETE FROM user_meta WHERE object_id NOT IN (SELECT id FROM usr)");
        __try("DELETE FROM seal_meta WHERE object_id NOT IN (SELECT id FROM seal)");
        __try("DELETE FROM registration_meta WHERE object_id NOT IN (SELECT id FROM registration)");
        __try("DELETE FROM notification_meta WHERE object_id NOT IN (SELECT id FROM notification)");

        __try("ALTER TABLE subsite_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE subsite_meta DROP CONSTRAINT IF EXISTS FK_780702F5232D562B;");
        __try("ALTER TABLE subsite_meta ADD CONSTRAINT FK_780702F5232D562B FOREIGN KEY (object_id) REFERENCES subsite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("ALTER TABLE subsite_meta ADD PRIMARY KEY (id);");

        __try("CREATE INDEX subsite_meta_owner_key_idx ON subsite_meta (object_id, key);");
        __try("CREATE INDEX subsite_meta_owner_idx ON subsite_meta (object_id);");

        __try("ALTER TABLE agent_meta DROP CONSTRAINT agent_agent_meta_fk;");
        __try("ALTER TABLE agent_meta ALTER id DROP DEFAULT;");
        __try("ALTER TABLE agent_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE agent_meta DROP CONSTRAINT IF EXISTS FK_7A69AED6232D562B;");
        __try("ALTER TABLE agent_meta ADD CONSTRAINT FK_7A69AED6232D562B FOREIGN KEY (object_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX agent_meta_owner_key_idx ON agent_meta (object_id, key);");
        __try("CREATE INDEX agent_meta_owner_idx ON agent_meta (object_id);");

        __try("ALTER TABLE user_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE user_meta DROP CONSTRAINT IF EXISTS FK_AD7358FC232D562B;");
        __try("ALTER TABLE user_meta ADD CONSTRAINT FK_AD7358FC232D562B FOREIGN KEY (object_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("ALTER TABLE user_meta ADD PRIMARY KEY (id);");

        __try("CREATE INDEX user_meta_owner_key_idx ON user_meta (object_id, key);");
        __try("CREATE INDEX user_meta_owner_idx ON user_meta (object_id);");

        __try("ALTER TABLE event_meta DROP CONSTRAINT event_project_meta_fk;");
        __try("ALTER TABLE event_meta ALTER id DROP DEFAULT;");
        __try("ALTER TABLE event_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE event_meta DROP CONSTRAINT IF EXISTS FK_C839589E232D562B;");
        __try("ALTER TABLE event_meta ADD CONSTRAINT FK_C839589E232D562B FOREIGN KEY (object_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX event_meta_owner_key_idx ON event_meta (object_id, key);");
        __try("CREATE INDEX event_meta_owner_idx ON event_meta (object_id);");

        __try("ALTER TABLE space_meta DROP CONSTRAINT space_space_meta_fk;");
        __try("ALTER TABLE space_meta ALTER id DROP DEFAULT;");
        __try("ALTER TABLE space_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE space_meta DROP CONSTRAINT IF EXISTS FK_BC846EBF232D562B;");
        __try("ALTER TABLE space_meta ADD CONSTRAINT FK_BC846EBF232D562B FOREIGN KEY (object_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX space_meta_owner_key_idx ON space_meta (object_id, key);");
        __try("CREATE INDEX space_meta_owner_idx ON space_meta (object_id);");

        __try("ALTER TABLE project_meta DROP CONSTRAINT project_project_meta_fk;");
        __try("ALTER TABLE project_meta ALTER id DROP DEFAULT;");
        __try("ALTER TABLE project_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE project_meta DROP CONSTRAINT IF EXISTS FK_EE63DC2D232D562B;");
        __try("ALTER TABLE project_meta ADD CONSTRAINT FK_EE63DC2D232D562B FOREIGN KEY (object_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX project_meta_owner_key_idx ON project_meta (object_id, key);");
        __try("CREATE INDEX project_meta_owner_idx ON project_meta (object_id);");

        __try("ALTER TABLE seal_meta DROP CONSTRAINT seal_meta_fk;");
        __try("ALTER TABLE seal_meta ALTER object_id SET NOT NULL;");
        __try("ALTER TABLE seal_meta DROP CONSTRAINT IF EXISTS FK_A92E5E22232D562B;");
        __try("ALTER TABLE seal_meta ADD CONSTRAINT FK_A92E5E22232D562B FOREIGN KEY (object_id) REFERENCES seal (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX seal_meta_owner_key_idx ON seal_meta (object_id, key);");
        __try("CREATE INDEX seal_meta_owner_idx ON seal_meta (object_id);");

        __try("ALTER TABLE registration ADD PRIMARY KEY(id);");

        __try("ALTER TABLE registration_meta ALTER id DROP DEFAULT;");
        __try("ALTER TABLE registration_meta ALTER key TYPE VARCHAR(255);");
        __try("ALTER TABLE registration_meta DROP CONSTRAINT IF EXISTS FK_18CC03E9232D562B;");
        __try("ALTER TABLE registration_meta ADD CONSTRAINT FK_18CC03E9232D562B FOREIGN KEY (object_id) REFERENCES registration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX registration_meta_owner_key_idx ON registration_meta (object_id, key);");
        __try("CREATE INDEX registration_meta_owner_idx ON registration_meta (object_id);");

        __try("ALTER TABLE notification_meta DROP CONSTRAINT notification_meta_fk;");
        __try("ALTER TABLE notification_meta ALTER object_id SET NOT NULL;");
        __try("ALTER TABLE notification_meta DROP CONSTRAINT IF EXISTS FK_6FCE5F0F232D562B;");
        __try("ALTER TABLE notification_meta ADD CONSTRAINT FK_6FCE5F0F232D562B FOREIGN KEY (object_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __try("CREATE INDEX notification_meta_owner_key_idx ON notification_meta (object_id, key);");
        __try("CREATE INDEX notification_meta_owner_idx ON notification_meta (object_id);");

    },

    /**
     * Migrações Projeto -> Opurtunidade
     *
     * - files do grupo rules
     */

    'create permission cache pending table2' => function() use ($conn) {

        if(__table_exists('permission_cache_pending')){
            echo "TABLE permission_cache_pending ALREADY EXISTS";
            return true;
        }

        $conn->executeQuery("CREATE TABLE permission_cache_pending (
            id INT NOT NULL, 
            object_id INT NOT NULL, 
            object_type VARCHAR(255) NOT NULL, 
            
            PRIMARY KEY(id)
        );");
    },

    'create opportunity tables' => function () {
        if(!__table_exists('opportunity')){
            __exec("DELETE FROM registration_meta WHERE object_id IN (SELECT id FROM registration WHERE project_id NOT IN (SELECT id FROM project))");
            __exec("DELETE FROM registration WHERE project_id NOT IN (SELECT id FROM project)");

            // cria tabelas das oportunidades
            __exec("CREATE SEQUENCE opportunity_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
            __exec("CREATE TABLE opportunity (id INT NOT NULL, parent_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, type SMALLINT NOT NULL, name VARCHAR(255) NOT NULL, short_description TEXT DEFAULT NULL, long_description TEXT DEFAULT NULL, registration_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, registration_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, published_registrations BOOLEAN NOT NULL, registration_categories text DEFAULT NULL, create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status SMALLINT NOT NULL, subsite_id INT DEFAULT NULL, object_type VARCHAR(255) NOT NULL, object_id INT NOT NULL, PRIMARY KEY(id));");
            __exec("CREATE INDEX opportunity_owner_idx ON opportunity (agent_id);");
            __exec("CREATE INDEX opportunity_entity_idx ON opportunity (object_type, object_id);");
            __exec("CREATE INDEX opportunity_parent_idx ON opportunity (parent_id);");
            __exec("CREATE TABLE opportunity_meta (id INT NOT NULL, object_id INT NOT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id));");
            __exec("CREATE INDEX opportunity_meta_owner_idx ON opportunity_meta (object_id);");
            __exec("CREATE INDEX opportunity_meta_owner_key_idx ON opportunity_meta (object_id, key);");
            __exec("ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D7727ACA70 FOREIGN KEY (parent_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D73414710B FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("ALTER TABLE opportunity_meta ADD CONSTRAINT FK_2BB06D08232D562B FOREIGN KEY (object_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");

            // cria as oportunidades existentes
            __exec("
                INSERT INTO opportunity (
                    id, parent_id, agent_id, type, name, short_description, registration_categories,
                    long_description, registration_from, registration_to, published_registrations,
                    create_timestamp, update_timestamp, status, subsite_id, object_type, object_id
                ) (
                    SELECT
                        p.id, p.parent_id, p.agent_id, p.type, p.name, p.short_description, p.registration_categories,
                        p.long_description, p.registration_from, p.registration_to, p.published_registrations,
                        p.create_timestamp, p.update_timestamp, p.status, p.subsite_id, 'MapasCulturais\Entities\Project', p.id
                    FROM
                        project p
                    WHERE
                        p.id IN (SELECT DISTINCT(project_id) FROM registration) OR
                        p.id IN (SELECT DISTINCT(project_id) FROM registration_file_configuration) OR
                        p.id IN (SELECT DISTINCT(project_id) FROM registration_field_configuration) OR
                        p.use_registrations IS TRUE
                );");

            __exec("INSERT INTO opportunity_meta (id,object_id,key,value) SELECT id, object_id, key, value FROM project_meta WHERE object_id IN (SELECT id FROM opportunity)");
            __exec("INSERT INTO term_relation (term_id, object_type, object_id) SELECT term_id, 'MapasCulturais\Entities\Opportunity', object_id FROM term_relation WHERE object_type = 'MapasCulturais\Entities\Project' AND object_id IN (SELECT id FROM opportunity);");


            // modifica a tabela de projetos retirando o que tem de referencia a registration
            __exec("ALTER TABLE project DROP CONSTRAINT fk_2fb3d0eec79c849a;");
            __exec("ALTER TABLE project DROP registration_categories;");
            __exec("ALTER TABLE project DROP use_registrations;");
            __exec("ALTER TABLE project DROP published_registrations;");

            // ajusta a tabela de inscrições
            __exec("DELETE FROM registration WHERE agent_id NOT IN (SELECT id FROM agent)");

            __exec("ALTER TABLE registration DROP CONSTRAINT fk_62a8a7a7c79c849a;");
            
            __exec("ALTER TABLE registration RENAME COLUMN project_id TO opportunity_id;");
            __exec("ALTER TABLE registration ALTER id SET DEFAULT pseudo_random_id_generator();");
            __exec("ALTER TABLE registration ALTER status TYPE SMALLINT;");
            __exec("ALTER TABLE registration ALTER status DROP DEFAULT;");
            __exec("ALTER TABLE registration ALTER agents_data DROP DEFAULT;");
            __exec("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A79A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A73414710B FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IDX_62A8A7A79A34590F ON registration (opportunity_id);");
            __exec("CREATE INDEX IDX_62A8A7A73414710B ON registration (agent_id);");

            // ajusta a tabela
            __exec("ALTER TABLE registration_file_configuration DROP CONSTRAINT fk_209c792e166d1f9c;");
            __exec("ALTER TABLE registration_file_configuration RENAME COLUMN project_id TO opportunity_id;");
            __exec("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_209C792E9A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IDX_209C792E9A34590F ON registration_file_configuration (opportunity_id);");

            __exec("ALTER TABLE registration_field_configuration DROP CONSTRAINT fk_60c85cb1166d1f9c;");
            __exec("ALTER TABLE registration_field_configuration RENAME COLUMN project_id TO opportunity_id;");
            __exec("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_60C85CB19A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IDX_60C85CB19A34590F ON registration_field_configuration (opportunity_id);");

        }
    },
    
    'DROP CONSTRAINT registration_project_fk");' => function() {
        __exec("ALTER TABLE registration DROP CONSTRAINT IF EXISTS registration_project_fk ;");
    },

    'fix opportunity parent FK' => function() {
        __exec("ALTER TABLE opportunity DROP CONSTRAINT IF EXISTS FK_8389C3D7727ACA70;");
        __exec("UPDATE opportunity SET parent_id = null WHERE parent_id NOT IN (SELECT id FROM opportunity)");
        __try("ALTER TABLE opportunity DROP CONSTRAINT IF EXISTS opportunity_parent_fk;");
        __exec("ALTER TABLE opportunity ADD CONSTRAINT opportunity_parent_fk FOREIGN KEY (parent_id) REFERENCES opportunity (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },

    'fix opportunity type 35' => function(){
        __exec("UPDATE opportunity SET type = 45 WHERE type = 35");
    },
            
    'create opportunity sequence' => function () use ($conn) {
        if(__sequence_exists('opportunity_id_seq')){
            return true;
        }
        $last_id = $conn->fetchColumn ('SELECT max(id) FROM opportunity;');
        $last_id++;
        $conn->executeQuery("CREATE SEQUENCE opportunity_id_seq
                                START WITH $last_id
                                INCREMENT BY 1
                                NO MINVALUE
                                NO MAXVALUE
                                CACHE 1;");

        $conn->executeQuery("ALTER TABLE ONLY opportunity ALTER COLUMN id SET DEFAULT nextval('opportunity_id_seq'::regclass);");
        
    },
            
    'update opportunity_meta_id sequence' => function() use ($conn){
        $last_id = $conn->fetchColumn ('SELECT max(id) FROM opportunity_meta;');
        $last_id++;
        
        $conn->executeQuery("ALTER SEQUENCE opportunity_meta_id_seq START {$last_id} RESTART");
        
        $conn->executeQuery("ALTER TABLE ONLY opportunity_meta ALTER COLUMN id SET DEFAULT nextval('opportunity_meta_id_seq'::regclass);");
    },

    'rename opportunity_meta key isProjectPhase to isOpportunityPhase' => function() {
        __exec("UPDATE opportunity_meta SET key = 'isOpportunityPhase' WHERE key = 'isProjectPhase'");
    },
            
    'migrate introInscricoes value to shortDescription' => function() use($conn) {
        $values = $conn->fetchAll("SELECT * from opportunity_meta WHERE key = 'introInscricoes'");
        foreach($values as $value){
            $conn->executeQuery("UPDATE opportunity SET short_description = :desc WHERE id = :id", ['id' => $value['object_id'], 'desc' => $value['value']]);
        }
    },
    
    
    
    'ALTER TABLE registration ADD consolidated_result' => function () {
        if(!__column_exists('registration', 'consolidated_result')){
            __exec("ALTER TABLE registration ADD consolidated_result VARCHAR(255) DEFAULT NULL;");
        }
    },

    'create evaluation methods tables' => function (){
        if(__table_exists('evaluation_method_configuration')){
            echo "evaluation_method_configuration table already exists";
            return true;
        }
        __exec("CREATE SEQUENCE evaluationMethodConfiguration_meta_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("CREATE TABLE evaluation_method_configuration (id INT NOT NULL, opportunity_id INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id));");
        __exec("CREATE UNIQUE INDEX UNIQ_330CB54C9A34590F ON evaluation_method_configuration (opportunity_id);");
        __exec("CREATE TABLE evaluationMethodConfiguration_meta (id INT NOT NULL, object_id INT NOT NULL, key VARCHAR(255) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id));");
        __exec("CREATE INDEX evaluationMethodConfiguration_meta_owner_idx ON evaluationMethodConfiguration_meta (object_id);");
        __exec("CREATE INDEX evaluationMethodConfiguration_meta_owner_key_idx ON evaluationMethodConfiguration_meta (object_id, key);");
        __exec("ALTER TABLE evaluation_method_configuration ADD CONSTRAINT FK_330CB54C9A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE evaluationMethodConfiguration_meta ADD CONSTRAINT FK_D7EDF8B2232D562B FOREIGN KEY (object_id) REFERENCES evaluation_method_configuration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("CREATE SEQUENCE evaluation_method_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("ALTER SEQUENCE evaluation_method_configuration_id_seq OWNED BY evaluation_method_configuration.id;");
        __exec("ALTER TABLE ONLY evaluation_method_configuration ALTER COLUMN id SET DEFAULT nextval('evaluation_method_configuration_id_seq'::regclass);");


        $opportunities = $this->repo('Opportunity')->findAll();

        foreach($opportunities as $opportunity){
            __exec("INSERT INTO evaluation_method_configuration ( opportunity_id, type) VALUES ($opportunity->id, 'simple');");
        }
    },

    'create registration_evaluation table' => function(){
        if(__table_exists('registration_evaluation')){
            echo "ALREADY APPLIED";
            return true;
        }

        __exec("CREATE SEQUENCE registration_evaluation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("CREATE TABLE registration_evaluation (
            id INT NOT NULL,
            registration_id INT DEFAULT pseudo_random_id_generator() NOT NULL,
            user_id INT NOT NULL,
            result VARCHAR(255) DEFAULT NULL,
            evaluation_data TEXT NOT NULL,
            status SMALLINT DEFAULT NULL,
            PRIMARY KEY(id));");
        __exec("CREATE INDEX IDX_2E186C5C833D8F43 ON registration_evaluation (registration_id);");
        __exec("CREATE INDEX IDX_2E186C5CA76ED395 ON registration_evaluation (user_id);");
        __exec("ALTER TABLE registration_evaluation ADD CONSTRAINT FK_2E186C5C833D8F43 FOREIGN KEY (registration_id) REFERENCES registration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_evaluation ADD CONSTRAINT FK_2E186C5CA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },
            
    'ALTER TABLE opportunity ALTER type DROP NOT NULL;' => function() use($conn){
        $conn->executeUpdate('ALTER TABLE opportunity ALTER type DROP NOT NULL;');
    },
    
    'ALTER TABLE registration ADD consolidated_result' => function () {
        if(!__column_exists('registration', 'consolidated_result')){
            __exec("ALTER TABLE registration ADD consolidated_result VARCHAR(255) DEFAULT NULL;");
        }
    },

    'create seal relation renovation flag field' => function() use($conn) {
        if(__column_exists('seal_relation', 'renovation_request')){
            echo "ALREADY APPLIED";
            return true;
        }
        $conn->executeQuery("ALTER TABLE seal_relation ADD COLUMN renovation_request BOOLEAN;");
    },
    'create seal relation validate date' => function() use($conn) {
        if(__column_exists('seal_relation', 'validate_date')){
            echo "ALREADY APPLIED";
            return true;
        }

        $conn->executeQuery("ALTER TABLE seal_relation ADD COLUMN validate_date DATE;");
    },
        
    'update seal_relation set validate_date' => function() use ($conn) {
        
        $conn->executeQuery("UPDATE seal_relation SET validate_date = seal_relation.create_timestamp + cast(cast(s.valid_period as text) || 'month' as interval) FROM (SELECT id, valid_period FROM seal) AS s WHERE s.id = seal_id AND validate_date IS NULL;");
    },
            
    'refactor of entity meta keky value indexes' => function() use ($conn){
        $__try = function($sql) use ($conn){
            try{
                $conn->executeQuery($sql);
            } catch (\Exception $ex) {
            }
        };

        $__try("DROP INDEX subsite_meta_key_value_idx;");
        $__try("CREATE INDEX subsite_meta_key_idx ON subsite_meta(key);");
        $__try("DROP INDEX agent_meta_key_value_idx;");
        $__try("CREATE INDEX agent_meta_key_idx ON agent_meta(key);");
        $__try("DROP INDEX user_meta_key_value_idx;");
        $__try("CREATE INDEX user_meta_key_idx ON user_meta(key);");
        $__try("DROP INDEX event_meta_key_value_idx;");
        $__try("CREATE INDEX event_meta_key_idx ON event_meta(key);");
        $__try("DROP INDEX space_meta_key_value_idx;");
        $__try("CREATE INDEX space_meta_key_idx ON space_meta(key);");
        $__try("DROP INDEX project_meta_key_value_idx;");
        $__try("CREATE INDEX project_meta_key_idx ON project_meta(key);");
        $__try("DROP INDEX seal_meta_key_value_idx;");
        $__try("CREATE INDEX seal_meta_key_idx ON seal_meta(key);");
        $__try("CREATE INDEX registration_meta_key_idx ON registration_meta key;");
        $__try("DROP INDEX notification_meta_key_value_idx;");
        $__try("CREATE INDEX notification_meta_key_idx ON notification_meta(key);");
    },

    'DROP index registration_meta_value_idx' => function () use ($conn){
        __try("DROP INDEX registration_meta_value_idx;");
    },

    'altertable registration_file_and_files_add_order' => function () use($conn){
        if(__column_exists('registration_file_configuration', 'order')){
            echo "ALREADY APPLIED";
        } else {
            $conn->executeQuery("ALTER TABLE registration_file_configuration ADD COLUMN display_order SMALLINT DEFAULT 255;");
        }
        
        if(__column_exists('registration_field_configuration', 'order')){
            echo "ALREADY APPLIED";
        } else {
            $conn->executeQuery("ALTER TABLE registration_field_configuration ADD COLUMN display_order SMALLINT DEFAULT 255;");
        }

    },

    'replace subsite entidades_habilitadas values' => function () use($conn) {
        $rs = $conn->fetchAll("SELECT * FROM subsite_meta WHERE key = 'entidades_habilitadas'");
        
        foreach($rs as $r){
            $r = (object) $r;
            $value = preg_replace(['#Espa[^;]+os#i', '#Eventos#i', '#Agentes#i', '#Projetos#i', '#Oportunidades"i'], ['Spaces', 'Events', 'Agents', 'Projects', 'Opportunities'], $r->value);
            $conn->exec("UPDATE subsite_meta SET value = '{$value}' WHERE id = {$r->id}");
        }
    },
    'replace subsite cor entidades values' => function () use($conn) {
        $conn->executeQuery("UPDATE subsite_meta SET key = 'spaces_color' where key = 'cor_espacos';");
        $conn->executeQuery("UPDATE subsite_meta SET key = 'events_color' where key = 'cor_eventos';");
        $conn->executeQuery("UPDATE subsite_meta SET key = 'projects_color' where key = 'cor_projetos';");
        $conn->executeQuery("UPDATE subsite_meta SET key = 'agents_color' where key = 'cor_agentes';");
        $conn->executeQuery("UPDATE subsite_meta SET key = 'seals_color' where key = 'cor_selos';");
    },

    'ALTER TABLE file ADD private and update' => function () use ($conn) {
        if(__column_exists('file', 'private')){
            return true;
        }

        $conn->executeQuery("ALTER TABLE file ADD private BOOLEAN NOT NULL DEFAULT FALSE;");
        
        $conn->executeQuery("UPDATE file SET private = true WHERE grp LIKE 'rfc_%' OR grp = 'zipArchive'");
        
    },

    'fix subsite verifiedSeals array' => function() use($app){
        $subsites = $app->repo('Subsite')->findAll();
        foreach($subsites as $subsite){
            $subsite->setVerifiedSeals($subsite->verifiedSeals);
            $subsite->save(true);
        }

        return false;
    },
    
    'move private files' => function () use ($conn) {
        
        
        $files = App::i()->repo('File')->findBy(['private' => true]);
        
        $StorageConfig = App::i()->storage->config;
        
        foreach($files as $file) {
            
            
            // vou pegar a info de path direto do banco, sem usar o metodo do storage
            // para evitar erro com inconsistencias no banco
            $relative_path = $file->getRelativePath(false);
            
            if (!$relative_path) {
                echo "ATENCAO: Seu banco possui arquivos que não tem a informação de path' \n";
                continue;
            }
            
            $targetPath = str_replace('\\', '-', $StorageConfig['private_dir'] . $relative_path);
            
            $oldPath = str_replace('\\', '-', $StorageConfig['dir'] . $relative_path);
            
            if (file_exists($oldPath)) {
            
                if(!is_dir(dirname($targetPath)))
                    mkdir (dirname($targetPath), 0755, true);
                
                rename($oldPath, $targetPath);
            
            }
            
            
        }
        
        
        
    },

    'create permission cache sequence' => function() use ($conn) {

        $conn->executeQuery("CREATE SEQUENCE permission_cache_pending_seq
                                START WITH 1
                                INCREMENT BY 1
                                NO MINVALUE
                                NO MAXVALUE
                                CACHE 1;");
    },
	
	'create evaluation methods sequence' => function (){
        if(__sequence_exists('evaluation_method_configuration_id_seq')){
            echo "evaluation_method_configuration_id_seq sequence already exists";
            return true;
        }
        __exec("CREATE SEQUENCE evaluation_method_configuration_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("ALTER SEQUENCE evaluation_method_configuration_id_seq OWNED BY evaluation_method_configuration.id;");
        __exec("ALTER TABLE ONLY evaluation_method_configuration ALTER COLUMN id SET DEFAULT nextval('evaluation_method_configuration_id_seq'::regclass);");
		
		__exec("SELECT setval('evaluation_method_configuration_id_seq', (select max(id) from evaluation_method_configuration), true);");
    },

    'change opportunity field agent_id not null' => function() use ($conn) {
        $conn->executeQuery(" ALTER TABLE opportunity ALTER COLUMN agent_id SET NOT NULL ");
    },

    'alter table registration add column number' => function() use($conn) {
        if(!__column_exists('registration', 'number')){
            $conn->executeQuery("ALTER TABLE registration ADD COLUMN number VARCHAR(24)");
        }
    },

    'update registrations set number fixed'=> function () use($conn){
        echo "\nsalvando número da inscrição para oportunidades de uma só fase ou para a primeira fase das inscrições\n";
        $conn->executeQuery("UPDATE registration SET number = CONCAT('on-', id) WHERE opportunity_id IN (SELECT id FROM opportunity WHERE parent_id IS NULL)");
        $regs = $conn->fetchAll("
            SELECT r.id, m.value AS previous 
            FROM registration r 
            LEFT JOIN registration_meta m ON m.object_id = r.id AND m.key = 'previousPhaseRegistrationId'");
        
        echo "\nsalvando número da inscrição para demais fases das oportunidades\n";

        $registrations = [];

        foreach($regs as $reg){
            $reg = (object) $reg;
            $registrations[$reg->id] = $reg;
        }

        foreach($registrations as $reg){
            if(!$reg->previous){
                continue;
            }

            $current = $reg;
            
            while($current->previous){
                print_r($current);
                $current = $registrations[$current->previous];
            }

            echo "\n - inscrição de id {$reg->id} número {$current->id}\n";
            $conn->executeQuery("UPDATE registration SET number = 'on-{$current->id}' WHERE id = $reg->id");
        }
    },
    'alter table opportunity add column publishedPreliminaryRegistrations'=> function () use($conn){

        if(!__column_exists('opportunity', 'published_preliminary_registrations')){
            $conn->executeQuery("ALTER TABLE opportunity ADD COLUMN published_preliminary_registrations BOOLEAN NOT NULL DEFAULT FALSE;");
        }
    },
    'alter table opportunity add column publishedPreliminaryRegistrationsTimestamp'=> function () use($conn){

        if(!__column_exists('opportunity', 'published_preliminary_registrations_timestamp')){
            $conn->executeQuery("ALTER TABLE opportunity ADD COLUMN published_preliminary_registrations_timestamp timestamp(0) without time zone;");
        }
    },

    'insert city and states'=> function () use($conn) {

        if(__table_exists('state')){
            echo "TABLE state ALREADY EXISTS";
            return true;
        }
        if(__table_exists('city')){
            echo "TABLE city ALREADY EXISTS";
            return true;
        }

        $conn->executeQuery("CREATE TABLE state (
                                id integer NOT NULL,
                                code character varying(2) NOT NULL, 
                                name character varying(255) NOT NULL
                                );");

        $conn->executeQuery("ALTER TABLE ONLY state ADD CONSTRAINT state_id_pk PRIMARY KEY (id);");

        $conn->executeQuery("CREATE TABLE city (
                            id integer NOT NULL,
                            state_id integer NOT NULL, 
                            name character varying(255) NOT NULL
                            );");

        $conn->executeQuery("ALTER TABLE ONLY city ADD CONSTRAINT city_id_pk PRIMARY KEY (id);");

        $conn->executeQuery("ALTER TABLE ONLY city ADD CONSTRAINT state_id_fk FOREIGN KEY (state_id) REFERENCES state(id);");

        $conn->executeQuery("CREATE SEQUENCE state_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER SEQUENCE state_id_seq OWNED BY state.id;");
        $conn->executeQuery("ALTER TABLE ONLY state ALTER COLUMN id SET DEFAULT nextval('state_id_seq'::regclass);");

        $conn->executeQuery("CREATE SEQUENCE city_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        $conn->executeQuery("ALTER SEQUENCE city_id_seq OWNED BY city.id;");
        $conn->executeQuery("ALTER TABLE ONLY city ALTER COLUMN id SET DEFAULT nextval('city_id_seq'::regclass);");

        echo "\ninserindo estados e cidades\n";

        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (01, 'AC', 'Acre')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (02, 'AL', 'Alagoas')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (03, 'AP', 'Amapá')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (04, 'AM', 'Amazonas')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (05, 'BA', 'Bahia')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (06, 'CE', 'Ceará')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (07, 'DF', 'Distrito Federal')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (08, 'ES', 'Espírito Santo')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (09, 'GO', 'Goiás')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (10, 'MA', 'Maranhão')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (11, 'MT', 'Mato Grosso')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (12, 'MS', 'Mato Grosso do Sul')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (13, 'MG', 'Minas Gerais')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (14, 'PA', 'Pará')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (15, 'PB', 'Paraíba')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (16, 'PR', 'Paraná')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (17, 'PE', 'Pernambuco')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (18, 'PI', 'Piauí')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (19, 'RJ', 'Rio de Janeiro')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (20, 'RN', 'Rio Grande do Norte')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (21, 'RS', 'Rio Grande do Sul')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (22, 'RO', 'Rondônia')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (23, 'RR', 'Roraima')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (24, 'SC', 'Santa Catarina')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (25, 'SP', 'São Paulo')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (26, 'SE', 'Sergipe')");
        $conn->executeQuery("INSERT INTO state (id, code, name) VALUES (27, 'TO', 'Tocantins')");

        /* ******************************** A c r e ********************************* */
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0001, 01, 'Acrelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0002, 01, 'Assis Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0003, 01, 'Brasiléia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0004, 01, 'Bujari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0005, 01, 'Capixaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0006, 01, 'Cruzeiro do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0007, 01, 'Epitaciolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0008, 01, 'Feijó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0009, 01, 'Jordão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0010, 01, 'Mâncio Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0011, 01, 'Manoel Urbano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0012, 01, 'Marechal Thaumaturgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0013, 01, 'Plácido de Castro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0014, 01, 'Porto Acre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0015, 01, 'Porto Walter')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0016, 01, 'Rio Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0017, 01, 'Rodrigues Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0018, 01, 'Santa Rosa do Purus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0019, 01, 'Sena Madureira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0020, 01, 'Senador Guiomard')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0021, 01, 'Tarauacá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0022, 01, 'Xapuri')");


        /* ***************************** A l a g o a s ****************************** */
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0023, 02, 'Água Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0024, 02, 'Anadia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0025, 02, 'Arapiraca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0026, 02, 'Atalaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0027, 02, 'Barra de Santo Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0028, 02, 'Barra de São Miguel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0029, 02, 'Batalha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0030, 02, 'Belém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0031, 02, 'Belo Monte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0032, 02, 'Boca da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0033, 02, 'Branquinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0034, 02, 'Cacimbinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0035, 02, 'Cajueiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0036, 02, 'Campestre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0037, 02, 'Campo Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0038, 02, 'Campo Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0039, 02, 'Canapi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0040, 02, 'Capela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0041, 02, 'Carneiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0042, 02, 'Chã Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0043, 02, 'Coité do Nóia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0044, 02, 'Colônia Leopoldina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0045, 02, 'Coqueiro Seco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0046, 02, 'Coruripe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0047, 02, 'Craíbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0048, 02, 'Delmiro Gouveia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0049, 02, 'Dois Riachos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0050, 02, 'Estrela de Alagoas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0051, 02, 'Feira Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0052, 02, 'Feliz Deserto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0053, 02, 'Flexeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0054, 02, 'Girau do Ponciano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0055, 02, 'Ibateguara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0056, 02, 'Igaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0057, 02, 'Igreja Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0058, 02, 'Inhapi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0059, 02, 'Jacaré dos Homens')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0060, 02, 'Jacuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0061, 02, 'Japaratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0062, 02, 'Jaramataia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0063, 02, 'Joaquim Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0064, 02, 'Jundiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0065, 02, 'Junqueiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0066, 02, 'Lagoa da Canoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0067, 02, 'Limoeiro de Anadia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0068, 02, 'Maceió')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0069, 02, 'Major Isidoro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0070, 02, 'Mar Vermelho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0071, 02, 'Maragogi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0072, 02, 'Maravilha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0073, 02, 'Marechal Deodoro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0074, 02, 'Maribondo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0075, 02, 'Mata Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0076, 02, 'Matriz de Camaragibe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0077, 02, 'Messias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0078, 02, 'Minador do Negrão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0079, 02, 'Monteirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0080, 02, 'Murici')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0081, 02, 'Novo Lino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0082, 02, 'Olho d`Água das Flores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0083, 02, 'Olho d`Água do Casado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0084, 02, 'Olho d`Água Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0085, 02, 'Olivença')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0086, 02, 'Ouro Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0087, 02, 'Palestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0088, 02, 'Palmeira dos Índios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0089, 02, 'Pão de Açúcar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0090, 02, 'Pariconha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0091, 02, 'Paripueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0092, 02, 'Passo de Camaragibe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0093, 02, 'Paulo Jacinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0094, 02, 'Penedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0095, 02, 'Piaçabuçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0096, 02, 'Pilar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0097, 02, 'Pindoba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0098, 02, 'Piranhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0099, 02, 'Poço das Trincheiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0100, 02, 'Porto Calvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0101, 02, 'Porto de Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0102, 02, 'Porto Real do Colégio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0103, 02, 'Quebrangulo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0104, 02, 'Rio Largo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0105, 02, 'Roteiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0106, 02, 'Santa Luzia do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0107, 02, 'Santana do Ipanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0108, 02, 'Santana do Mundaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0109, 02, 'São Brás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0110, 02, 'São José da Laje')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0111, 02, 'São José da Tapera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0112, 02, 'São Luís do Quitunde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0113, 02, 'São Miguel dos Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0114, 02, 'São Miguel dos Milagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0115, 02, 'São Sebastião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0116, 02, 'Satuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0117, 02, 'Senador Rui Palmeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0118, 02, 'Tanque d`Arca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0119, 02, 'Taquarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0120, 02, 'Teotônio Vilela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0121, 02, 'Traipu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0122, 02, 'União dos Palmares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0123, 02, 'Viçosa')");


        /* ******************************* A m a p á ******************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0124, 03, 'Amapá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0125, 03, 'Calçoene')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0126, 03, 'Cutias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0127, 03, 'Ferreira Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0128, 03, 'Itaubal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0129, 03, 'Laranjal do Jari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0130, 03, 'Macapá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0131, 03, 'Mazagão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0132, 03, 'Oiapoque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0133, 03, 'Pedra Branca do Amaparí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0134, 03, 'Porto Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0135, 03, 'Pracuúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0136, 03, 'Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0137, 03, 'Serra do Navio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0138, 03, 'Tartarugalzinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0139, 03, 'Vitória do Jari')");


        /* **************************** A m a z o n a s ***************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0140, 04, 'Alvarães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0141, 04, 'Amaturá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0142, 04, 'namã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0143, 04, 'Anori')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0144, 04, 'Apuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0145, 04, 'Atalaia do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0146, 04, 'Autazes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0147, 04, 'Barcelos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0148, 04, 'Barreirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0149, 04, 'Benjamin Constant')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0150, 04, 'Beruri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0151, 04, 'Boa Vista do Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0152, 04, 'Boca do Acre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0153, 04, 'Borba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0154, 04, 'Caapiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0155, 04, 'Canutama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0156, 04, 'Carauari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0157, 04, 'Careiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0158, 04, 'Careiro da Várzea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0159, 04, 'Coari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0160, 04, 'Codajás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0161, 04, 'Eirunepé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0162, 04, 'Envira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0163, 04, 'Fonte Boa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0164, 04, 'Guajará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0165, 04, 'Humaitá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0166, 04, 'Ipixuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0167, 04, 'Iranduba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0168, 04, 'Itacoatiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0169, 04, 'Itamarati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0170, 04, 'Itapiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0171, 04, 'Japurá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0172, 04, 'Juruá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0173, 04, 'Jutaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0174, 04, 'Lábrea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0175, 04, 'Manacapuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0176, 04, 'Manaquiri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0177, 04, 'Manaus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0178, 04, 'Manicoré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0179, 04, 'Maraã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0180, 04, 'Maués')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0181, 04, 'Nhamundá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0182, 04, 'Nova Olinda do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0183, 04, 'Novo Airão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0184, 04, 'Novo Aripuanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0185, 04, 'Parintins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0186, 04, 'Pauini')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0187, 04, 'Presidente Figueiredo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0188, 04, 'Rio Preto da Eva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0189, 04, 'Santa Isabel do Rio Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0190, 04, 'Santo Antônio do Içá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0191, 04, 'São Gabriel da Cachoeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0192, 04, 'São Paulo de Olivença')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0193, 04, 'São Sebastião do Uatumã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0194, 04, 'Silves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0195, 04, 'Tabatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0196, 04, 'Tapauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0197, 04, 'Tefé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0198, 04, 'Tonantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0199, 04, 'Uarini')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0200, 04, 'Urucará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0201, 04, 'Urucurituba')");


        /* ******************************* B a h i a ******************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0202, 05, 'Abaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0203, 05, 'Abaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0204, 05, 'Acajutiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0205, 05, 'Adustina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0206, 05, 'Água Fria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0207, 05, 'Aiquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0208, 05, 'Alagoinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0209, 05, 'Alcobaça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0210, 05, 'Almadina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0211, 05, 'Amargosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0212, 05, 'Amélia Rodrigues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0213, 05, 'América Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0214, 05, 'Anagé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0215, 05, 'Andaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0216, 05, 'Andorinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0217, 05, 'Angical')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0218, 05, 'Anguera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0219, 05, 'Antas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0220, 05, 'Antônio Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0221, 05, 'Antônio Gonçalves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0222, 05, 'Aporá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0223, 05, 'Apuarema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0224, 05, 'Araças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0225, 05, 'Aracatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0226, 05, 'Araci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0227, 05, 'Aramari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0228, 05, 'Arataca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0229, 05, 'Aratuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0230, 05, 'Aurelino Leal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0231, 05, 'Baianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0232, 05, 'Baixa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0233, 05, 'Banzaê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0234, 05, 'Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0235, 05, 'Barra da Estiva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0236, 05, 'Barra do Choça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0237, 05, 'Barra do Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0238, 05, 'Barra do Rocha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0239, 05, 'Barreiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0240, 05, 'Barro Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0241, 05, 'Belmonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0242, 05, 'Belo Campo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0243, 05, 'Biritinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0244, 05, 'Boa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0245, 05, 'Boa Vista do Tupim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0246, 05, 'Bom Jesus da Lapa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0247, 05, 'Bom Jesus da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0248, 05, 'Boninal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0249, 05, 'Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0250, 05, 'Boquira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0251, 05, 'Botuporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0252, 05, 'Brejões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0253, 05, 'Brejolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0254, 05, 'Brotas de Macaúbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0255, 05, 'Brumado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0256, 05, 'Buerarema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0257, 05, 'Buritirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0258, 05, 'Caatiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0259, 05, 'Cabaceiras do Paraguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0260, 05, 'Cachoeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0261, 05, 'Caculé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0262, 05, 'Caém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0263, 05, 'Caetanos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0264, 05, 'Caetité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0265, 05, 'Cafarnaum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0266, 05, 'Cairu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0267, 05, 'Caldeirão Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0268, 05, 'Camacan')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0269, 05, 'Camaçari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0270, 05, 'Camamu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0271, 05, 'Campo Alegre de Lourdes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0272, 05, 'Campo Formoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0273, 05, 'Canápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0274, 05, 'Canarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0275, 05, 'Canavieiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0276, 05, 'Candeal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0277, 05, 'Candeias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0278, 05, 'Candiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0279, 05, 'Cândido Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0280, 05, 'Cansanção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0281, 05, 'Canudos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0282, 05, 'Capela do Alto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0283, 05, 'Capim Grosso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0284, 05, 'Caraíbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0285, 05, 'Caravelas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0286, 05, 'Cardeal da Silva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0287, 05, 'Carinhanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0288, 05, 'Casa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0289, 05, 'Castro Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0290, 05, 'Catolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0291, 05, 'Catu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0292, 05, 'Caturama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0293, 05, 'Central')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0294, 05, 'Chorrochó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0295, 05, 'Cícero Dantas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0296, 05, 'Cipó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0297, 05, 'Coaraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0298, 05, 'Cocos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0299, 05, 'Conceição da Feira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0300, 05, 'Conceição do Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0301, 05, 'Conceição do Coité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0302, 05, 'Conceição do Jacuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0303, 05, 'Conde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0304, 05, 'Condeúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0305, 05, 'Contendas do Sincorá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0306, 05, 'Coração de Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0307, 05, 'Cordeiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0308, 05, 'Coribe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0309, 05, 'Coronel João Sá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0310, 05, 'Correntina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0311, 05, 'Cotegipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0312, 05, 'Cravolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0313, 05, 'Crisópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0314, 05, 'Cristópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0315, 05, 'Cruz das Almas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0316, 05, 'Curaçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0317, 05, 'Dário Meira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0318, 05, 'Dias d`Ávila')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0319, 05, 'Dom Basílio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0320, 05, 'Dom Macedo Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0321, 05, 'Elísio Medrado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0322, 05, 'Encruzilhada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0323, 05, 'Entre Rios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0324, 05, 'Érico Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0325, 05, 'Esplanada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0326, 05, 'Euclides da Cunha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0327, 05, 'Eunápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0328, 05, 'Fátima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0329, 05, 'Feira da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0330, 05, 'Feira de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0331, 05, 'Filadélfia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0332, 05, 'Firmino Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0333, 05, 'Floresta Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0334, 05, 'Formosa do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0335, 05, 'Gandu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0336, 05, 'Gavião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0337, 05, 'Gentio do Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0338, 05, 'Glória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0339, 05, 'Gongogi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0340, 05, 'Governador Lomanto Júnior')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0341, 05, 'Governador Mangabeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0342, 05, 'Guajeru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0343, 05, 'Guanambi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0344, 05, 'Guaratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0345, 05, 'Heliópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0346, 05, 'Iaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0347, 05, 'Ibiassucê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0348, 05, 'Ibicaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0349, 05, 'Ibicoara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0350, 05, 'Ibicuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0351, 05, 'Ibipeba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0352, 05, 'Ibipitanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0353, 05, 'Ibiquera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0354, 05, 'Ibirapitanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0355, 05, 'Ibirapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0356, 05, 'Ibirataia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0357, 05, 'Ibitiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0358, 05, 'Ibititá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0359, 05, 'Ibotirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0360, 05, 'Ichu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0361, 05, 'Igaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0362, 05, 'Igrapiúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0363, 05, 'Iguaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0364, 05, 'Ilhéus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0365, 05, 'Inhambupe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0366, 05, 'Ipecaetá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0367, 05, 'Ipiaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0368, 05, 'Ipirá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0369, 05, 'Ipupiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0370, 05, 'Irajuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0371, 05, 'Iramaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0372, 05, 'Iraquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0373, 05, 'Irará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0374, 05, 'Irecê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0375, 05, 'Itabela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0376, 05, 'Itaberaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0377, 05, 'Itabuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0378, 05, 'Itacaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0379, 05, 'Itaeté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0380, 05, 'Itagi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0381, 05, 'Itagibá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0382, 05, 'Itagimirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0383, 05, 'Itaguaçu da Bahia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0384, 05, 'Itaju do Colônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0385, 05, 'Itajuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0386, 05, 'Itamaraju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0387, 05, 'Itamari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0388, 05, 'Itambé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0389, 05, 'Itanagra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0390, 05, 'Itanhém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0391, 05, 'Itaparica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0392, 05, 'Itapé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0393, 05, 'Itapebi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0394, 05, 'Itapetinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0395, 05, 'Itapicuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0396, 05, 'Itapitanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0397, 05, 'Itaquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0398, 05, 'Itarantim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0399, 05, 'Itatim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0400, 05, 'Itiruçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0401, 05, 'Itiúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0402, 05, 'Itororó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0403, 05, 'Ituaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0404, 05, 'Ituberá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0405, 05, 'Iuiú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0406, 05, 'Jaborandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0407, 05, 'Jacaraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0408, 05, 'Jacobina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0409, 05, 'Jaguaquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0410, 05, 'Jaguarari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0411, 05, 'Jaguaripe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0412, 05, 'Jandaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0413, 05, 'Jequié')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0414, 05, 'Jeremoabo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0415, 05, 'Jiquiriçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0416, 05, 'Jitaúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0417, 05, 'João Dourado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0418, 05, 'Juazeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0419, 05, 'Jucuruçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0420, 05, 'Jussara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0421, 05, 'Jussari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0422, 05, 'Jussiape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0423, 05, 'Lafaiete Coutinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0424, 05, 'Lagoa Real')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0425, 05, 'Laje')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0426, 05, 'Lajedão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0427, 05, 'Lajedinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0428, 05, 'Lajedo do Tabocal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0429, 05, 'Lamarão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0430, 05, 'Lapão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0431, 05, 'Lauro de Freitas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0432, 05, 'Lençóis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0433, 05, 'Licínio de Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0434, 05, 'Livramento de Nossa Senhora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0435, 05, 'Macajuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0436, 05, 'Macarani')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0437, 05, 'Macaúbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0438, 05, 'Macururé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0439, 05, 'Madre de Deus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0440, 05, 'Maetinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0441, 05, 'Maiquinique')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0442, 05, 'Mairi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0443, 05, 'Malhada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0444, 05, 'Malhada de Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0445, 05, 'Manoel Vitorino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0446, 05, 'Mansidão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0447, 05, 'Maracás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0448, 05, 'Maragogipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0449, 05, 'Maraú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0450, 05, 'Marcionílio Souza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0451, 05, 'Mascote')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0452, 05, 'Mata de São João')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0453, 05, 'Matina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0454, 05, 'Medeiros Neto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0455, 05, 'Miguel Calmon')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0456, 05, 'Milagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0457, 05, 'Mirangaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0458, 05, 'Mirante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0459, 05, 'Monte Santo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0460, 05, 'Morpará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0461, 05, 'Morro do Chapéu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0462, 05, 'Mortugaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0463, 05, 'Mucugê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0464, 05, 'Mucuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0465, 05, 'Mulungu do Morro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0466, 05, 'Mundo Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0467, 05, 'Muniz Ferreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0468, 05, 'Muquém de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0469, 05, 'Muritiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0470, 05, 'Mutuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0471, 05, 'Nazaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0472, 05, 'Nilo Peçanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0473, 05, 'Nordestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0474, 05, 'Nova Canaã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0475, 05, 'Nova Fátima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0476, 05, 'Nova Ibiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0477, 05, 'Nova Itarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0478, 05, 'Nova Redenção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0479, 05, 'Nova Soure')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0480, 05, 'Nova Viçosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0481, 05, 'Novo Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0482, 05, 'Novo Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0483, 05, 'Olindina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0484, 05, 'Oliveira dos Brejinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0485, 05, 'Ouriçangas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0486, 05, 'Ourolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0487, 05, 'Palmas de Monte Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0488, 05, 'Palmeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0489, 05, 'Paramirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0490, 05, 'Paratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0491, 05, 'Paripiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0492, 05, 'Pau Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0493, 05, 'Paulo Afonso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0494, 05, 'Pé de Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0495, 05, 'Pedrão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0496, 05, 'Pedro Alexandre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0497, 05, 'Piatã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0498, 05, 'Pilão Arcado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0499, 05, 'Pindaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0500, 05, 'Pindobaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0501, 05, 'Pintadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0502, 05, 'Piraí do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0503, 05, 'Piripá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0504, 05, 'Piritiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0505, 05, 'Planaltino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0506, 05, 'Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0507, 05, 'Poções')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0508, 05, 'Pojuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0509, 05, 'Ponto Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0510, 05, 'Porto Seguro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0511, 05, 'Potiraguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0512, 05, 'Prado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0513, 05, 'Presidente Dutra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0514, 05, 'Presidente Jânio Quadros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0515, 05, 'Presidente Tancredo Neves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0516, 05, 'Queimadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0517, 05, 'Quijingue')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0518, 05, 'Quixabeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0519, 05, 'Rafael Jambeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0520, 05, 'Remanso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0521, 05, 'Retirolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0522, 05, 'Riachão das Neves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0523, 05, 'Riachão do Jacuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0524, 05, 'Riacho de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0525, 05, 'Ribeira do Amparo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0526, 05, 'Ribeira do Pombal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0527, 05, 'Ribeirão do Largo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0528, 05, 'Rio de Contas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0529, 05, 'Rio do Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0530, 05, 'Rio do Pires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0531, 05, 'Rio Real')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0532, 05, 'Rodelas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0533, 05, 'Ruy Barbosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0534, 05, 'Salinas da Margarida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0535, 05, 'Salvador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0536, 05, 'Santa Bárbara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0537, 05, 'Santa Brígida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0538, 05, 'Santa Cruz Cabrália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0539, 05, 'Santa Cruz da Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0540, 05, 'Santa Inês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0541, 05, 'Santa Luzia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0542, 05, 'Santa Maria da Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0543, 05, 'Santa Rita de Cássia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0544, 05, 'Santa Teresinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0545, 05, 'Santaluz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0546, 05, 'Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0547, 05, 'Santanópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0548, 05, 'Santo Amaro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0549, 05, 'Santo Antônio de Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0550, 05, 'Santo Estêvão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0551, 05, 'São Desidério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0552, 05, 'São Domingos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0553, 05, 'São Felipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0554, 05, 'São Félix')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0555, 05, 'São Félix do Coribe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0556, 05, 'São Francisco do Conde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0557, 05, 'São Gabriel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0558, 05, 'São Gonçalo dos Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0559, 05, 'São José da Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0560, 05, 'São José do Jacuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0561, 05, 'São Miguel das Matas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0562, 05, 'São Sebastião do Passé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0563, 05, 'Sapeaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0564, 05, 'Sátiro Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0565, 05, 'Saubara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0566, 05, 'Saúde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0567, 05, 'Seabra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0568, 05, 'Sebastião Laranjeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0569, 05, 'Senhor do Bonfim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0570, 05, 'Sento Sé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0571, 05, 'Serra do Ramalho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0572, 05, 'Serra Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0573, 05, 'Serra Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0574, 05, 'Serrinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0575, 05, 'Serrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0576, 05, 'Simões Filho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0577, 05, 'Sítio do Mato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0578, 05, 'Sítio do Quinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0579, 05, 'Sobradinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0580, 05, 'Souto Soares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0581, 05, 'Tabocas do Brejo Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0582, 05, 'Tanhaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0583, 05, 'Tanque Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0584, 05, 'Tanquinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0585, 05, 'Taperoá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0586, 05, 'Tapiramutá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0587, 05, 'Teixeira de Freitas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0588, 05, 'Teodoro Sampaio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0589, 05, 'Teofilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0590, 05, 'Teolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0591, 05, 'Terra Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0592, 05, 'Tremedal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0593, 05, 'Tucano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0594, 05, 'Uauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0595, 05, 'Ubaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0596, 05, 'Ubaitaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0597, 05, 'Ubatã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0598, 05, 'Uibaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0599, 05, 'Umburanas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0600, 05, 'Una')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0601, 05, 'Urandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0602, 05, 'Uruçuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0603, 05, 'Utinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0604, 05, 'Valença')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0605, 05, 'Valente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0606, 05, 'Várzea da Roça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0607, 05, 'Várzea do Poço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0608, 05, 'Várzea Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0609, 05, 'Varzedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0610, 05, 'Vera Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0611, 05, 'Vereda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0612, 05, 'Vitória da Conquista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0613, 05, 'Wagner')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0614, 05, 'Wanderley')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0615, 05, 'Wenceslau Guimarães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0616, 05, 'Xique-Xique')");


        /* ******************************* C e a r á ******************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0617, 06, 'Abaiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0618, 06, 'Acarapé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0619, 06, 'Acaraú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0620, 06, 'Acopiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0621, 06, 'Aiuaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0622, 06, 'Alcântaras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0623, 06, 'Altaneira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0624, 06, 'Alto Santo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0625, 06, 'Amontada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0626, 06, 'Antonina do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0627, 06, 'Apuiarés')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0628, 06, 'Aquiraz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0629, 06, 'Aracati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0630, 06, 'Aracoiaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0631, 06, 'Ararendá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0632, 06, 'Araripe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0633, 06, 'Aratuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0634, 06, 'Arneiroz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0635, 06, 'Assaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0636, 06, 'Aurora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0637, 06, 'Baixio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0638, 06, 'Banabuiú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0639, 06, 'Barbalha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0640, 06, 'Barreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0641, 06, 'Barro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0642, 06, 'Barroquinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0643, 06, 'Baturité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0644, 06, 'Beberibe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0645, 06, 'Bela Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0646, 06, 'Boa Viagem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0647, 06, 'Brejo Santo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0648, 06, 'Camocim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0649, 06, 'Campos Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0650, 06, 'Canindé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0651, 06, 'Capistrano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0652, 06, 'Caridade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0653, 06, 'Cariré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0654, 06, 'Caririaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0655, 06, 'Cariús')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0656, 06, 'Carnaubal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0657, 06, 'Cascavel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0658, 06, 'Catarina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0659, 06, 'Catunda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0660, 06, 'Caucaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0661, 06, 'Cedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0662, 06, 'Chaval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0663, 06, 'Choró')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0664, 06, 'Chorozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0665, 06, 'Coreaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0666, 06, 'Crateús')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0667, 06, 'Crato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0668, 06, 'Croatá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0669, 06, 'Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0670, 06, 'Deputado Irapuan Pinheiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0671, 06, 'Ererê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0672, 06, 'Eusébio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0673, 06, 'Farias Brito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0674, 06, 'Forquilha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0675, 06, 'Fortaleza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0676, 06, 'Fortim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0677, 06, 'Frecheirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0678, 06, 'General Sampaio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0679, 06, 'Graça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0680, 06, 'Granja')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0681, 06, 'Granjeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0682, 06, 'Groaíras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0683, 06, 'Guaiúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0684, 06, 'Guaraciaba do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0685, 06, 'Guaramiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0686, 06, 'Hidrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0687, 06, 'Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0688, 06, 'Ibaretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0689, 06, 'Ibiapina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0690, 06, 'Ibicuitinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0691, 06, 'Icapuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0692, 06, 'Icó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0693, 06, 'Iguatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0694, 06, 'Independência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0695, 06, 'Ipaporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0696, 06, 'Ipaumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0697, 06, 'Ipu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0698, 06, 'Ipueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0699, 06, 'Iracema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0700, 06, 'Irauçuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0701, 06, 'Itaiçaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0702, 06, 'Itaitinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0703, 06, 'Itapagé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0704, 06, 'Itapipoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0705, 06, 'Itapiúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0706, 06, 'Itarema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0707, 06, 'Itatira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0708, 06, 'Jaguaretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0709, 06, 'Jaguaribara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0710, 06, 'Jaguaribe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0711, 06, 'Jaguaruana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0712, 06, 'Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0713, 06, 'Jati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0714, 06, 'Jijoca de Jericoacoara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0715, 06, 'Juazeiro do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0716, 06, 'Jucás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0717, 06, 'Lavras da Mangabeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0718, 06, 'Limoeiro do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0719, 06, 'Madalena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0720, 06, 'Maracanaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0721, 06, 'Maranguape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0722, 06, 'Marco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0723, 06, 'Martinópole')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0724, 06, 'Massapê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0725, 06, 'Mauriti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0726, 06, 'Meruoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0727, 06, 'Milagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0728, 06, 'Milhã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0729, 06, 'Miraíma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0730, 06, 'Missão Velha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0731, 06, 'Mombaça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0732, 06, 'Monsenhor Tabosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0733, 06, 'Morada Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0734, 06, 'Moraújo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0735, 06, 'Morrinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0736, 06, 'Mucambo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0737, 06, 'Mulungu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0738, 06, 'Nova Olinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0739, 06, 'Nova Russas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0740, 06, 'Novo Oriente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0741, 06, 'Ocara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0742, 06, 'Orós')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0743, 06, 'Pacajus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0744, 06, 'Pacatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0745, 06, 'Pacoti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0746, 06, 'Pacujá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0747, 06, 'Palhano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0748, 06, 'Palmácia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0749, 06, 'Paracuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0750, 06, 'Paraipaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0751, 06, 'Parambu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0752, 06, 'Paramoti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0753, 06, 'Pedra Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0754, 06, 'Penaforte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0755, 06, 'Pentecoste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0756, 06, 'Pereiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0757, 06, 'Pindoretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0758, 06, 'Piquet Carneiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0759, 06, 'Pires Ferreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0760, 06, 'Poranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0761, 06, 'Porteiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0762, 06, 'Potengi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0763, 06, 'Potiretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0764, 06, 'Quiterianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0765, 06, 'Quixadá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0766, 06, 'Quixelô')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0767, 06, 'Quixeramobim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0768, 06, 'Quixeré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0769, 06, 'Redenção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0770, 06, 'Reriutaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0771, 06, 'Russas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0772, 06, 'Saboeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0773, 06, 'Salitre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0774, 06, 'Santa Quitéria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0775, 06, 'Santana do Acaraú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0776, 06, 'Santana do Cariri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0777, 06, 'São Benedito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0778, 06, 'São Gonçalo do Amarante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0779, 06, 'São João do Jaguaribe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0780, 06, 'São Luís do Curu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0781, 06, 'Senador Pompeu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0782, 06, 'Senador Sá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0783, 06, 'Sobral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0784, 06, 'Solonópole')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0785, 06, 'Tabuleiro do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0786, 06, 'Tamboril')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0787, 06, 'Tarrafas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0788, 06, 'Tauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0789, 06, 'Tejuçuoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0790, 06, 'Tianguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0791, 06, 'Trairi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0792, 06, 'Tururu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0793, 06, 'Ubajara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0794, 06, 'Umari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0795, 06, 'Umirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0796, 06, 'Uruburetama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0797, 06, 'Uruoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0798, 06, 'Varjota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0799, 06, 'Várzea Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0800, 06, 'Viçosa do Ceará')");


        /* ******************** D i s t r i t o   F e d e r a l ********************* */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0801, 07, 'Brasília')");


        /* ********************** E s p í r i t o   S a n t o *********************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0802, 08, 'Afonso Cláudio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0803, 08, 'Água Doce do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0804, 08, 'Águia Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0805, 08, 'Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0806, 08, 'Alfredo Chaves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0807, 08, 'Alto Rio Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0808, 08, 'Anchieta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0809, 08, 'Apiacá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0810, 08, 'Aracruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0811, 08, 'Atilio Vivacqua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0812, 08, 'Baixo Guandu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0813, 08, 'Barra de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0814, 08, 'Boa Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0815, 08, 'Bom Jesus do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0816, 08, 'Brejetuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0817, 08, 'Cachoeiro de Itapemirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0818, 08, 'Cariacica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0819, 08, 'Castelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0820, 08, 'Colatina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0821, 08, 'Conceição da Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0822, 08, 'Conceição do Castelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0823, 08, 'Divino de São Lourenço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0824, 08, 'Domingos Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0825, 08, 'Dores do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0826, 08, 'Ecoporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0827, 08, 'Fundão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0828, 08, 'Guaçuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0829, 08, 'Guarapari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0830, 08, 'Ibatiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0831, 08, 'Ibiraçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0832, 08, 'Ibitirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0833, 08, 'Iconha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0834, 08, 'Irupi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0835, 08, 'Itaguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0836, 08, 'Itapemirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0837, 08, 'Itarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0838, 08, 'Iúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0839, 08, 'Jaguaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0840, 08, 'Jerônimo Monteiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0841, 08, 'João Neiva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0842, 08, 'Laranja da Terra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0843, 08, 'Linhares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0844, 08, 'Mantenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0845, 08, 'Marataízes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0846, 08, 'Marechal Floriano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0847, 08, 'Marilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0848, 08, 'Mimoso do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0849, 08, 'Montanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0850, 08, 'Mucurici')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0851, 08, 'Muniz Freire')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0852, 08, 'Muqui')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0853, 08, 'Nova Venécia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0854, 08, 'Pancas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0855, 08, 'Pedro Canário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0856, 08, 'Pinheiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0857, 08, 'Piúma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0858, 08, 'Ponto Belo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0859, 08, 'Presidente Kennedy')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0860, 08, 'Rio Bananal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0861, 08, 'Rio Novo do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0862, 08, 'Santa Leopoldina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0863, 08, 'Santa Maria de Jetibá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0864, 08, 'Santa Teresa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0865, 08, 'São Domingos do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0866, 08, 'São Gabriel da Palha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0867, 08, 'São José do Calçado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0868, 08, 'São Mateus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0869, 08, 'São Roque do Canaã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0870, 08, 'Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0871, 08, 'Sooretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0872, 08, 'Vargem Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0873, 08, 'Venda Nova do Imigrante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0874, 08, 'Viana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0875, 08, 'Vila Pavão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0876, 08, 'Vila Valério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0877, 08, 'Vila Velha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0878, 08, 'Vitória')");


        /* ******************************* G o i á s ******************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0879, 09, 'Abadia de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0880, 09, 'Abadiânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0881, 09, 'Acreúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0882, 09, 'Adelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0883, 09, 'Água Fria de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0884, 09, 'Água Limpa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0885, 09, 'Águas Lindas de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0886, 09, 'Alexânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0887, 09, 'Aloândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0888, 09, 'Alto Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0889, 09, 'Alto Paraíso de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0890, 09, 'Alvorada do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0891, 09, 'Amaralina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0892, 09, 'Americano do Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0893, 09, 'Amorinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0894, 09, 'Anápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0895, 09, 'Anhanguera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0896, 09, 'Anicuns')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0897, 09, 'Aparecida de Goiânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0898, 09, 'Aparecida do Rio Doce')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0899, 09, 'Aporé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0900, 09, 'Araçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0901, 09, 'Aragarças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0902, 09, 'Aragoiânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0903, 09, 'Araguapaz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0904, 09, 'Arenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0905, 09, 'Aruanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0906, 09, 'Aurilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0907, 09, 'Avelinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0908, 09, 'Baliza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0909, 09, 'Barro Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0910, 09, 'Bela Vista de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0911, 09, 'Bom Jardim de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0912, 09, 'Bom Jesus de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0913, 09, 'Bonfinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0914, 09, 'Bonópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0915, 09, 'Brazabrantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0916, 09, 'Britânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0917, 09, 'Buriti Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0918, 09, 'Buriti de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0919, 09, 'Buritinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0920, 09, 'Cabeceiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0921, 09, 'Cachoeira Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0922, 09, 'Cachoeira de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0923, 09, 'Cachoeira Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0924, 09, 'Caçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0925, 09, 'Caiapônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0926, 09, 'Caldas Novas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0927, 09, 'Caldazinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0928, 09, 'Campestre de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0929, 09, 'Campinaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0930, 09, 'Campinorte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0931, 09, 'Campo Alegre de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0932, 09, 'Campos Belos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0933, 09, 'Campos Verdes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0934, 09, 'Carmo do Rio Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0935, 09, 'Castelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0936, 09, 'Catalão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0937, 09, 'Caturaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0938, 09, 'Cavalcante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0939, 09, 'Ceres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0940, 09, 'Cezarina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0941, 09, 'Chapadão do Céu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0942, 09, 'Cidade Ocidental')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0943, 09, 'Cocalzinho de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0944, 09, 'Colinas do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0945, 09, 'Córrego do Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0946, 09, 'Corumbá de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0947, 09, 'Corumbaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0948, 09, 'Cristalina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0949, 09, 'Cristianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0950, 09, 'Crixás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0951, 09, 'Cromínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0952, 09, 'Cumari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0953, 09, 'Damianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0954, 09, 'Damolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0955, 09, 'Davinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0956, 09, 'Diorama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0957, 09, 'Divinópolis de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0958, 09, 'Doverlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0959, 09, 'Edealina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0960, 09, 'Edéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0961, 09, 'Estrela do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0962, 09, 'Faina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0963, 09, 'Fazenda Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0964, 09, 'Firminópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0965, 09, 'Flores de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0966, 09, 'Formosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0967, 09, 'Formoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0968, 09, 'Goianápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0969, 09, 'Goiandira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0970, 09, 'Goianésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0971, 09, 'Goiânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0972, 09, 'Goianira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0973, 09, 'Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0974, 09, 'Goiatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0975, 09, 'Gouvelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0976, 09, 'Guapó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0977, 09, 'Guaraíta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0978, 09, 'Guarani de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0979, 09, 'Guarinos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0980, 09, 'Heitoraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0981, 09, 'Hidrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0982, 09, 'Hidrolina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0983, 09, 'Iaciara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0984, 09, 'Inaciolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0985, 09, 'Indiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0986, 09, 'Inhumas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0987, 09, 'Ipameri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0988, 09, 'Iporá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0989, 09, 'Israelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0990, 09, 'Itaberaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0991, 09, 'Itaguari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0992, 09, 'Itaguaru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0993, 09, 'Itajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0994, 09, 'Itapaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0995, 09, 'Itapirapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0996, 09, 'Itapuranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0997, 09, 'Itarumã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0998, 09, 'Itauçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (0999, 09, 'Itumbiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1000, 09, 'Ivolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1001, 09, 'Jandaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1002, 09, 'Jaraguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1003, 09, 'Jataí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1004, 09, 'Jaupaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1005, 09, 'Jesúpolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1006, 09, 'Joviânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1007, 09, 'Jussara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1008, 09, 'Leopoldo de Bulhões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1009, 09, 'Luziânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1010, 09, 'Mairipotaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1011, 09, 'Mambaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1012, 09, 'Mara Rosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1013, 09, 'Marzagão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1014, 09, 'Matrinchã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1015, 09, 'Maurilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1016, 09, 'Mimoso de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1017, 09, 'Minaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1018, 09, 'Mineiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1019, 09, 'Moiporá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1020, 09, 'Monte Alegre de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1021, 09, 'Montes Claros de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1022, 09, 'Montividiu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1023, 09, 'Montividiu do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1024, 09, 'Morrinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1025, 09, 'Morro Agudo de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1026, 09, 'Mossâmedes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1027, 09, 'Mozarlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1028, 09, 'Mundo Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1029, 09, 'Mutunópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1030, 09, 'Nazário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1031, 09, 'Nerópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1032, 09, 'Niquelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1033, 09, 'Nova América')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1034, 09, 'Nova Aurora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1035, 09, 'Nova Crixás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1036, 09, 'Nova Glória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1037, 09, 'Nova Iguaçu de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1038, 09, 'Nova Roma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1039, 09, 'Nova Veneza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1040, 09, 'Novo Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1041, 09, 'Novo Gama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1042, 09, 'Novo Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1043, 09, 'Orizona')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1044, 09, 'Ouro Verde de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1045, 09, 'Ouvidor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1046, 09, 'Padre Bernardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1047, 09, 'Palestina de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1048, 09, 'Palmeiras de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1049, 09, 'Palmelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1050, 09, 'Palminópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1051, 09, 'Panamá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1052, 09, 'Paranaiguara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1053, 09, 'Paraúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1054, 09, 'Perolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1055, 09, 'Petrolina de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1056, 09, 'Pilar de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1057, 09, 'Piracanjuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1058, 09, 'Piranhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1059, 09, 'Pirenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1060, 09, 'Pires do Rio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1061, 09, 'Planaltina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1062, 09, 'Pontalina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1063, 09, 'Porangatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1064, 09, 'Porteirão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1065, 09, 'Portelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1066, 09, 'Posse')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1067, 09, 'Professor Jamil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1068, 09, 'Quirinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1069, 09, 'Rialma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1070, 09, 'Rianápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1071, 09, 'Rio Quente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1072, 09, 'Rio Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1073, 09, 'Rubiataba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1074, 09, 'Sanclerlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1075, 09, 'Santa Bárbara de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1076, 09, 'Santa Cruz de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1077, 09, 'Santa Fé de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1078, 09, 'Santa Helena de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1079, 09, 'Santa Isabel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1080, 09, 'Santa Rita do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1081, 09, 'Santa Rita do Novo Destino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1082, 09, 'Santa Rosa de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1083, 09, 'Santa Tereza de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1084, 09, 'Santa Terezinha de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1085, 09, 'Santo Antônio da Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1086, 09, 'Santo Antônio de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1087, 09, 'Santo Antônio do Descoberto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1088, 09, 'São Domingos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1089, 09, 'São Francisco de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1090, 09, 'São João d`Aliança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1091, 09, 'São João da Paraúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1092, 09, 'São Luís de Montes Belos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1093, 09, 'São Luíz do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1094, 09, 'São Miguel do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1095, 09, 'São Miguel do Passa Quatro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1096, 09, 'São Patrício')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1097, 09, 'São Simão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1098, 09, 'Senador Canedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1099, 09, 'Serranópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1100, 09, 'Silvânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1101, 09, 'Simolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1102, 09, 'Sítio d`Abadia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1103, 09, 'Taquaral de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1104, 09, 'Teresina de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1105, 09, 'Terezópolis de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1106, 09, 'Três Ranchos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1107, 09, 'Trindade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1108, 09, 'Trombas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1109, 09, 'Turvânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1110, 09, 'Turvelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1111, 09, 'Uirapuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1112, 09, 'Uruaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1113, 09, 'Uruana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1114, 09, 'Urutaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1115, 09, 'Valparaíso de Goiás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1116, 09, 'Varjão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1117, 09, 'Vianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1118, 09, 'Vicentinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1119, 09, 'Vila Boa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1120, 09, 'Vila Propício')");


        /* **************************** M a r a n h ã o ***************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1121, 10, 'Açailândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1122, 10, 'Afonso Cunha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1123, 10, 'Água Doce do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1124, 10, 'Alcântara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1125, 10, 'Aldeias Altas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1126, 10, 'Altamira do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1127, 10, 'Alto Alegre do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1128, 10, 'Alto Alegre do Pindaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1129, 10, 'Alto Parnaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1130, 10, 'Amapá do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1131, 10, 'Amarante do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1132, 10, 'Anajatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1133, 10, 'Anapurus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1134, 10, 'Apicum-Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1135, 10, 'Araguanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1136, 10, 'Araioses')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1137, 10, 'Arame')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1138, 10, 'Arari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1139, 10, 'Axixá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1140, 10, 'Bacabal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1141, 10, 'Bacabeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1142, 10, 'Bacuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1143, 10, 'Bacurituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1144, 10, 'Balsas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1145, 10, 'Barão de Grajaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1146, 10, 'Barra do Corda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1147, 10, 'Barreirinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1148, 10, 'Bela Vista do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1149, 10, 'Belágua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1150, 10, 'Benedito Leite')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1151, 10, 'Bequimão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1152, 10, 'Bernardo do Mearim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1153, 10, 'Boa Vista do Gurupi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1154, 10, 'Bom Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1155, 10, 'Bom Jesus das Selvas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1156, 10, 'Bom Lugar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1157, 10, 'Brejo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1158, 10, 'Brejo de Areia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1159, 10, 'Buriti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1160, 10, 'Buriti Bravo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1161, 10, 'Buriticupu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1162, 10, 'Buritirana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1163, 10, 'Cachoeira Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1164, 10, 'Cajapió')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1165, 10, 'Cajari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1166, 10, 'Campestre do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1167, 10, 'Cândido Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1168, 10, 'Cantanhede')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1169, 10, 'Capinzal do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1170, 10, 'Carolina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1171, 10, 'Carutapera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1172, 10, 'Caxias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1173, 10, 'Cedral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1174, 10, 'Central do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1175, 10, 'Centro do Guilherme')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1176, 10, 'Centro Novo do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1177, 10, 'Chapadinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1178, 10, 'Cidelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1179, 10, 'Codó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1180, 10, 'Coelho Neto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1181, 10, 'Colinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1182, 10, 'Conceição do Lago-Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1183, 10, 'Coroatá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1184, 10, 'Cururupu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1185, 10, 'Davinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1186, 10, 'Dom Pedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1187, 10, 'Duque Bacelar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1188, 10, 'Esperantinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1189, 10, 'Estreito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1190, 10, 'Feira Nova do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1191, 10, 'Fernando Falcão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1192, 10, 'Formosa da Serra Negra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1193, 10, 'Fortaleza dos Nogueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1194, 10, 'Fortuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1195, 10, 'Godofredo Viana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1196, 10, 'Gonçalves Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1197, 10, 'Governador Archer')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1198, 10, 'Governador Edison Lobão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1199, 10, 'Governador Eugênio Barros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1200, 10, 'Governador Luiz Rocha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1201, 10, 'Governador Newton Bello')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1202, 10, 'Governador Nunes Freire')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1203, 10, 'Graça Aranha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1204, 10, 'Grajaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1205, 10, 'Guimarães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1206, 10, 'Humberto de Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1207, 10, 'Icatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1208, 10, 'Igarapé do Meio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1209, 10, 'Igarapé Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1210, 10, 'Imperatriz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1211, 10, 'Itaipava do Grajaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1212, 10, 'Itapecuru Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1213, 10, 'Itinga do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1214, 10, 'Jatobá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1215, 10, 'Jenipapo dos Vieiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1216, 10, 'João Lisboa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1217, 10, 'Joselândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1218, 10, 'Junco do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1219, 10, 'Lago da Pedra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1220, 10, 'Lago do Junco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1221, 10, 'Lago dos Rodrigues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1222, 10, 'Lago Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1223, 10, 'Lagoa do Mato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1224, 10, 'Lagoa Grande do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1225, 10, 'Lajeado Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1226, 10, 'Lima Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1227, 10, 'Loreto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1228, 10, 'Luís Domingues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1229, 10, 'Magalhães de Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1230, 10, 'Maracaçumé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1231, 10, 'Marajá do Sena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1232, 10, 'Maranhãozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1233, 10, 'Mata Roma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1234, 10, 'Matinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1235, 10, 'Matões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1236, 10, 'Matões do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1237, 10, 'Milagres do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1238, 10, 'Mirador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1239, 10, 'Miranda do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1240, 10, 'Mirinzal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1241, 10, 'Monção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1242, 10, 'Montes Altos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1243, 10, 'Morros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1244, 10, 'Nina Rodrigues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1245, 10, 'Nova Colinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1246, 10, 'Nova Iorque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1247, 10, 'Nova Olinda do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1248, 10, 'Olho d`Água das Cunhãs')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1249, 10, 'Olinda Nova do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1250, 10, 'Paço do Lumiar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1251, 10, 'Palmeirândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1252, 10, 'Paraibano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1253, 10, 'Parnarama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1254, 10, 'Passagem Franca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1255, 10, 'Pastos Bons')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1256, 10, 'Paulino Neves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1257, 10, 'Paulo Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1258, 10, 'Pedreiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1259, 10, 'Pedro do Rosário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1260, 10, 'Penalva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1261, 10, 'Peri Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1262, 10, 'Peritoró')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1263, 10, 'Pindaré-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1264, 10, 'Pinheiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1265, 10, 'Pio XII')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1266, 10, 'Pirapemas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1267, 10, 'Poção de Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1268, 10, 'Porto Franco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1269, 10, 'Porto Rico do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1270, 10, 'Presidente Dutra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1271, 10, 'Presidente Juscelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1272, 10, 'Presidente Médici')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1273, 10, 'Presidente Sarney')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1274, 10, 'Presidente Vargas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1275, 10, 'Primeira Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1276, 10, 'Raposa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1277, 10, 'Riachão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1278, 10, 'Ribamar Fiquene')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1279, 10, 'Rosário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1280, 10, 'Sambaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1281, 10, 'Santa Filomena do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1282, 10, 'Santa Helena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1283, 10, 'Santa Inês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1284, 10, 'Santa Luzia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1285, 10, 'Santa Luzia do Paruá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1286, 10, 'Santa Quitéria do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1287, 10, 'Santa Rita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1288, 10, 'Santana do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1289, 10, 'Santo Amaro do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1290, 10, 'Santo Antônio dos Lopes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1291, 10, 'São Benedito do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1292, 10, 'São Bento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1293, 10, 'São Bernardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1294, 10, 'São Domingos do Azeitão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1295, 10, 'São Domingos do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1296, 10, 'São Félix de Balsas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1297, 10, 'São Francisco do Brejão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1298, 10, 'São Francisco do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1299, 10, 'São João Batista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1300, 10, 'São João do Carú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1301, 10, 'São João do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1302, 10, 'São João do Soter')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1303, 10, 'São João dos Patos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1304, 10, 'São José de Ribamar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1305, 10, 'São José dos Basílios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1306, 10, 'São Luís')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1307, 10, 'São Luís Gonzaga do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1308, 10, 'São Mateus do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1309, 10, 'São Pedro da Água Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1310, 10, 'São Pedro dos Crentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1311, 10, 'São Raimundo das Mangabeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1312, 10, 'São Raimundo do Doca Bezerra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1313, 10, 'São Roberto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1314, 10, 'São Vicente Ferrer')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1315, 10, 'Satubinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1316, 10, 'Senador Alexandre Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1317, 10, 'Senador La Rocque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1318, 10, 'Serrano do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1319, 10, 'Sítio Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1320, 10, 'Sucupira do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1321, 10, 'Sucupira do Riachão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1322, 10, 'Tasso Fragoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1323, 10, 'Timbiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1324, 10, 'Timon')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1325, 10, 'Trizidela do Vale')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1326, 10, 'Tufilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1327, 10, 'Tuntum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1328, 10, 'Turiaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1329, 10, 'Turilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1330, 10, 'Tutóia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1331, 10, 'Urbano Santos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1332, 10, 'Vargem Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1333, 10, 'Viana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1334, 10, 'Vila Nova dos Martírios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1335, 10, 'Vitória do Mearim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1336, 10, 'Vitorino Freire')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1337, 10, 'Zé Doca')");


        /* ************************* M a t o   G r o s s o ************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1338, 11, 'Acorizal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1339, 11, 'Água Boa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1340, 11, 'Alta Floresta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1341, 11, 'Alto Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1342, 11, 'Alto Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1343, 11, 'Alto Garças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1344, 11, 'Alto Paraguai')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1345, 11, 'Alto Taquari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1346, 11, 'Apiacás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1347, 11, 'Araguaiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1348, 11, 'Araguainha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1349, 11, 'Araputanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1350, 11, 'Arenápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1351, 11, 'Aripuanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1352, 11, 'Barão de Melgaço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1353, 11, 'Barra do Bugres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1354, 11, 'Barra do Garças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1355, 11, 'Brasnorte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1356, 11, 'Cáceres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1357, 11, 'Campinápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1358, 11, 'Campo Novo do Parecis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1359, 11, 'Campo Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1360, 11, 'Campos de Júlio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1361, 11, 'Canabrava do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1362, 11, 'Canarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1363, 11, 'Carlinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1364, 11, 'Castanheira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1365, 11, 'Chapada dos Guimarães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1366, 11, 'Cláudia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1367, 11, 'Cocalinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1368, 11, 'Colíder')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1369, 11, 'Comodoro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1370, 11, 'Confresa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1371, 11, 'Cotriguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1372, 11, 'Cuiabá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1373, 11, 'Denise')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1374, 11, 'Diamantino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1375, 11, 'Dom Aquino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1376, 11, 'Feliz Natal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1377, 11, 'Figueirópolis d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1378, 11, 'Gaúcha do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1379, 11, 'General Carneiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1380, 11, 'Glória d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1381, 11, 'Guarantã do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1382, 11, 'Guiratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1383, 11, 'Indiavaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1384, 11, 'Itaúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1385, 11, 'Itiquira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1386, 11, 'Jaciara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1387, 11, 'Jangada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1388, 11, 'Jauru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1389, 11, 'Juara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1390, 11, 'Juína')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1391, 11, 'Juruena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1392, 11, 'Juscimeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1393, 11, 'Lambari d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1394, 11, 'Lucas do Rio Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1395, 11, 'Luciára')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1396, 11, 'Marcelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1397, 11, 'Matupá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1398, 11, 'Mirassol d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1399, 11, 'Nobres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1400, 11, 'Nortelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1401, 11, 'Nossa Senhora do Livramento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1402, 11, 'Nova Bandeirantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1403, 11, 'Nova Brasilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1404, 11, 'Nova Canaã do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1405, 11, 'Nova Guarita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1406, 11, 'Nova Lacerda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1407, 11, 'Nova Marilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1408, 11, 'Nova Maringá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1409, 11, 'Nova Monte Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1410, 11, 'Nova Mutum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1411, 11, 'Nova Olímpia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1412, 11, 'Nova Ubiratã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1413, 11, 'Nova Xavantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1414, 11, 'Novo Horizonte do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1415, 11, 'Novo Mundo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1416, 11, 'Novo São Joaquim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1417, 11, 'Paranaíta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1418, 11, 'Paranatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1419, 11, 'Pedra Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1420, 11, 'Peixoto de Azevedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1421, 11, 'Planalto da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1422, 11, 'Poconé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1423, 11, 'Pontal do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1424, 11, 'Ponte Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1425, 11, 'Pontes e Lacerda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1426, 11, 'Porto Alegre do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1427, 11, 'Porto dos Gaúchos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1428, 11, 'Porto Esperidião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1429, 11, 'Porto Estrela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1430, 11, 'Poxoréo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1431, 11, 'Primavera do Leste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1432, 11, 'Querência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1433, 11, 'Reserva do Cabaçal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1434, 11, 'Ribeirão Cascalheira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1435, 11, 'Ribeirãozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1436, 11, 'Rio Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1437, 11, 'Rondonópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1438, 11, 'Rosário Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1439, 11, 'Salto do Céu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1440, 11, 'Santa Carmem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1441, 11, 'Santa Terezinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1442, 11, 'Santo Afonso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1443, 11, 'Santo Antônio do Leverger')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1444, 11, 'São Félix do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1445, 11, 'São José do Povo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1446, 11, 'São José do Rio Claro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1447, 11, 'São José do Xingu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1448, 11, 'São José dos Quatro Marcos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1449, 11, 'São Pedro da Cipa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1450, 11, 'Sapezal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1451, 11, 'Sinop')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1452, 11, 'Sorriso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1453, 11, 'Tabaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1454, 11, 'Tangará da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1455, 11, 'Tapurah')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1456, 11, 'Terra Nova do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1457, 11, 'Tesouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1458, 11, 'Torixoréu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1459, 11, 'União do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1460, 11, 'Várzea Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1461, 11, 'Vera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1462, 11, 'Vila Bela da Santíssima Trindade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1463, 11, 'Vila Rica')");



        /* ****************** M a t o   G r o s s o   do   S u l ******************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1464, 12, 'Água Clara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1465, 12, 'Alcinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1466, 12, 'Amambaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1467, 12, 'Anastácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1468, 12, 'Anaurilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1469, 12, 'Angélica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1470, 12, 'Antônio João')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1471, 12, 'Aparecida do Taboado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1472, 12, 'Aquidauana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1473, 12, 'Aral Moreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1474, 12, 'Bandeirantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1475, 12, 'Bataguassu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1476, 12, 'Bataiporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1477, 12, 'Bela Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1478, 12, 'Bodoquena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1479, 12, 'Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1480, 12, 'Brasilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1481, 12, 'Caarapó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1482, 12, 'Camapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1483, 12, 'Campo Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1484, 12, 'Caracol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1485, 12, 'Cassilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1486, 12, 'Chapadão do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1487, 12, 'Corguinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1488, 12, 'Coronel Sapucaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1489, 12, 'Corumbá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1490, 12, 'Costa Rica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1491, 12, 'Coxim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1492, 12, 'Deodápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1493, 12, 'Dois Irmãos do Buriti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1494, 12, 'Douradina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1495, 12, 'Dourados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1496, 12, 'Eldorado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1497, 12, 'Fátima do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1498, 12, 'Glória de Dourados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1499, 12, 'Guia Lopes da Laguna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1500, 12, 'Iguatemi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1501, 12, 'Inocência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1502, 12, 'Itaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1503, 12, 'Itaquiraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1504, 12, 'Ivinhema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1505, 12, 'Japorã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1506, 12, 'Jaraguari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1507, 12, 'Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1508, 12, 'Jateí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1509, 12, 'Juti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1510, 12, 'Ladário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1511, 12, 'Laguna Carapã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1512, 12, 'Maracaju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1513, 12, 'Miranda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1514, 12, 'Mundo Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1515, 12, 'Naviraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1516, 12, 'Nioaque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1517, 12, 'Nova Alvorada do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1518, 12, 'Nova Andradina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1519, 12, 'Novo Horizonte do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1520, 12, 'Paranaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1521, 12, 'Paranhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1522, 12, 'Pedro Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1523, 12, 'Ponta Porã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1524, 12, 'Porto Murtinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1525, 12, 'Ribas do Rio Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1526, 12, 'Rio Brilhante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1527, 12, 'Rio Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1528, 12, 'Rio Verde de Mato Grosso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1529, 12, 'Rochedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1530, 12, 'Santa Rita do Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1531, 12, 'São Gabriel do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1532, 12, 'Selvíria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1533, 12, 'Sete Quedas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1534, 12, 'Sidrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1535, 12, 'Sonora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1536, 12, 'Tacuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1537, 12, 'Taquarussu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1538, 12, 'Terenos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1539, 12, 'Três Lagoas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1540, 12, 'Vicentina')");


        /* ************************ M i n a s   G e r a i s ************************* */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1541, 13, 'Abadia dos Dourados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1542, 13, 'Abaeté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1543, 13, 'Abre Campo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1544, 13, 'Acaiaca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1545, 13, 'Açucena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1546, 13, 'Água Boa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1547, 13, 'Água Comprida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1548, 13, 'Aguanil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1549, 13, 'Águas Formosas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1550, 13, 'Águas Vermelhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1551, 13, 'Aimorés')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1552, 13, 'Aiuruoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1553, 13, 'Alagoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1554, 13, 'Albertina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1555, 13, 'Além Paraíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1556, 13, 'Alfenas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1557, 13, 'Alfredo Vasconcelos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1558, 13, 'Almenara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1559, 13, 'Alpercata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1560, 13, 'Alpinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1561, 13, 'Alterosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1562, 13, 'Alto Caparaó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1563, 13, 'Alto Jequitibá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1564, 13, 'Alto Rio Doce')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1565, 13, 'Alvarenga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1566, 13, 'Alvinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1567, 13, 'Alvorada de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1568, 13, 'Amparo do Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1569, 13, 'Andradas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1570, 13, 'Andrelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1571, 13, 'Angelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1572, 13, 'Antônio Carlos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1573, 13, 'Antônio Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1574, 13, 'Antônio Prado de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1575, 13, 'Araçaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1576, 13, 'Aracitaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1577, 13, 'Araçuaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1578, 13, 'Araguari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1579, 13, 'Arantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1580, 13, 'Araponga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1581, 13, 'Araporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1582, 13, 'Arapuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1583, 13, 'Araújos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1584, 13, 'Araxá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1585, 13, 'Arceburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1586, 13, 'Arcos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1587, 13, 'Areado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1588, 13, 'Argirita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1589, 13, 'Aricanduva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1590, 13, 'Arinos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1591, 13, 'Astolfo Dutra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1592, 13, 'Ataléia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1593, 13, 'Augusto de Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1594, 13, 'Baependi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1595, 13, 'Baldim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1596, 13, 'Bambuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1597, 13, 'Bandeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1598, 13, 'Bandeira do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1599, 13, 'Barão de Cocais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1600, 13, 'Barão de Monte Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1601, 13, 'Barbacena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1602, 13, 'Barra Longa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1603, 13, 'Barroso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1604, 13, 'Bela Vista de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1605, 13, 'Belmiro Braga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1606, 13, 'Belo Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1607, 13, 'Belo Oriente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1608, 13, 'Belo Vale')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1609, 13, 'Berilo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1610, 13, 'Berizal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1611, 13, 'Bertópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1612, 13, 'Betim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1613, 13, 'Bias Fortes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1614, 13, 'Bicas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1615, 13, 'Biquinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1616, 13, 'Boa Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1617, 13, 'Bocaina de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1618, 13, 'Bocaiúva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1619, 13, 'Bom Despacho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1620, 13, 'Bom Jardim de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1621, 13, 'Bom Jesus da Penha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1622, 13, 'Bom Jesus do Amparo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1623, 13, 'Bom Jesus do Galho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1624, 13, 'Bom Repouso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1625, 13, 'Bom Sucesso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1626, 13, 'Bonfim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1627, 13, 'Bonfinópolis de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1628, 13, 'Bonito de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1629, 13, 'Borda da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1630, 13, 'Botelhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1631, 13, 'Botumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1632, 13, 'Brás Pires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1633, 13, 'Brasilândia de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1634, 13, 'Brasília de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1635, 13, 'Brasópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1636, 13, 'Braúnas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1637, 13, 'Brumadinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1638, 13, 'Bueno Brandão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1639, 13, 'Buenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1640, 13, 'Bugre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1641, 13, 'Buritis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1642, 13, 'Buritizeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1643, 13, 'Cabeceira Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1644, 13, 'Cabo Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1645, 13, 'Cachoeira da Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1646, 13, 'Cachoeira de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1647, 13, 'Cachoeira de Pajeú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1648, 13, 'Cachoeira Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1649, 13, 'Caetanópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1650, 13, 'Caeté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1651, 13, 'Caiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1652, 13, 'Cajuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1653, 13, 'Caldas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1654, 13, 'Camacho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1655, 13, 'Camanducaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1656, 13, 'Cambuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1657, 13, 'Cambuquira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1658, 13, 'Campanário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1659, 13, 'Campanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1660, 13, 'Campestre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1661, 13, 'Campina Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1662, 13, 'Campo Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1663, 13, 'Campo Belo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1664, 13, 'Campo do Meio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1665, 13, 'Campo Florido')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1666, 13, 'Campos Altos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1667, 13, 'Campos Gerais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1668, 13, 'Cana Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1669, 13, 'Canaã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1670, 13, 'Canápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1671, 13, 'Candeias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1672, 13, 'Cantagalo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1673, 13, 'Caparaó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1674, 13, 'Capela Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1675, 13, 'Capelinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1676, 13, 'Capetinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1677, 13, 'Capim Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1678, 13, 'Capinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1679, 13, 'Capitão Andrade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1680, 13, 'Capitão Enéas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1681, 13, 'Capitólio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1682, 13, 'Caputira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1683, 13, 'Caraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1684, 13, 'Caranaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1685, 13, 'Carandaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1686, 13, 'Carangola')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1687, 13, 'Caratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1688, 13, 'Carbonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1689, 13, 'Careaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1690, 13, 'Carlos Chagas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1691, 13, 'Carmésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1692, 13, 'Carmo da Cachoeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1693, 13, 'Carmo da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1694, 13, 'Carmo de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1695, 13, 'Carmo do Cajuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1696, 13, 'Carmo do Paranaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1697, 13, 'Carmo do Rio Claro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1698, 13, 'Carmópolis de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1699, 13, 'Carneirinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1700, 13, 'Carrancas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1701, 13, 'Carvalhópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1702, 13, 'Carvalhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1703, 13, 'Casa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1704, 13, 'Cascalho Rico')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1705, 13, 'Cássia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1706, 13, 'Cataguases')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1707, 13, 'Catas Altas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1708, 13, 'Catas Altas da Noruega')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1709, 13, 'Catuji')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1710, 13, 'Catuti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1711, 13, 'Caxambu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1712, 13, 'Cedro do Abaeté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1713, 13, 'Central de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1714, 13, 'Centralina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1715, 13, 'Chácara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1716, 13, 'Chalé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1717, 13, 'Chapada do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1718, 13, 'Chapada Gaúcha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1719, 13, 'Chiador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1720, 13, 'Cipotânea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1721, 13, 'Claraval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1722, 13, 'Claro dos Poções')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1723, 13, 'Cláudio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1724, 13, 'Coimbra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1725, 13, 'Coluna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1726, 13, 'Comendador Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1727, 13, 'Comercinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1728, 13, 'Conceição da Aparecida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1729, 13, 'Conceição da Barra de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1730, 13, 'Conceição das Alagoas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1731, 13, 'Conceição das Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1732, 13, 'Conceição de Ipanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1733, 13, 'Conceição do Mato Dentro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1734, 13, 'Conceição do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1735, 13, 'Conceição do Rio Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1736, 13, 'Conceição dos Ouros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1737, 13, 'Cônego Marinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1738, 13, 'Confins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1739, 13, 'Congonhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1740, 13, 'Congonhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1741, 13, 'Congonhas do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1742, 13, 'Conquista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1743, 13, 'Conselheiro Lafaiete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1744, 13, 'Conselheiro Pena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1745, 13, 'Consolação')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1746, 13, 'Contagem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1747, 13, 'Coqueiral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1748, 13, 'Coração de Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1749, 13, 'Cordisburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1750, 13, 'Cordislândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1751, 13, 'Corinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1752, 13, 'Coroaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1753, 13, 'Coromandel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1754, 13, 'Coronel Fabriciano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1755, 13, 'Coronel Murta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1756, 13, 'Coronel Pacheco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1757, 13, 'Coronel Xavier Chaves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1758, 13, 'Córrego Danta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1759, 13, 'Córrego do Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1760, 13, 'Córrego Fundo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1761, 13, 'Córrego Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1762, 13, 'Couto de Magalhães de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1763, 13, 'Crisólita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1764, 13, 'Cristais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1765, 13, 'Cristália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1766, 13, 'Cristiano Otoni')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1767, 13, 'Cristina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1768, 13, 'Crucilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1769, 13, 'Cruzeiro da Fortaleza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1770, 13, 'Cruzília')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1771, 13, 'Cuparaque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1772, 13, 'Curral de Dentro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1773, 13, 'Curvelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1774, 13, 'Datas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1775, 13, 'Delfim Moreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1776, 13, 'Delfinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1777, 13, 'Delta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1778, 13, 'Descoberto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1779, 13, 'Desterro de Entre Rios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1780, 13, 'Desterro do Melo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1781, 13, 'Diamantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1782, 13, 'Diogo de Vasconcelos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1783, 13, 'Dionísio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1784, 13, 'Divinésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1785, 13, 'Divino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1786, 13, 'Divino das Laranjeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1787, 13, 'Divinolândia de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1788, 13, 'Divinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1789, 13, 'Divisa Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1790, 13, 'Divisa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1791, 13, 'Divisópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1792, 13, 'Dom Bosco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1793, 13, 'Dom Cavati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1794, 13, 'Dom Joaquim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1795, 13, 'Dom Silvério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1796, 13, 'Dom Viçoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1797, 13, 'Dona Eusébia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1798, 13, 'Dores de Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1799, 13, 'Dores de Guanhães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1800, 13, 'Dores do Indaiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1801, 13, 'Dores do Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1802, 13, 'Doresópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1803, 13, 'Douradoquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1804, 13, 'Durandé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1805, 13, 'Elói Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1806, 13, 'Engenheiro Caldas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1807, 13, 'Engenheiro Navarro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1808, 13, 'Entre Folhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1809, 13, 'Entre Rios de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1810, 13, 'Ervália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1811, 13, 'Esmeraldas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1812, 13, 'Espera Feliz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1813, 13, 'Espinosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1814, 13, 'Espírito Santo do Dourado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1815, 13, 'Estiva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1816, 13, 'Estrela Dalva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1817, 13, 'Estrela do Indaiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1818, 13, 'Estrela do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1819, 13, 'Eugenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1820, 13, 'Ewbank da Câmara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1821, 13, 'Extrema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1822, 13, 'Fama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1823, 13, 'Faria Lemos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1824, 13, 'Felício dos Santos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1825, 13, 'Felisburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1826, 13, 'Felixlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1827, 13, 'Fernandes Tourinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1828, 13, 'Ferros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1829, 13, 'Fervedouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1830, 13, 'Florestal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1831, 13, 'Formiga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1832, 13, 'Formoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1833, 13, 'Fortaleza de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1834, 13, 'Fortuna de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1835, 13, 'Francisco Badaró')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1836, 13, 'Francisco Dumont')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1837, 13, 'Francisco Sá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1838, 13, 'Franciscópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1839, 13, 'Frei Gaspar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1840, 13, 'Frei Inocêncio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1841, 13, 'Frei Lagonegro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1842, 13, 'Fronteira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1843, 13, 'Fronteira dos Vales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1844, 13, 'Fruta de Leite')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1845, 13, 'Frutal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1846, 13, 'Funilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1847, 13, 'Galiléia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1848, 13, 'Gameleiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1849, 13, 'Glaucilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1850, 13, 'Goiabeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1851, 13, 'Goianá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1852, 13, 'Gonçalves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1853, 13, 'Gonzaga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1854, 13, 'Gouveia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1855, 13, 'Governador Valadares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1856, 13, 'Grão Mogol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1857, 13, 'Grupiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1858, 13, 'Guanhães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1859, 13, 'Guapé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1860, 13, 'Guaraciaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1861, 13, 'Guaraciama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1862, 13, 'Guaranésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1863, 13, 'Guarani')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1864, 13, 'Guarará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1865, 13, 'Guarda-Mor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1866, 13, 'Guaxupé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1867, 13, 'Guidoval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1868, 13, 'Guimarânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1869, 13, 'Guiricema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1870, 13, 'Gurinhatã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1871, 13, 'Heliodora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1872, 13, 'Iapu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1873, 13, 'Ibertioga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1874, 13, 'Ibiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1875, 13, 'Ibiaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1876, 13, 'Ibiracatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1877, 13, 'Ibiraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1878, 13, 'Ibirité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1879, 13, 'Ibitiúra de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1880, 13, 'Ibituruna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1881, 13, 'Icaraí de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1882, 13, 'Igarapé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1883, 13, 'Igaratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1884, 13, 'Iguatama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1885, 13, 'Ijaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1886, 13, 'Ilicínea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1887, 13, 'Imbé de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1888, 13, 'Inconfidentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1889, 13, 'Indaiabira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1890, 13, 'Indianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1891, 13, 'Ingaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1892, 13, 'Inhapim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1893, 13, 'Inhaúma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1894, 13, 'Inimutaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1895, 13, 'Ipaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1896, 13, 'Ipanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1897, 13, 'Ipatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1898, 13, 'Ipiaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1899, 13, 'Ipuiúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1900, 13, 'Iraí de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1901, 13, 'Itabira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1902, 13, 'Itabirinha de Mantena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1903, 13, 'Itabirito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1904, 13, 'Itacambira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1905, 13, 'Itacarambi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1906, 13, 'Itaguara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1907, 13, 'Itaipé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1908, 13, 'Itajubá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1909, 13, 'Itamarandiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1910, 13, 'Itamarati de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1911, 13, 'Itambacuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1912, 13, 'Itambé do Mato Dentro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1913, 13, 'Itamogi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1914, 13, 'Itamonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1915, 13, 'Itanhandu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1916, 13, 'Itanhomi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1917, 13, 'Itaobim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1918, 13, 'Itapagipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1919, 13, 'Itapecerica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1920, 13, 'Itapeva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1921, 13, 'Itatiaiuçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1922, 13, 'Itaú de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1923, 13, 'Itaúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1924, 13, 'Itaverava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1925, 13, 'Itinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1926, 13, 'Itueta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1927, 13, 'Ituiutaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1928, 13, 'Itumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1929, 13, 'Iturama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1930, 13, 'Itutinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1931, 13, 'Jaboticatubas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1932, 13, 'Jacinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1933, 13, 'Jacuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1934, 13, 'Jacutinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1935, 13, 'Jaguaraçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1936, 13, 'Jaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1937, 13, 'Jampruca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1938, 13, 'Janaúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1939, 13, 'Januária')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1940, 13, 'Japaraíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1941, 13, 'Japonvar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1942, 13, 'Jeceaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1943, 13, 'Jenipapo de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1944, 13, 'Jequeri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1945, 13, 'Jequitaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1946, 13, 'Jequitibá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1947, 13, 'Jequitinhonha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1948, 13, 'Jesuânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1949, 13, 'Joaíma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1950, 13, 'Joanésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1951, 13, 'João Monlevade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1952, 13, 'João Pinheiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1953, 13, 'Joaquim Felício')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1954, 13, 'Jordânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1955, 13, 'José Gonçalves de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1956, 13, 'José Raydan')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1957, 13, 'Josenópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1958, 13, 'Juatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1959, 13, 'Juiz de Fora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1960, 13, 'Juramento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1961, 13, 'Juruaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1962, 13, 'Juvenília')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1963, 13, 'Ladainha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1964, 13, 'Lagamar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1965, 13, 'Lagoa da Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1966, 13, 'Lagoa dos Patos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1967, 13, 'Lagoa Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1968, 13, 'Lagoa Formosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1969, 13, 'Lagoa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1970, 13, 'Lagoa Santa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1971, 13, 'Lajinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1972, 13, 'Lambari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1973, 13, 'Lamim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1974, 13, 'Laranjal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1975, 13, 'Lassance')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1976, 13, 'Lavras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1977, 13, 'Leandro Ferreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1978, 13, 'Leme do Prado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1979, 13, 'Leopoldina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1980, 13, 'Liberdade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1981, 13, 'Lima Duarte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1982, 13, 'Limeira do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1983, 13, 'Lontra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1984, 13, 'Luisburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1985, 13, 'Luislândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1986, 13, 'Luminárias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1987, 13, 'Luz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1988, 13, 'Machacalis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1989, 13, 'Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1990, 13, 'Madre de Deus de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1991, 13, 'Malacacheta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1992, 13, 'Mamonas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1993, 13, 'Manga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1994, 13, 'Manhuaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1995, 13, 'Manhumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1996, 13, 'Mantena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1997, 13, 'Mar de Espanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1998, 13, 'Maravilhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (1999, 13, 'Maria da Fé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2000, 13, 'Mariana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2001, 13, 'Marilac')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2002, 13, 'Mário Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2003, 13, 'Maripá de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2004, 13, 'Marliéria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2005, 13, 'Marmelópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2006, 13, 'Martinho Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2007, 13, 'Martins Soares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2008, 13, 'Mata Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2009, 13, 'Materlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2010, 13, 'Mateus Leme')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2011, 13, 'Mathias Lobato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2012, 13, 'Matias Barbosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2013, 13, 'Matias Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2014, 13, 'Matipó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2015, 13, 'Mato Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2016, 13, 'Matozinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2017, 13, 'Matutina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2018, 13, 'Medeiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2019, 13, 'Medina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2020, 13, 'Mendes Pimentel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2021, 13, 'Mercês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2022, 13, 'Mesquita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2023, 13, 'Minas Novas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2024, 13, 'Minduri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2025, 13, 'Mirabela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2026, 13, 'Miradouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2027, 13, 'Miraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2028, 13, 'Miravânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2029, 13, 'Moeda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2030, 13, 'Moema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2031, 13, 'Monjolos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2032, 13, 'Monsenhor Paulo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2033, 13, 'Montalvânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2034, 13, 'Monte Alegre de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2035, 13, 'Monte Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2036, 13, 'Monte Belo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2037, 13, 'Monte Carmelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2038, 13, 'Monte Formoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2039, 13, 'Monte Santo de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2040, 13, 'Monte Sião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2041, 13, 'Montes Claros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2042, 13, 'Montezuma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2043, 13, 'Morada Nova de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2044, 13, 'Morro da Garça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2045, 13, 'Morro do Pilar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2046, 13, 'Munhoz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2047, 13, 'Muriaé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2048, 13, 'Mutum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2049, 13, 'Muzambinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2050, 13, 'Nacip Raydan')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2051, 13, 'Nanuque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2052, 13, 'Naque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2053, 13, 'Natalândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2054, 13, 'Natércia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2055, 13, 'Nazareno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2056, 13, 'Nepomuceno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2057, 13, 'Ninheira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2058, 13, 'Nova Belém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2059, 13, 'Nova Era')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2060, 13, 'Nova Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2061, 13, 'Nova Módica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2062, 13, 'Nova Ponte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2063, 13, 'Nova Porteirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2064, 13, 'Nova Resende')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2065, 13, 'Nova Serrana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2066, 13, 'Nova União')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2067, 13, 'Novo Cruzeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2068, 13, 'Novo Oriente de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2069, 13, 'Novorizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2070, 13, 'Olaria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2071, 13, 'Olhos-d`Água')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2072, 13, 'Olímpio Noronha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2073, 13, 'Oliveira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2074, 13, 'Oliveira Fortes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2075, 13, 'Onça de Pitangui')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2076, 13, 'Oratórios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2077, 13, 'Orizânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2078, 13, 'Ouro Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2079, 13, 'Ouro Fino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2080, 13, 'Ouro Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2081, 13, 'Ouro Verde de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2082, 13, 'Padre Carvalho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2083, 13, 'Padre Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2084, 13, 'Pai Pedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2085, 13, 'Paineiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2086, 13, 'Pains')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2087, 13, 'Paiva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2088, 13, 'Palma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2089, 13, 'Palmópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2090, 13, 'Papagaios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2091, 13, 'Pará de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2092, 13, 'Paracatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2093, 13, 'Paraguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2094, 13, 'Paraisópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2095, 13, 'Paraopeba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2096, 13, 'Passa Quatro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2097, 13, 'Passa Tempo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2098, 13, 'Passa-Vinte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2099, 13, 'Passabém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2100, 13, 'Passos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2101, 13, 'Patis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2102, 13, 'Patos de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2103, 13, 'Patrocínio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2104, 13, 'Patrocínio do Muriaé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2105, 13, 'Paula Cândido')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2106, 13, 'Paulistas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2107, 13, 'Pavão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2108, 13, 'Peçanha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2109, 13, 'Pedra Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2110, 13, 'Pedra Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2111, 13, 'Pedra do Anta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2112, 13, 'Pedra do Indaiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2113, 13, 'Pedra Dourada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2114, 13, 'Pedralva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2115, 13, 'Pedras de Maria da Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2116, 13, 'Pedrinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2117, 13, 'Pedro Leopoldo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2118, 13, 'Pedro Teixeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2119, 13, 'Pequeri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2120, 13, 'Pequi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2121, 13, 'Perdigão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2122, 13, 'Perdizes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2123, 13, 'Perdões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2124, 13, 'Periquito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2125, 13, 'Pescador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2126, 13, 'Piau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2127, 13, 'Piedade de Caratinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2128, 13, 'Piedade de Ponte Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2129, 13, 'Piedade do Rio Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2130, 13, 'Piedade dos Gerais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2131, 13, 'Pimenta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2132, 13, 'Pingo-d`Água')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2133, 13, 'Pintópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2134, 13, 'Piracema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2135, 13, 'Pirajuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2136, 13, 'Piranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2137, 13, 'Piranguçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2138, 13, 'Piranguinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2139, 13, 'Pirapetinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2140, 13, 'Pirapora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2141, 13, 'Piraúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2142, 13, 'Pitangui')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2143, 13, 'Piumhi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2144, 13, 'Planura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2145, 13, 'Poço Fundo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2146, 13, 'Poços de Caldas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2147, 13, 'Pocrane')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2148, 13, 'Pompéu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2149, 13, 'Ponte Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2150, 13, 'Ponto Chique')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2151, 13, 'Ponto dos Volantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2152, 13, 'Porteirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2153, 13, 'Porto Firme')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2154, 13, 'Poté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2155, 13, 'Pouso Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2156, 13, 'Pouso Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2157, 13, 'Prados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2158, 13, 'Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2159, 13, 'Pratápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2160, 13, 'Pratinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2161, 13, 'Presidente Bernardes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2162, 13, 'Presidente Juscelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2163, 13, 'Presidente Kubitschek')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2164, 13, 'Presidente Olegário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2165, 13, 'Prudente de Morais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2166, 13, 'Quartel Geral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2167, 13, 'Queluzito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2168, 13, 'Raposos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2169, 13, 'Raul Soares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2170, 13, 'Recreio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2171, 13, 'Reduto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2172, 13, 'Resende Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2173, 13, 'Resplendor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2174, 13, 'Ressaquinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2175, 13, 'Riachinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2176, 13, 'Riacho dos Machados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2177, 13, 'Ribeirão das Neves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2178, 13, 'Ribeirão Vermelho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2179, 13, 'Rio Acima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2180, 13, 'Rio Casca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2181, 13, 'Rio do Prado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2182, 13, 'Rio Doce')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2183, 13, 'Rio Espera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2184, 13, 'Rio Manso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2185, 13, 'Rio Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2186, 13, 'Rio Paranaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2187, 13, 'Rio Pardo de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2188, 13, 'Rio Piracicaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2189, 13, 'Rio Pomba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2190, 13, 'Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2191, 13, 'Rio Vermelho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2192, 13, 'Ritápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2193, 13, 'Rochedo de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2194, 13, 'Rodeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2195, 13, 'Romaria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2196, 13, 'Rosário da Limeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2197, 13, 'Rubelita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2198, 13, 'Rubim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2199, 13, 'Sabará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2200, 13, 'Sabinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2201, 13, 'Sacramento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2202, 13, 'Salinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2203, 13, 'Salto da Divisa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2204, 13, 'Santa Bárbara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2205, 13, 'Santa Bárbara do Leste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2206, 13, 'Santa Bárbara do Monte Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2207, 13, 'Santa Bárbara do Tugúrio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2208, 13, 'Santa Cruz de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2209, 13, 'Santa Cruz de Salinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2210, 13, 'Santa Cruz do Escalvado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2211, 13, 'Santa Efigênia de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2212, 13, 'Santa Fé de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2213, 13, 'Santa Helena de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2214, 13, 'Santa Juliana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2215, 13, 'Santa Luzia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2216, 13, 'Santa Margarida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2217, 13, 'Santa Maria de Itabira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2218, 13, 'Santa Maria do Salto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2219, 13, 'Santa Maria do Suaçuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2220, 13, 'Santa Rita de Caldas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2221, 13, 'Santa Rita de Ibitipoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2222, 13, 'Santa Rita de Jacutinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2223, 13, 'Santa Rita de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2224, 13, 'Santa Rita do Itueto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2225, 13, 'Santa Rita do Sapucaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2226, 13, 'Santa Rosa da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2227, 13, 'Santa Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2228, 13, 'Santana da Vargem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2229, 13, 'Santana de Cataguases')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2230, 13, 'Santana de Pirapama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2231, 13, 'Santana do Deserto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2232, 13, 'Santana do Garambéu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2233, 13, 'Santana do Jacaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2234, 13, 'Santana do Manhuaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2235, 13, 'Santana do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2236, 13, 'Santana do Riacho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2237, 13, 'Santana dos Montes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2238, 13, 'Santo Antônio do Amparo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2239, 13, 'Santo Antônio do Aventureiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2240, 13, 'Santo Antônio do Grama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2241, 13, 'Santo Antônio do Itambé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2242, 13, 'Santo Antônio do Jacinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2243, 13, 'Santo Antônio do Monte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2244, 13, 'Santo Antônio do Retiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2245, 13, 'Santo Antônio do Rio Abaixo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2246, 13, 'Santo Hipólito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2247, 13, 'Santos Dumont')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2248, 13, 'São Bento Abade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2249, 13, 'São Brás do Suaçuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2250, 13, 'São Domingos das Dores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2251, 13, 'São Domingos do Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2252, 13, 'São Félix de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2253, 13, 'São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2254, 13, 'São Francisco de Paula')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2255, 13, 'São Francisco de Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2256, 13, 'São Francisco do Glória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2257, 13, 'São Geraldo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2258, 13, 'São Geraldo da Piedade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2259, 13, 'São Geraldo do Baixio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2260, 13, 'São Gonçalo do Abaeté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2261, 13, 'São Gonçalo do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2262, 13, 'São Gonçalo do Rio Abaixo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2263, 13, 'São Gonçalo do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2264, 13, 'São Gonçalo do Sapucaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2265, 13, 'São Gotardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2266, 13, 'São João Batista do Glória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2267, 13, 'São João da Lagoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2268, 13, 'São João da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2269, 13, 'São João da Ponte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2270, 13, 'São João das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2271, 13, 'São João del Rei')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2272, 13, 'São João do Manhuaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2273, 13, 'São João do Manteninha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2274, 13, 'São João do Oriente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2275, 13, 'São João do Pacuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2276, 13, 'São João do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2277, 13, 'São João Evangelista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2278, 13, 'São João Nepomuceno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2279, 13, 'São Joaquim de Bicas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2280, 13, 'São José da Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2281, 13, 'São José da Lapa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2282, 13, 'São José da Safira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2283, 13, 'São José da Varginha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2284, 13, 'São José do Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2285, 13, 'São José do Divino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2286, 13, 'São José do Goiabal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2287, 13, 'São José do Jacuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2288, 13, 'São José do Mantimento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2289, 13, 'São Lourenço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2290, 13, 'São Miguel do Anta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2291, 13, 'São Pedro da União')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2292, 13, 'São Pedro do Suaçuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2293, 13, 'São Pedro dos Ferros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2294, 13, 'São Romão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2295, 13, 'São Roque de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2296, 13, 'São Sebastião da Bela Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2297, 13, 'São Sebastião da Vargem Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2298, 13, 'São Sebastião do Anta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2299, 13, 'São Sebastião do Maranhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2300, 13, 'São Sebastião do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2301, 13, 'São Sebastião do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2302, 13, 'São Sebastião do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2303, 13, 'São Sebastião do Rio Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2304, 13, 'São Thomé das Letras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2305, 13, 'São Tiago')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2306, 13, 'São Tomás de Aquino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2307, 13, 'São Vicente de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2308, 13, 'Sapucaí-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2309, 13, 'Sardoá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2310, 13, 'Sarzedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2311, 13, 'Sem-Peixe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2312, 13, 'Senador Amaral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2313, 13, 'Senador Cortes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2314, 13, 'Senador Firmino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2315, 13, 'Senador José Bento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2316, 13, 'Senador Modestino Gonçalves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2317, 13, 'Senhora de Oliveira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2318, 13, 'Senhora do Porto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2319, 13, 'Senhora dos Remédios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2320, 13, 'Sericita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2321, 13, 'Seritinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2322, 13, 'Serra Azul de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2323, 13, 'Serra da Saudade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2324, 13, 'Serra do Salitre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2325, 13, 'Serra dos Aimorés')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2326, 13, 'Serrania')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2327, 13, 'Serranópolis de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2328, 13, 'Serranos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2329, 13, 'Serro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2330, 13, 'Sete Lagoas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2331, 13, 'Setubinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2332, 13, 'Silveirânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2333, 13, 'Silvianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2334, 13, 'Simão Pereira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2335, 13, 'Simonésia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2336, 13, 'Sobrália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2337, 13, 'Soledade de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2338, 13, 'Tabuleiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2339, 13, 'Taiobeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2340, 13, 'Taparuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2341, 13, 'Tapira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2342, 13, 'Tapiraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2343, 13, 'Taquaraçu de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2344, 13, 'Tarumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2345, 13, 'Teixeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2346, 13, 'Teófilo Otoni')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2347, 13, 'Timóteo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2348, 13, 'Tiradentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2349, 13, 'Tiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2350, 13, 'Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2351, 13, 'Tocos do Moji')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2352, 13, 'Toledo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2353, 13, 'Tombos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2354, 13, 'Três Corações')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2355, 13, 'Três Marias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2356, 13, 'Três Pontas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2357, 13, 'Tumiritinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2358, 13, 'Tupaciguara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2359, 13, 'Turmalina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2360, 13, 'Turvolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2361, 13, 'Ubá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2362, 13, 'Ubaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2363, 13, 'Ubaporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2364, 13, 'Uberaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2365, 13, 'Uberlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2366, 13, 'Umburatiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2367, 13, 'Unaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2368, 13, 'União de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2369, 13, 'Uruana de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2370, 13, 'Urucânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2371, 13, 'Urucuia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2372, 13, 'Vargem Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2373, 13, 'Vargem Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2374, 13, 'Vargem Grande do Rio Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2375, 13, 'Varginha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2376, 13, 'Varjão de Minas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2377, 13, 'Várzea da Palma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2378, 13, 'Varzelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2379, 13, 'Vazante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2380, 13, 'Verdelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2381, 13, 'Veredinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2382, 13, 'Veríssimo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2383, 13, 'Vermelho Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2384, 13, 'Vespasiano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2385, 13, 'Viçosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2386, 13, 'Vieiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2387, 13, 'Virgem da Lapa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2388, 13, 'Virgínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2389, 13, 'Virginópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2390, 13, 'Virgolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2391, 13, 'Visconde do Rio Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2392, 13, 'Volta Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2393, 13, 'Wenceslau Braz')");


        /* ******************************** P a r á ********************************* */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2394, 14, 'Abaetetuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2395, 14, 'Abel Figueiredo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2396, 14, 'Acará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2397, 14, 'Afuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2398, 14, 'Água Azul do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2399, 14, 'Alenquer')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2400, 14, 'Almeirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2401, 14, 'Altamira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2402, 14, 'Anajás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2403, 14, 'Ananindeua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2404, 14, 'Anapu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2405, 14, 'Augusto Corrêa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2406, 14, 'Aurora do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2407, 14, 'Aveiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2408, 14, 'Bagre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2409, 14, 'Baião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2410, 14, 'Bannach')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2411, 14, 'Barcarena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2412, 14, 'Belém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2413, 14, 'Belterra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2414, 14, 'Benevides')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2415, 14, 'Bom Jesus do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2416, 14, 'Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2417, 14, 'Bragança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2418, 14, 'Brasil Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2419, 14, 'Brejo Grande do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2420, 14, 'Breu Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2421, 14, 'Breves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2422, 14, 'Bujaru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2423, 14, 'Cachoeira do Arari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2424, 14, 'Cachoeira do Piriá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2425, 14, 'Cametá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2426, 14, 'Canaã dos Carajás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2427, 14, 'Capanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2428, 14, 'Capitão Poço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2429, 14, 'Castanhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2430, 14, 'Chaves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2431, 14, 'Colares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2432, 14, 'Conceição do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2433, 14, 'Concórdia do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2434, 14, 'Cumaru do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2435, 14, 'Curionópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2436, 14, 'Curralinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2437, 14, 'Curuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2438, 14, 'Curuçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2439, 14, 'Dom Eliseu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2440, 14, 'Eldorado dos Carajás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2441, 14, 'Faro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2442, 14, 'Floresta do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2443, 14, 'Garrafão do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2444, 14, 'Goianésia do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2445, 14, 'Gurupá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2446, 14, 'Igarapé-Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2447, 14, 'Igarapé-Miri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2448, 14, 'Inhangapi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2449, 14, 'Ipixuna do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2450, 14, 'Irituia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2451, 14, 'Itaituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2452, 14, 'Itupiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2453, 14, 'Jacareacanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2454, 14, 'Jacundá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2455, 14, 'Juruti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2456, 14, 'Limoeiro do Ajuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2457, 14, 'Mãe do Rio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2458, 14, 'Magalhães Barata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2459, 14, 'Marabá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2460, 14, 'Maracanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2461, 14, 'Marapanim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2462, 14, 'Marituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2463, 14, 'Medicilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2464, 14, 'Melgaço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2465, 14, 'Mocajuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2466, 14, 'Moju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2467, 14, 'Monte Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2468, 14, 'Muaná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2469, 14, 'Nova Esperança do Piriá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2470, 14, 'Nova Ipixuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2471, 14, 'Nova Timboteua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2472, 14, 'Novo Progresso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2473, 14, 'Novo Repartimento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2474, 14, 'Óbidos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2475, 14, 'Oeiras do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2476, 14, 'Oriximiná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2477, 14, 'Ourém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2478, 14, 'Ourilândia do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2479, 14, 'Pacajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2480, 14, 'Palestina do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2481, 14, 'Paragominas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2482, 14, 'Parauapebas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2483, 14, 'Pau d`Arco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2484, 14, 'Peixe-Boi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2485, 14, 'Piçarra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2486, 14, 'Placas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2487, 14, 'Ponta de Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2488, 14, 'Portel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2489, 14, 'Porto de Moz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2490, 14, 'Prainha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2491, 14, 'Primavera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2492, 14, 'Quatipuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2493, 14, 'Redenção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2494, 14, 'Rio Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2495, 14, 'Rondon do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2496, 14, 'Rurópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2497, 14, 'Salinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2498, 14, 'Salvaterra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2499, 14, 'Santa Bárbara do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2500, 14, 'Santa Cruz do Arari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2501, 14, 'Santa Isabel do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2502, 14, 'Santa Luzia do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2503, 14, 'Santa Maria das Barreiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2504, 14, 'Santa Maria do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2505, 14, 'Santana do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2506, 14, 'Santarém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2507, 14, 'Santarém Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2508, 14, 'Santo Antônio do Tauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2509, 14, 'São Caetano de Odivelas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2510, 14, 'São Domingos do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2511, 14, 'São Domingos do Capim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2512, 14, 'São Félix do Xingu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2513, 14, 'São Francisco do Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2514, 14, 'São Geraldo do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2515, 14, 'São João da Ponta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2516, 14, 'São João de Pirabas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2517, 14, 'São João do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2518, 14, 'São Miguel do Guamá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2519, 14, 'São Sebastião da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2520, 14, 'Sapucaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2521, 14, 'Senador José Porfírio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2522, 14, 'Soure')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2523, 14, 'Tailândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2524, 14, 'Terra Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2525, 14, 'Terra Santa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2526, 14, 'Tomé-Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2527, 14, 'Tracuateua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2528, 14, 'Trairão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2529, 14, 'Tucumã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2530, 14, 'Tucuruí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2531, 14, 'Ulianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2532, 14, 'Uruará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2533, 14, 'Vigia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2534, 14, 'Viseu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2535, 14, 'Vitória do Xingu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2536, 14, 'Xinguara')");


        /* ***************************** P a r a í b a ****************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2537, 15, 'Água Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2538, 15, 'Aguiar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2539, 15, 'Alagoa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2540, 15, 'Alagoa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2541, 15, 'Alagoinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2542, 15, 'Alcantil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2543, 15, 'Algodão de Jandaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2544, 15, 'Alhandra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2545, 15, 'Amparo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2546, 15, 'Aparecida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2547, 15, 'Araçagi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2548, 15, 'Arara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2549, 15, 'Araruna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2550, 15, 'Areia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2551, 15, 'Areia de Baraúnas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2552, 15, 'Areial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2553, 15, 'Aroeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2554, 15, 'Assunção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2555, 15, 'Baía da Traição')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2556, 15, 'Bananeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2557, 15, 'Baraúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2558, 15, 'Barra de Santa Rosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2559, 15, 'Barra de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2560, 15, 'Barra de São Miguel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2561, 15, 'Bayeux')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2562, 15, 'Belém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2563, 15, 'Belém do Brejo do Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2564, 15, 'Bernardino Batista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2565, 15, 'Boa Ventura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2566, 15, 'Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2567, 15, 'Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2568, 15, 'Bom Sucesso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2569, 15, 'Bonito de Santa Fé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2570, 15, 'Boqueirão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2571, 15, 'Borborema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2572, 15, 'Brejo do Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2573, 15, 'Brejo dos Santos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2574, 15, 'Caaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2575, 15, 'Cabaceiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2576, 15, 'Cabedelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2577, 15, 'Cachoeira dos Índios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2578, 15, 'Cacimba de Areia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2579, 15, 'Cacimba de Dentro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2580, 15, 'Cacimbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2581, 15, 'Caiçara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2582, 15, 'Cajazeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2583, 15, 'Cajazeirinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2584, 15, 'Caldas Brandão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2585, 15, 'Camalaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2586, 15, 'Campina Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2587, 15, 'Campo de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2588, 15, 'Capim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2589, 15, 'Caraúbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2590, 15, 'Carrapateira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2591, 15, 'Casserengue')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2592, 15, 'Catingueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2593, 15, 'Catolé do Rocha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2594, 15, 'Caturité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2595, 15, 'Conceição')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2596, 15, 'Condado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2597, 15, 'Conde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2598, 15, 'Congo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2599, 15, 'Coremas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2600, 15, 'Coxixola')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2601, 15, 'Cruz do Espírito Santo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2602, 15, 'Cubati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2603, 15, 'Cuité')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2604, 15, 'Cuité de Mamanguape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2605, 15, 'Cuitegi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2606, 15, 'Curral de Cima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2607, 15, 'Curral Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2608, 15, 'Damião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2609, 15, 'Desterro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2610, 15, 'Diamante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2611, 15, 'Dona Inês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2612, 15, 'Duas Estradas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2613, 15, 'Emas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2614, 15, 'Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2615, 15, 'Fagundes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2616, 15, 'Frei Martinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2617, 15, 'Gado Bravo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2618, 15, 'Guarabira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2619, 15, 'Gurinhém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2620, 15, 'Gurjão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2621, 15, 'Ibiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2622, 15, 'Igaracy')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2623, 15, 'Imaculada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2624, 15, 'Ingá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2625, 15, 'Itabaiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2626, 15, 'Itaporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2627, 15, 'Itapororoca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2628, 15, 'Itatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2629, 15, 'Jacaraú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2630, 15, 'Jericó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2631, 15, 'João Pessoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2632, 15, 'Juarez Távora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2633, 15, 'Juazeirinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2634, 15, 'Junco do Seridó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2635, 15, 'Juripiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2636, 15, 'Juru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2637, 15, 'Lagoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2638, 15, 'Lagoa de Dentro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2639, 15, 'Lagoa Seca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2640, 15, 'Lastro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2641, 15, 'Livramento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2642, 15, 'Logradouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2643, 15, 'Lucena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2644, 15, 'Mãe d`Água')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2645, 15, 'Malta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2646, 15, 'Mamanguape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2647, 15, 'Manaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2648, 15, 'Marcação')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2649, 15, 'Mari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2650, 15, 'Marizópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2651, 15, 'Massaranduba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2652, 15, 'Mataraca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2653, 15, 'Matinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2654, 15, 'Mato Grosso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2655, 15, 'Maturéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2656, 15, 'Mogeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2657, 15, 'Montadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2658, 15, 'Monte Horebe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2659, 15, 'Monteiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2660, 15, 'Mulungu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2661, 15, 'Natuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2662, 15, 'Nazarezinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2663, 15, 'Nova Floresta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2664, 15, 'Nova Olinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2665, 15, 'Nova Palmeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2666, 15, 'Olho d`Água')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2667, 15, 'Olivedos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2668, 15, 'Ouro Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2669, 15, 'Parari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2670, 15, 'Passagem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2671, 15, 'Patos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2672, 15, 'Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2673, 15, 'Pedra Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2674, 15, 'Pedra Lavrada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2675, 15, 'Pedras de Fogo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2676, 15, 'Pedro Régis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2677, 15, 'Piancó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2678, 15, 'Picuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2679, 15, 'Pilar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2680, 15, 'Pilões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2681, 15, 'Pilõezinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2682, 15, 'Pirpirituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2683, 15, 'Pitimbu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2684, 15, 'Pocinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2685, 15, 'Poço Dantas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2686, 15, 'Poço de José de Moura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2687, 15, 'Pombal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2688, 15, 'Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2689, 15, 'Princesa Isabel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2690, 15, 'Puxinanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2691, 15, 'Queimadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2692, 15, 'Quixabá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2693, 15, 'Remígio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2694, 15, 'Riachão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2695, 15, 'Riachão do Bacamarte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2696, 15, 'Riachão do Poço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2697, 15, 'Riacho de Santo Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2698, 15, 'Riacho dos Cavalos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2699, 15, 'Rio Tinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2700, 15, 'Salgadinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2701, 15, 'Salgado de São Félix')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2702, 15, 'Santa Cecília')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2703, 15, 'Santa Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2704, 15, 'Santa Helena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2705, 15, 'Santa Inês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2706, 15, 'Santa Luzia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2707, 15, 'Santa Rita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2708, 15, 'Santa Teresinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2709, 15, 'Santana de Mangueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2710, 15, 'Santana dos Garrotes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2711, 15, 'Santarém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2712, 15, 'Santo André')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2713, 15, 'São Bentinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2714, 15, 'São Bento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2715, 15, 'São Domingos de Pombal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2716, 15, 'São Domingos do Cariri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2717, 15, 'São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2718, 15, 'São João do Cariri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2719, 15, 'São João do Rio do Peixe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2720, 15, 'São João do Tigre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2721, 15, 'São José da Lagoa Tapada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2722, 15, 'São José de Caiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2723, 15, 'São José de Espinharas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2724, 15, 'São José de Piranhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2725, 15, 'São José de Princesa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2726, 15, 'São José do Bonfim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2727, 15, 'São José do Brejo do Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2728, 15, 'São José do Sabugi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2729, 15, 'São José dos Cordeiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2730, 15, 'São José dos Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2731, 15, 'São Mamede')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2732, 15, 'São Miguel de Taipu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2733, 15, 'São Sebastião de Lagoa de Roça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2734, 15, 'São Sebastião do Umbuzeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2735, 15, 'Sapé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2736, 15, 'Seridó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2737, 15, 'Serra Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2738, 15, 'Serra da Raiz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2739, 15, 'Serra Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2740, 15, 'Serra Redonda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2741, 15, 'Serraria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2742, 15, 'Sertãozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2743, 15, 'Sobrado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2744, 15, 'Solânea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2745, 15, 'Soledade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2746, 15, 'Sossêgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2747, 15, 'Sousa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2748, 15, 'Sumé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2749, 15, 'Taperoá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2750, 15, 'Tavares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2751, 15, 'Teixeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2752, 15, 'Tenório')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2753, 15, 'Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2754, 15, 'Uiraúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2755, 15, 'Umbuzeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2756, 15, 'Várzea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2757, 15, 'Vieirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2758, 15, 'Vista Serrana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2759, 15, 'Zabelê')");


        /* ****************************** P a r a n á ******************************* */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2760, 16, 'Abatiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2761, 16, 'Adrianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2762, 16, 'Agudos do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2763, 16, 'Almirante Tamandaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2764, 16, 'Altamira do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2765, 16, 'Alto Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2766, 16, 'Alto Piquiri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2767, 16, 'Altônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2768, 16, 'Alvorada do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2769, 16, 'Amaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2770, 16, 'Ampére')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2771, 16, 'Anahy')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2772, 16, 'Andirá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2773, 16, 'Ângulo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2774, 16, 'Antonina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2775, 16, 'Antônio Olinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2776, 16, 'Apucarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2777, 16, 'Arapongas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2778, 16, 'Arapoti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2779, 16, 'Arapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2780, 16, 'Araruna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2781, 16, 'Araucária')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2782, 16, 'Ariranha do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2783, 16, 'Assaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2784, 16, 'Assis Chateaubriand')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2785, 16, 'Astorga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2786, 16, 'Atalaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2787, 16, 'Balsa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2788, 16, 'Bandeirantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2789, 16, 'Barbosa Ferraz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2790, 16, 'Barra do Jacaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2791, 16, 'Barracão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2792, 16, 'Bela Vista da Caroba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2793, 16, 'Bela Vista do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2794, 16, 'Bituruna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2795, 16, 'Boa Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2796, 16, 'Boa Esperança do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2797, 16, 'Boa Ventura de São Roque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2798, 16, 'Boa Vista da Aparecida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2799, 16, 'Bocaiúva do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2800, 16, 'Bom Jesus do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2801, 16, 'Bom Sucesso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2802, 16, 'Bom Sucesso do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2803, 16, 'Borrazópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2804, 16, 'Braganey')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2805, 16, 'Brasilândia do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2806, 16, 'Cafeara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2807, 16, 'Cafelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2808, 16, 'Cafezal do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2809, 16, 'Califórnia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2810, 16, 'Cambará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2811, 16, 'Cambé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2812, 16, 'Cambira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2813, 16, 'Campina da Lagoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2814, 16, 'Campina do Simão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2815, 16, 'Campina Grande do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2816, 16, 'Campo Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2817, 16, 'Campo do Tenente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2818, 16, 'Campo Largo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2819, 16, 'Campo Magro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2820, 16, 'Campo Mourão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2821, 16, 'Cândido de Abreu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2822, 16, 'Candói')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2823, 16, 'Cantagalo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2824, 16, 'Capanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2825, 16, 'Capitão Leônidas Marques')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2826, 16, 'Carambeí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2827, 16, 'Carlópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2828, 16, 'Cascavel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2829, 16, 'Castro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2830, 16, 'Catanduvas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2831, 16, 'Centenário do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2832, 16, 'Cerro Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2833, 16, 'Céu Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2834, 16, 'Chopinzinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2835, 16, 'Cianorte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2836, 16, 'Cidade Gaúcha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2837, 16, 'Clevelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2838, 16, 'Colombo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2839, 16, 'Colorado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2840, 16, 'Congonhinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2841, 16, 'Conselheiro Mairinck')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2842, 16, 'Contenda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2843, 16, 'Corbélia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2844, 16, 'Cornélio Procópio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2845, 16, 'Coronel Domingos Soares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2846, 16, 'Coronel Vivida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2847, 16, 'Corumbataí do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2848, 16, 'Cruz Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2849, 16, 'Cruzeiro do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2850, 16, 'Cruzeiro do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2851, 16, 'Cruzeiro do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2852, 16, 'Cruzmaltina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2853, 16, 'Curitiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2854, 16, 'Curiúva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2855, 16, 'Diamante d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2856, 16, 'Diamante do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2857, 16, 'Diamante do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2858, 16, 'Dois Vizinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2859, 16, 'Douradina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2860, 16, 'Doutor Camargo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2861, 16, 'Doutor Ulysses')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2862, 16, 'Enéas Marques')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2863, 16, 'Engenheiro Beltrão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2864, 16, 'Entre Rios do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2865, 16, 'Esperança Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2866, 16, 'Espigão Alto do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2867, 16, 'Farol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2868, 16, 'Faxinal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2869, 16, 'Fazenda Rio Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2870, 16, 'Fênix')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2871, 16, 'Fernandes Pinheiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2872, 16, 'Figueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2873, 16, 'Flor da Serra do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2874, 16, 'Floraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2875, 16, 'Floresta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2876, 16, 'Florestópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2877, 16, 'Flórida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2878, 16, 'Formosa do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2879, 16, 'Foz do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2880, 16, 'Foz do Jordão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2881, 16, 'Francisco Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2882, 16, 'Francisco Beltrão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2883, 16, 'General Carneiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2884, 16, 'Godoy Moreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2885, 16, 'Goioerê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2886, 16, 'Goioxim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2887, 16, 'Grandes Rios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2888, 16, 'Guaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2889, 16, 'Guairaçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2890, 16, 'Guamiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2891, 16, 'Guapirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2892, 16, 'Guaporema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2893, 16, 'Guaraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2894, 16, 'Guaraniaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2895, 16, 'Guarapuava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2896, 16, 'Guaraqueçaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2897, 16, 'Guaratuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2898, 16, 'Honório Serpa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2899, 16, 'Ibaiti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2900, 16, 'Ibema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2901, 16, 'Ibiporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2902, 16, 'Icaraíma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2903, 16, 'Iguaraçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2904, 16, 'Iguatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2905, 16, 'Imbaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2906, 16, 'Imbituva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2907, 16, 'Inácio Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2908, 16, 'Inajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2909, 16, 'Indianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2910, 16, 'Ipiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2911, 16, 'Iporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2912, 16, 'Iracema do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2913, 16, 'Irati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2914, 16, 'Iretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2915, 16, 'Itaguajé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2916, 16, 'Itaipulândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2917, 16, 'Itambaracá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2918, 16, 'Itambé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2919, 16, 'Itapejara d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2920, 16, 'Itaperuçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2921, 16, 'Itaúna do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2922, 16, 'Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2923, 16, 'Ivaiporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2924, 16, 'Ivaté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2925, 16, 'Ivatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2926, 16, 'Jaboti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2927, 16, 'Jacarezinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2928, 16, 'Jaguapitã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2929, 16, 'Jaguariaíva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2930, 16, 'Jandaia do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2931, 16, 'Janiópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2932, 16, 'Japira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2933, 16, 'Japurá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2934, 16, 'Jardim Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2935, 16, 'Jardim Olinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2936, 16, 'Jataizinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2937, 16, 'Jesuítas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2938, 16, 'Joaquim Távora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2939, 16, 'Jundiaí do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2940, 16, 'Juranda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2941, 16, 'Jussara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2942, 16, 'Kaloré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2943, 16, 'Lapa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2944, 16, 'Laranjal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2945, 16, 'Laranjeiras do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2946, 16, 'Leópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2947, 16, 'Lidianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2948, 16, 'Lindoeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2949, 16, 'Loanda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2950, 16, 'Lobato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2951, 16, 'Londrina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2952, 16, 'Luiziana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2953, 16, 'Lunardelli')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2954, 16, 'Lupionópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2955, 16, 'Mallet')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2956, 16, 'Mamborê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2957, 16, 'Mandaguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2958, 16, 'Mandaguari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2959, 16, 'Mandirituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2960, 16, 'Manfrinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2961, 16, 'Mangueirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2962, 16, 'Manoel Ribas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2963, 16, 'Marechal Cândido Rondon')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2964, 16, 'Maria Helena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2965, 16, 'Marialva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2966, 16, 'Marilândia do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2967, 16, 'Marilena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2968, 16, 'Mariluz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2969, 16, 'Maringá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2970, 16, 'Mariópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2971, 16, 'Maripá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2972, 16, 'Marmeleiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2973, 16, 'Marquinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2974, 16, 'Marumbi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2975, 16, 'Matelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2976, 16, 'Matinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2977, 16, 'Mato Rico')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2978, 16, 'Mauá da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2979, 16, 'Medianeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2980, 16, 'Mercedes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2981, 16, 'Mirador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2982, 16, 'Miraselva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2983, 16, 'Missal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2984, 16, 'Moreira Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2985, 16, 'Morretes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2986, 16, 'Munhoz de Melo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2987, 16, 'Nossa Senhora das Graças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2988, 16, 'Nova Aliança do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2989, 16, 'Nova América da Colina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2990, 16, 'Nova Aurora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2991, 16, 'Nova Cantu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2992, 16, 'Nova Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2993, 16, 'Nova Esperança do Sudoeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2994, 16, 'Nova Fátima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2995, 16, 'Nova Laranjeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2996, 16, 'Nova Londrina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2997, 16, 'Nova Olímpia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2998, 16, 'Nova Prata do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (2999, 16, 'Nova Santa Bárbara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3000, 16, 'Nova Santa Rosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3001, 16, 'Nova Tebas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3002, 16, 'Novo Itacolomi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3003, 16, 'Ortigueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3004, 16, 'Ourizona')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3005, 16, 'Ouro Verde do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3006, 16, 'Paiçandu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3007, 16, 'Palmas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3008, 16, 'Palmeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3009, 16, 'Palmital')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3010, 16, 'Palotina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3011, 16, 'Paraíso do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3012, 16, 'Paranacity')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3013, 16, 'Paranaguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3014, 16, 'Paranapoema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3015, 16, 'Paranavaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3016, 16, 'Pato Bragado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3017, 16, 'Pato Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3018, 16, 'Paula Freitas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3019, 16, 'Paulo Frontin')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3020, 16, 'Peabiru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3021, 16, 'Perobal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3022, 16, 'Pérola')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3023, 16, 'Pérola d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3024, 16, 'Piên')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3025, 16, 'Pinhais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3026, 16, 'Pinhal de São Bento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3027, 16, 'Pinhalão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3028, 16, 'Pinhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3029, 16, 'Piraí do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3030, 16, 'Piraquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3031, 16, 'Pitanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3032, 16, 'Pitangueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3033, 16, 'Planaltina do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3034, 16, 'Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3035, 16, 'Ponta Grossa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3036, 16, 'Pontal do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3037, 16, 'Porecatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3038, 16, 'Porto Amazonas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3039, 16, 'Porto Barreiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3040, 16, 'Porto Rico')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3041, 16, 'Porto Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3042, 16, 'Prado Ferreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3043, 16, 'Pranchita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3044, 16, 'Presidente Castelo Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3045, 16, 'Primeiro de Maio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3046, 16, 'Prudentópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3047, 16, 'Quarto Centenário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3048, 16, 'Quatiguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3049, 16, 'Quatro Barras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3050, 16, 'Quatro Pontes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3051, 16, 'Quedas do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3052, 16, 'Querência do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3053, 16, 'Quinta do Sol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3054, 16, 'Quitandinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3055, 16, 'Ramilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3056, 16, 'Rancho Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3057, 16, 'Rancho Alegre d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3058, 16, 'Realeza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3059, 16, 'Rebouças')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3060, 16, 'Renascença')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3061, 16, 'Reserva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3062, 16, 'Reserva do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3063, 16, 'Ribeirão Claro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3064, 16, 'Ribeirão do Pinhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3065, 16, 'Rio Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3066, 16, 'Rio Bom')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3067, 16, 'Rio Bonito do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3068, 16, 'Rio Branco do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3069, 16, 'Rio Branco do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3070, 16, 'Rio Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3071, 16, 'Rolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3072, 16, 'Roncador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3073, 16, 'Rondon')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3074, 16, 'Rosário do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3075, 16, 'Sabáudia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3076, 16, 'Salgado Filho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3077, 16, 'Salto do Itararé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3078, 16, 'Salto do Lontra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3079, 16, 'Santa Amélia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3080, 16, 'Santa Cecília do Pavão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3081, 16, 'Santa Cruz de Monte Castelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3082, 16, 'Santa Fé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3083, 16, 'Santa Helena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3084, 16, 'Santa Inês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3085, 16, 'Santa Isabel do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3086, 16, 'Santa Izabel do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3087, 16, 'Santa Lúcia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3088, 16, 'Santa Maria do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3089, 16, 'Santa Mariana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3090, 16, 'Santa Mônica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3091, 16, 'Santa Tereza do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3092, 16, 'Santa Terezinha de Itaipu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3093, 16, 'Santana do Itararé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3094, 16, 'Santo Antônio da Platina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3095, 16, 'Santo Antônio do Caiuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3096, 16, 'Santo Antônio do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3097, 16, 'Santo Antônio do Sudoeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3098, 16, 'Santo Inácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3099, 16, 'São Carlos do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3100, 16, 'São Jerônimo da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3101, 16, 'São João')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3102, 16, 'São João do Caiuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3103, 16, 'São João do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3104, 16, 'São João do Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3105, 16, 'São Jorge d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3106, 16, 'São Jorge do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3107, 16, 'São Jorge do Patrocínio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3108, 16, 'São José da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3109, 16, 'São José das Palmeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3110, 16, 'São José dos Pinhais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3111, 16, 'São Manoel do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3112, 16, 'São Mateus do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3113, 16, 'São Miguel do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3114, 16, 'São Pedro do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3115, 16, 'São Pedro do Ivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3116, 16, 'São Pedro do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3117, 16, 'São Sebastião da Amoreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3118, 16, 'São Tomé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3119, 16, 'Sapopema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3120, 16, 'Sarandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3121, 16, 'Saudade do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3122, 16, 'Sengés')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3123, 16, 'Serranópolis do Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3124, 16, 'Sertaneja')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3125, 16, 'Sertanópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3126, 16, 'Siqueira Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3127, 16, 'Sulina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3128, 16, 'Tamarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3129, 16, 'Tamboara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3130, 16, 'Tapejara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3131, 16, 'Tapira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3132, 16, 'Teixeira Soares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3133, 16, 'Telêmaco Borba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3134, 16, 'Terra Boa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3135, 16, 'Terra Rica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3136, 16, 'Terra Roxa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3137, 16, 'Tibagi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3138, 16, 'Tijucas do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3139, 16, 'Toledo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3140, 16, 'Tomazina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3141, 16, 'Três Barras do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3142, 16, 'Tunas do Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3143, 16, 'Tuneiras do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3144, 16, 'Tupãssi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3145, 16, 'Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3146, 16, 'Ubiratã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3147, 16, 'Umuarama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3148, 16, 'União da Vitória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3149, 16, 'Uniflor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3150, 16, 'Uraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3151, 16, 'Ventania')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3152, 16, 'Vera Cruz do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3153, 16, 'Verê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3154, 16, 'Vila Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3155, 16, 'Virmond')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3156, 16, 'Vitorino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3157, 16, 'Wenceslau Braz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3158, 16, 'Xambrê')");


        /* ************************** P e r n a m b u c o *************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3159, 17, 'Abreu e Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3160, 17, 'Afogados da Ingazeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3161, 17, 'Afrânio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3162, 17, 'Agrestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3163, 17, 'Água Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3164, 17, 'Águas Belas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3165, 17, 'Alagoinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3166, 17, 'Aliança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3167, 17, 'Altinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3168, 17, 'Amaraji')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3169, 17, 'Angelim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3170, 17, 'Araçoiaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3171, 17, 'Araripina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3172, 17, 'Arcoverde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3173, 17, 'Barra de Guabiraba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3174, 17, 'Barreiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3175, 17, 'Belém de Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3176, 17, 'Belém de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3177, 17, 'Belo Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3178, 17, 'Betânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3179, 17, 'Bezerros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3180, 17, 'Bodocó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3181, 17, 'Bom Conselho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3182, 17, 'Bom Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3183, 17, 'Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3184, 17, 'Brejão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3185, 17, 'Brejinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3186, 17, 'Brejo da Madre de Deus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3187, 17, 'Buenos Aires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3188, 17, 'Buíque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3189, 17, 'Cabo de Santo Agostinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3190, 17, 'Cabrobó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3191, 17, 'Cachoeirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3192, 17, 'Caetés')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3193, 17, 'Calçado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3194, 17, 'Calumbi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3195, 17, 'Camaragibe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3196, 17, 'Camocim de São Félix')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3197, 17, 'Camutanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3198, 17, 'Canhotinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3199, 17, 'Capoeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3200, 17, 'Carnaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3201, 17, 'Carnaubeira da Penha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3202, 17, 'Carpina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3203, 17, 'Caruaru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3204, 17, 'Casinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3205, 17, 'Catende')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3206, 17, 'Cedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3207, 17, 'Chã de Alegria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3208, 17, 'Chã Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3209, 17, 'Condado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3210, 17, 'Correntes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3211, 17, 'Cortês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3212, 17, 'Cumaru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3213, 17, 'Cupira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3214, 17, 'Custódia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3215, 17, 'Dormentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3216, 17, 'Escada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3217, 17, 'Exu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3218, 17, 'Feira Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3219, 17, 'Fernando de Noronha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3220, 17, 'Ferreiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3221, 17, 'Flores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3222, 17, 'Floresta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3223, 17, 'Frei Miguelinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3224, 17, 'Gameleira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3225, 17, 'Garanhuns')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3226, 17, 'Glória do Goitá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3227, 17, 'Goiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3228, 17, 'Granito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3229, 17, 'Gravatá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3230, 17, 'Iati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3231, 17, 'Ibimirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3232, 17, 'Ibirajuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3233, 17, 'Igarassu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3234, 17, 'Iguaraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3235, 17, 'Inajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3236, 17, 'Ingazeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3237, 17, 'Ipojuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3238, 17, 'Ipubi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3239, 17, 'Itacuruba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3240, 17, 'Itaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3241, 17, 'Itamaracá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3242, 17, 'Itambé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3243, 17, 'Itapetim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3244, 17, 'Itapissuma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3245, 17, 'Itaquitinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3246, 17, 'Jaboatão dos Guararapes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3247, 17, 'Jaqueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3248, 17, 'Jataúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3249, 17, 'Jatobá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3250, 17, 'João Alfredo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3251, 17, 'Joaquim Nabuco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3252, 17, 'Jucati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3253, 17, 'Jupi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3254, 17, 'Jurema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3255, 17, 'Lagoa do Carro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3256, 17, 'Lagoa do Itaenga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3257, 17, 'Lagoa do Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3258, 17, 'Lagoa dos Gatos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3259, 17, 'Lagoa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3260, 17, 'Lajedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3261, 17, 'Limoeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3262, 17, 'Macaparana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3263, 17, 'Machados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3264, 17, 'Manari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3265, 17, 'Maraial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3266, 17, 'Mirandiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3267, 17, 'Moreilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3268, 17, 'Moreno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3269, 17, 'Nazaré da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3270, 17, 'Olinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3271, 17, 'Orobó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3272, 17, 'Orocó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3273, 17, 'Ouricuri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3274, 17, 'Palmares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3275, 17, 'Palmeirina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3276, 17, 'Panelas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3277, 17, 'Paranatama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3278, 17, 'Parnamirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3279, 17, 'Passira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3280, 17, 'Paudalho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3281, 17, 'Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3282, 17, 'Pedra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3283, 17, 'Pesqueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3284, 17, 'Petrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3285, 17, 'Petrolina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3286, 17, 'Poção')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3287, 17, 'Pombos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3288, 17, 'Primavera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3289, 17, 'Quipapá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3290, 17, 'Quixaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3291, 17, 'Recife')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3292, 17, 'Riacho das Almas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3293, 17, 'Ribeirão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3294, 17, 'Rio Formoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3295, 17, 'Sairé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3296, 17, 'Salgadinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3297, 17, 'Salgueiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3298, 17, 'Saloá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3299, 17, 'Sanharó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3300, 17, 'Santa Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3301, 17, 'Santa Cruz da Baixa Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3302, 17, 'Santa Cruz do Capibaribe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3303, 17, 'Santa Filomena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3304, 17, 'Santa Maria da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3305, 17, 'Santa Maria do Cambucá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3306, 17, 'Santa Terezinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3307, 17, 'São Benedito do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3308, 17, 'São Bento do Una')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3309, 17, 'São Caitano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3310, 17, 'São João')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3311, 17, 'São Joaquim do Monte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3312, 17, 'São José da Coroa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3313, 17, 'São José do Belmonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3314, 17, 'São José do Egito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3315, 17, 'São Lourenço da Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3316, 17, 'São Vicente Ferrer')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3317, 17, 'Serra Talhada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3318, 17, 'Serrita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3319, 17, 'Sertânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3320, 17, 'Sirinhaém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3321, 17, 'Solidão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3322, 17, 'Surubim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3323, 17, 'Tabira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3324, 17, 'Tacaimbó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3325, 17, 'Tacaratu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3326, 17, 'Tamandaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3327, 17, 'Taquaritinga do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3328, 17, 'Terezinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3329, 17, 'Terra Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3330, 17, 'Timbaúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3331, 17, 'Toritama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3332, 17, 'Tracunhaém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3333, 17, 'Trindade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3334, 17, 'Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3335, 17, 'Tupanatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3336, 17, 'Tuparetama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3337, 17, 'Venturosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3338, 17, 'Verdejante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3339, 17, 'Vertente do Lério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3340, 17, 'Vertentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3341, 17, 'Vicência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3342, 17, 'Vitória de Santo Antão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3343, 17, 'Xexéu')");


        /* ******************************* P i a u í ******************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3344, 18, 'Acauã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3345, 18, 'Agricolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3346, 18, 'Água Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3347, 18, 'Alagoinha do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3348, 18, 'Alegrete do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3349, 18, 'Alto Longá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3350, 18, 'Altos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3351, 18, 'Alvorada do Gurguéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3352, 18, 'Amarante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3353, 18, 'Angical do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3354, 18, 'Anísio de Abreu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3355, 18, 'Antônio Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3356, 18, 'Aroazes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3357, 18, 'Arraial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3358, 18, 'Assunção do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3359, 18, 'Avelino Lopes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3360, 18, 'Baixa Grande do Ribeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3361, 18, 'Barra d`Alcântara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3362, 18, 'Barras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3363, 18, 'Barreiras do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3364, 18, 'Barro Duro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3365, 18, 'Batalha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3366, 18, 'Bela Vista do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3367, 18, 'Belém do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3368, 18, 'Beneditinos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3369, 18, 'Bertolínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3370, 18, 'Betânia do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3371, 18, 'Boa Hora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3372, 18, 'Bocaina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3373, 18, 'Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3374, 18, 'Bom Princípio do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3375, 18, 'Bonfim do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3376, 18, 'Boqueirão do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3377, 18, 'Brasileira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3378, 18, 'Brejo do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3379, 18, 'Buriti dos Lopes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3380, 18, 'Buriti dos Montes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3381, 18, 'Cabeceiras do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3382, 18, 'Cajazeiras do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3383, 18, 'Cajueiro da Praia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3384, 18, 'Caldeirão Grande do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3385, 18, 'Campinas do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3386, 18, 'Campo Alegre do Fidalgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3387, 18, 'Campo Grande do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3388, 18, 'Campo Largo do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3389, 18, 'Campo Maior')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3390, 18, 'Canavieira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3391, 18, 'Canto do Buriti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3392, 18, 'Capitão de Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3393, 18, 'Capitão Gervásio Oliveira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3394, 18, 'Caracol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3395, 18, 'Caraúbas do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3396, 18, 'Caridade do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3397, 18, 'Castelo do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3398, 18, 'Caxingó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3399, 18, 'Cocal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3400, 18, 'Cocal de Telha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3401, 18, 'Cocal dos Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3402, 18, 'Coivaras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3403, 18, 'Colônia do Gurguéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3404, 18, 'Colônia do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3405, 18, 'Conceição do Canindé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3406, 18, 'Coronel José Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3407, 18, 'Corrente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3408, 18, 'Cristalândia do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3409, 18, 'Cristino Castro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3410, 18, 'Curimatá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3411, 18, 'Currais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3412, 18, 'Curral Novo do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3413, 18, 'Curralinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3414, 18, 'Demerval Lobão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3415, 18, 'Dirceu Arcoverde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3416, 18, 'Dom Expedito Lopes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3417, 18, 'Dom Inocêncio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3418, 18, 'Domingos Mourão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3419, 18, 'Elesbão Veloso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3420, 18, 'Eliseu Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3421, 18, 'Esperantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3422, 18, 'Fartura do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3423, 18, 'Flores do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3424, 18, 'Floresta do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3425, 18, 'Floriano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3426, 18, 'Francinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3427, 18, 'Francisco Ayres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3428, 18, 'Francisco Macedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3429, 18, 'Francisco Santos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3430, 18, 'Fronteiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3431, 18, 'Geminiano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3432, 18, 'Gilbués')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3433, 18, 'Guadalupe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3434, 18, 'Guaribas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3435, 18, 'Hugo Napoleão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3436, 18, 'Ilha Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3437, 18, 'Inhuma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3438, 18, 'Ipiranga do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3439, 18, 'Isaías Coelho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3440, 18, 'Itainópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3441, 18, 'Itaueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3442, 18, 'Jacobina do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3443, 18, 'Jaicós')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3444, 18, 'Jardim do Mulato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3445, 18, 'Jatobá do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3446, 18, 'Jerumenha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3447, 18, 'João Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3448, 18, 'Joaquim Pires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3449, 18, 'Joca Marques')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3450, 18, 'José de Freitas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3451, 18, 'Juazeiro do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3452, 18, 'Júlio Borges')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3453, 18, 'Jurema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3454, 18, 'Lagoa Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3455, 18, 'Lagoa de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3456, 18, 'Lagoa do Barro do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3457, 18, 'Lagoa do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3458, 18, 'Lagoa do Sítio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3459, 18, 'Lagoinha do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3460, 18, 'Landri Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3461, 18, 'Luís Correia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3462, 18, 'Luzilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3463, 18, 'Madeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3464, 18, 'Manoel Emídio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3465, 18, 'Marcolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3466, 18, 'Marcos Parente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3467, 18, 'Massapê do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3468, 18, 'Matias Olímpio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3469, 18, 'Miguel Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3470, 18, 'Miguel Leão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3471, 18, 'Milton Brandão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3472, 18, 'Monsenhor Gil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3473, 18, 'Monsenhor Hipólito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3474, 18, 'Monte Alegre do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3475, 18, 'Morro Cabeça no Tempo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3476, 18, 'Morro do Chapéu do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3477, 18, 'Murici dos Portelas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3478, 18, 'Nazaré do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3479, 18, 'Nossa Senhora de Nazaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3480, 18, 'Nossa Senhora dos Remédios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3481, 18, 'Nova Santa Rita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3482, 18, 'Novo Oriente do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3483, 18, 'Novo Santo Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3484, 18, 'Oeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3485, 18, 'Olho d`Água do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3486, 18, 'Padre Marcos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3487, 18, 'Paes Landim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3488, 18, 'Pajeú do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3489, 18, 'Palmeira do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3490, 18, 'Palmeirais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3491, 18, 'Paquetá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3492, 18, 'Parnaguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3493, 18, 'Parnaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3494, 18, 'Passagem Franca do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3495, 18, 'Patos do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3496, 18, 'Paulistana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3497, 18, 'Pavussu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3498, 18, 'Pedro II')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3499, 18, 'Pedro Laurentino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3500, 18, 'Picos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3501, 18, 'Pimenteiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3502, 18, 'Pio IX')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3503, 18, 'Piracuruca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3504, 18, 'Piripiri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3505, 18, 'Porto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3506, 18, 'Porto Alegre do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3507, 18, 'Prata do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3508, 18, 'Queimada Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3509, 18, 'Redenção do Gurguéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3510, 18, 'Regeneração')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3511, 18, 'Riacho Frio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3512, 18, 'Ribeira do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3513, 18, 'Ribeiro Gonçalves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3514, 18, 'Rio Grande do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3515, 18, 'Santa Cruz do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3516, 18, 'Santa Cruz dos Milagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3517, 18, 'Santa Filomena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3518, 18, 'Santa Luz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3519, 18, 'Santa Rosa do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3520, 18, 'Santana do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3521, 18, 'Santo Antônio de Lisboa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3522, 18, 'Santo Antônio dos Milagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3523, 18, 'Santo Inácio do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3524, 18, 'São Braz do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3525, 18, 'São Félix do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3526, 18, 'São Francisco de Assis do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3527, 18, 'São Francisco do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3528, 18, 'São Gonçalo do Gurguéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3529, 18, 'São Gonçalo do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3530, 18, 'São João da Canabrava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3531, 18, 'São João da Fronteira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3532, 18, 'São João da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3533, 18, 'São João da Varjota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3534, 18, 'São João do Arraial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3535, 18, 'São João do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3536, 18, 'São José do Divino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3537, 18, 'São José do Peixe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3538, 18, 'São José do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3539, 18, 'São Julião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3540, 18, 'São Lourenço do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3541, 18, 'São Luis do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3542, 18, 'São Miguel da Baixa Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3543, 18, 'São Miguel do Fidalgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3544, 18, 'São Miguel do Tapuio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3545, 18, 'São Pedro do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3546, 18, 'São Raimundo Nonato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3547, 18, 'Sebastião Barros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3548, 18, 'Sebastião Leal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3549, 18, 'Sigefredo Pacheco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3550, 18, 'Simões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3551, 18, 'Simplício Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3552, 18, 'Socorro do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3553, 18, 'Sussuapara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3554, 18, 'Tamboril do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3555, 18, 'Tanque do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3556, 18, 'Teresina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3557, 18, 'União')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3558, 18, 'Uruçuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3559, 18, 'Valença do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3560, 18, 'Várzea Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3561, 18, 'Várzea Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3562, 18, 'Vera Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3563, 18, 'Vila Nova do Piauí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3564, 18, 'Wall Ferraz')");


        /* ********************** R i o   d e   J a n e i r o *********************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3565, 19, 'Angra dos Reis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3566, 19, 'Aperibé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3567, 19, 'Araruama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3568, 19, 'Areal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3569, 19, 'Armação dos Búzios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3570, 19, 'Arraial do Cabo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3571, 19, 'Barra do Piraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3572, 19, 'Barra Mansa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3573, 19, 'Belford Roxo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3574, 19, 'Bom Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3575, 19, 'Bom Jesus do Itabapoana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3576, 19, 'Cabo Frio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3577, 19, 'Cachoeiras de Macacu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3578, 19, 'Cambuci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3579, 19, 'Campos dos Goytacazes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3580, 19, 'Cantagalo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3581, 19, 'Carapebus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3582, 19, 'Cardoso Moreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3583, 19, 'Carmo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3584, 19, 'Casimiro de Abreu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3585, 19, 'Comendador Levy Gasparian')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3586, 19, 'Conceição de Macabu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3587, 19, 'Cordeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3588, 19, 'Duas Barras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3589, 19, 'Duque de Caxias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3590, 19, 'Engenheiro Paulo de Frontin')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3591, 19, 'Guapimirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3592, 19, 'Iguaba Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3593, 19, 'Itaboraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3594, 19, 'Itaguaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3595, 19, 'Italva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3596, 19, 'Itaocara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3597, 19, 'Itaperuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3598, 19, 'Itatiaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3599, 19, 'Japeri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3600, 19, 'Laje do Muriaé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3601, 19, 'Macaé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3602, 19, 'Macuco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3603, 19, 'Magé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3604, 19, 'Mangaratiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3605, 19, 'Maricá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3606, 19, 'Mendes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3607, 19, 'Miguel Pereira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3608, 19, 'Miracema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3609, 19, 'Natividade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3610, 19, 'Nilópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3611, 19, 'Niterói')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3612, 19, 'Nova Friburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3613, 19, 'Nova Iguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3614, 19, 'Paracambi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3615, 19, 'Paraíba do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3616, 19, 'Parati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3617, 19, 'Paty do Alferes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3618, 19, 'Petrópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3619, 19, 'Pinheiral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3620, 19, 'Piraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3621, 19, 'Porciúncula')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3622, 19, 'Porto Real')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3623, 19, 'Quatis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3624, 19, 'Queimados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3625, 19, 'Quissamã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3626, 19, 'Resende')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3627, 19, 'Rio Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3628, 19, 'Rio Claro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3629, 19, 'Rio das Flores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3630, 19, 'Rio das Ostras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3631, 19, 'Rio de Janeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3632, 19, 'Santa Maria Madalena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3633, 19, 'Santo Antônio de Pádua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3634, 19, 'São Fidélis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3635, 19, 'São Francisco de Itabapoana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3636, 19, 'São Gonçalo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3637, 19, 'São João da Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3638, 19, 'São João de Meriti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3639, 19, 'São José de Ubá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3640, 19, 'São José do Vale do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3641, 19, 'São Pedro da Aldeia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3642, 19, 'São Sebastião do Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3643, 19, 'Sapucaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3644, 19, 'Saquarema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3645, 19, 'Seropédica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3646, 19, 'Silva Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3647, 19, 'Sumidouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3648, 19, 'Tanguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3649, 19, 'Teresópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3650, 19, 'Trajano de Morais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3651, 19, 'Três Rios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3652, 19, 'Valença')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3653, 19, 'Varre-Sai')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3654, 19, 'Vassouras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3655, 19, 'Volta Redonda')");


        /* ***************** R i o   G r a n d e   d o   N o r t e ****************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3656, 20, 'Acari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3657, 20, 'Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3658, 20, 'Afonso Bezerra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3659, 20, 'Água Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3660, 20, 'Alexandria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3661, 20, 'Almino Afonso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3662, 20, 'Alto do Rodrigues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3663, 20, 'Angicos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3664, 20, 'Antônio Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3665, 20, 'Apodi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3666, 20, 'Areia Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3667, 20, 'Arês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3668, 20, 'Augusto Severo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3669, 20, 'Baía Formosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3670, 20, 'Baraúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3671, 20, 'Barcelona')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3672, 20, 'Bento Fernandes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3673, 20, 'Bodó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3674, 20, 'Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3675, 20, 'Brejinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3676, 20, 'Caiçara do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3677, 20, 'Caiçara do Rio do Vento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3678, 20, 'Caicó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3679, 20, 'Campo Redondo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3680, 20, 'Canguaretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3681, 20, 'Caraúbas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3682, 20, 'Carnaúba dos Dantas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3683, 20, 'Carnaubais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3684, 20, 'Ceará-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3685, 20, 'Cerro Corá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3686, 20, 'Coronel Ezequiel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3687, 20, 'Coronel João Pessoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3688, 20, 'Cruzeta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3689, 20, 'Currais Novos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3690, 20, 'Doutor Severiano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3691, 20, 'Encanto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3692, 20, 'Equador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3693, 20, 'Espírito Santo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3694, 20, 'Extremoz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3695, 20, 'Felipe Guerra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3696, 20, 'Fernando Pedroza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3697, 20, 'Florânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3698, 20, 'Francisco Dantas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3699, 20, 'Frutuoso Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3700, 20, 'Galinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3701, 20, 'Goianinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3702, 20, 'Governador Dix-Sept Rosado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3703, 20, 'Grossos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3704, 20, 'Guamaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3705, 20, 'Ielmo Marinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3706, 20, 'Ipanguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3707, 20, 'Ipueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3708, 20, 'Itajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3709, 20, 'Itaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3710, 20, 'Jaçanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3711, 20, 'Jandaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3712, 20, 'Janduís')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3713, 20, 'Januário Cicco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3714, 20, 'Japi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3715, 20, 'Jardim de Angicos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3716, 20, 'Jardim de Piranhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3717, 20, 'Jardim do Seridó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3718, 20, 'João Câmara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3719, 20, 'João Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3720, 20, 'José da Penha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3721, 20, 'Jucurutu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3722, 20, 'Lagoa d`Anta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3723, 20, 'Lagoa de Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3724, 20, 'Lagoa de Velhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3725, 20, 'Lagoa Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3726, 20, 'Lagoa Salgada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3727, 20, 'Lajes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3728, 20, 'Lajes Pintadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3729, 20, 'Lucrécia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3730, 20, 'Luís Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3731, 20, 'Macaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3732, 20, 'Macau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3733, 20, 'Major Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3734, 20, 'Marcelino Vieira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3735, 20, 'Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3736, 20, 'Maxaranguape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3737, 20, 'Messias Targino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3738, 20, 'Montanhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3739, 20, 'Monte Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3740, 20, 'Monte das Gameleiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3741, 20, 'Mossoró')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3742, 20, 'Natal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3743, 20, 'Nísia Floresta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3744, 20, 'Nova Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3745, 20, 'Olho-d`Água do Borges')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3746, 20, 'Ouro Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3747, 20, 'Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3748, 20, 'Paraú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3749, 20, 'Parazinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3750, 20, 'Parelhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3751, 20, 'Parnamirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3752, 20, 'Passa e Fica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3753, 20, 'Passagem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3754, 20, 'Patu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3755, 20, 'Pau dos Ferros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3756, 20, 'Pedra Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3757, 20, 'Pedra Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3758, 20, 'Pedro Avelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3759, 20, 'Pedro Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3760, 20, 'Pendências')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3761, 20, 'Pilões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3762, 20, 'Poço Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3763, 20, 'Portalegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3764, 20, 'Porto do Mangue')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3765, 20, 'Presidente Juscelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3766, 20, 'Pureza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3767, 20, 'Rafael Fernandes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3768, 20, 'Rafael Godeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3769, 20, 'Riacho da Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3770, 20, 'Riacho de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3771, 20, 'Riachuelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3772, 20, 'Rio do Fogo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3773, 20, 'Rodolfo Fernandes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3774, 20, 'Ruy Barbosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3775, 20, 'Santa Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3776, 20, 'Santa Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3777, 20, 'Santana do Matos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3778, 20, 'Santana do Seridó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3779, 20, 'Santo Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3780, 20, 'São Bento do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3781, 20, 'São Bento do Trairí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3782, 20, 'São Fernando')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3783, 20, 'São Francisco do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3784, 20, 'São Gonçalo do Amarante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3785, 20, 'São João do Sabugi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3786, 20, 'São José de Mipibu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3787, 20, 'São José do Campestre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3788, 20, 'São José do Seridó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3789, 20, 'São Miguel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3790, 20, 'São Miguel de Touros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3791, 20, 'São Paulo do Potengi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3792, 20, 'São Pedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3793, 20, 'São Rafael')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3794, 20, 'São Tomé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3795, 20, 'São Vicente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3796, 20, 'Senador Elói de Souza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3797, 20, 'Senador Georgino Avelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3798, 20, 'Serra de São Bento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3799, 20, 'Serra do Mel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3800, 20, 'Serra Negra do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3801, 20, 'Serrinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3802, 20, 'Serrinha dos Pintos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3803, 20, 'Severiano Melo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3804, 20, 'Sítio Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3805, 20, 'Taboleiro Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3806, 20, 'Taipu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3807, 20, 'Tangará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3808, 20, 'Tenente Ananias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3809, 20, 'Tenente Laurentino Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3810, 20, 'Tibau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3811, 20, 'Tibau do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3812, 20, 'Timbaúba dos Batistas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3813, 20, 'Touros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3814, 20, 'Triunfo Potiguar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3815, 20, 'Umarizal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3816, 20, 'Upanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3817, 20, 'Várzea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3818, 20, 'Venha-Ver')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3819, 20, 'Vera Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3820, 20, 'Viçosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3821, 20, 'Vila Flor')");


        /* ******************* R i o   G r a n d e   d o   S u l ******************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3822, 21, 'Água Santa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3823, 21, 'Agudo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3824, 21, 'Ajuricaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3825, 21, 'Alecrim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3826, 21, 'Alegrete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3827, 21, 'Alegria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3828, 21, 'Alpestre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3829, 21, 'Alto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3830, 21, 'Alto Feliz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3831, 21, 'Alvorada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3832, 21, 'Amaral Ferrador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3833, 21, 'Ametista do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3834, 21, 'André da Rocha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3835, 21, 'Anta Gorda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3836, 21, 'Antônio Prado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3837, 21, 'Arambaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3838, 21, 'Araricá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3839, 21, 'Aratiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3840, 21, 'Arroio do Meio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3841, 21, 'Arroio do Sal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3842, 21, 'Arroio do Tigre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3843, 21, 'Arroio dos Ratos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3844, 21, 'Arroio Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3845, 21, 'Arvorezinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3846, 21, 'Augusto Pestana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3847, 21, 'Áurea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3848, 21, 'Bagé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3849, 21, 'Balneário Pinhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3850, 21, 'Barão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3851, 21, 'Barão de Cotegipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3852, 21, 'Barão do Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3853, 21, 'Barra do Guarita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3854, 21, 'Barra do Quaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3855, 21, 'Barra do Ribeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3856, 21, 'Barra do Rio Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3857, 21, 'Barra Funda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3858, 21, 'Barracão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3859, 21, 'Barros Cassal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3860, 21, 'Benjamin Constant do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3861, 21, 'Bento Gonçalves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3862, 21, 'Boa Vista das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3863, 21, 'Boa Vista do Buricá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3864, 21, 'Boa Vista do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3865, 21, 'Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3866, 21, 'Bom Princípio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3867, 21, 'Bom Progresso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3868, 21, 'Bom Retiro do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3869, 21, 'Boqueirão do Leão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3870, 21, 'Bossoroca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3871, 21, 'Braga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3872, 21, 'Brochier')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3873, 21, 'Butiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3874, 21, 'Caçapava do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3875, 21, 'Cacequi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3876, 21, 'Cachoeira do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3877, 21, 'Cachoeirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3878, 21, 'Cacique Doble')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3879, 21, 'Caibaté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3880, 21, 'Caiçara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3881, 21, 'Camaquã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3882, 21, 'Camargo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3883, 21, 'Cambará do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3884, 21, 'Campestre da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3885, 21, 'Campina das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3886, 21, 'Campinas do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3887, 21, 'Campo Bom')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3888, 21, 'Campo Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3889, 21, 'Campos Borges')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3890, 21, 'Candelária')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3891, 21, 'Cândido Godói')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3892, 21, 'Candiota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3893, 21, 'Canela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3894, 21, 'Canguçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3895, 21, 'Canoas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3896, 21, 'Capão da Canoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3897, 21, 'Capão do Leão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3898, 21, 'Capela de Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3899, 21, 'Capitão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3900, 21, 'Capivari do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3901, 21, 'Caraá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3902, 21, 'Carazinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3903, 21, 'Carlos Barbosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3904, 21, 'Carlos Gomes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3905, 21, 'Casca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3906, 21, 'Caseiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3907, 21, 'Catuípe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3908, 21, 'Caxias do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3909, 21, 'Centenário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3910, 21, 'Cerrito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3911, 21, 'Cerro Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3912, 21, 'Cerro Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3913, 21, 'Cerro Grande do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3914, 21, 'Cerro Largo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3915, 21, 'Chapada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3916, 21, 'Charqueadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3917, 21, 'Charrua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3918, 21, 'Chiapeta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3919, 21, 'Chuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3920, 21, 'Chuvisca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3921, 21, 'Cidreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3922, 21, 'Ciríaco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3923, 21, 'Colinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3924, 21, 'Colorado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3925, 21, 'Condor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3926, 21, 'Constantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3927, 21, 'Coqueiros do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3928, 21, 'Coronel Barros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3929, 21, 'Coronel Bicaco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3930, 21, 'Cotiporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3931, 21, 'Coxilha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3932, 21, 'Crissiumal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3933, 21, 'Cristal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3934, 21, 'Cristal do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3935, 21, 'Cruz Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3936, 21, 'Cruzeiro do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3937, 21, 'David Canabarro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3938, 21, 'Derrubadas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3939, 21, 'Dezesseis de Novembro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3940, 21, 'Dilermando de Aguiar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3941, 21, 'Dois Irmãos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3942, 21, 'Dois Irmãos das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3943, 21, 'Dois Lajeados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3944, 21, 'Dom Feliciano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3945, 21, 'Dom Pedrito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3946, 21, 'Dom Pedro de Alcântara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3947, 21, 'Dona Francisca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3948, 21, 'Doutor Maurício Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3949, 21, 'Doutor Ricardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3950, 21, 'Eldorado do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3951, 21, 'Encantado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3952, 21, 'Encruzilhada do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3953, 21, 'Engenho Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3954, 21, 'Entre Rios do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3955, 21, 'Entre-Ijuís')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3956, 21, 'Erebango')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3957, 21, 'Erechim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3958, 21, 'Ernestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3959, 21, 'Erval Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3960, 21, 'Erval Seco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3961, 21, 'Esmeralda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3962, 21, 'Esperança do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3963, 21, 'Espumoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3964, 21, 'Estação')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3965, 21, 'Estância Velha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3966, 21, 'Esteio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3967, 21, 'Estrela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3968, 21, 'Estrela Velha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3969, 21, 'Eugênio de Castro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3970, 21, 'Fagundes Varela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3971, 21, 'Farroupilha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3972, 21, 'Faxinal do Soturno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3973, 21, 'Faxinalzinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3974, 21, 'Fazenda Vilanova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3975, 21, 'Feliz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3976, 21, 'Flores da Cunha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3977, 21, 'Floriano Peixoto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3978, 21, 'Fontoura Xavier')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3979, 21, 'Formigueiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3980, 21, 'Fortaleza dos Valos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3981, 21, 'Frederico Westphalen')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3982, 21, 'Garibaldi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3983, 21, 'Garruchos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3984, 21, 'Gaurama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3985, 21, 'General Câmara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3986, 21, 'Gentil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3987, 21, 'Getúlio Vargas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3988, 21, 'Giruá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3989, 21, 'Glorinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3990, 21, 'Gramado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3991, 21, 'Gramado dos Loureiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3992, 21, 'Gramado Xavier')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3993, 21, 'Gravataí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3994, 21, 'Guabiju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3995, 21, 'Guaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3996, 21, 'Guaporé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3997, 21, 'Guarani das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3998, 21, 'Harmonia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (3999, 21, 'Herval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4000, 21, 'Herveiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4001, 21, 'Horizontina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4002, 21, 'Hulha Negra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4003, 21, 'Humaitá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4004, 21, 'Ibarama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4005, 21, 'Ibiaçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4006, 21, 'Ibiraiaras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4007, 21, 'Ibirapuitã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4008, 21, 'Ibirubá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4009, 21, 'Igrejinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4010, 21, 'Ijuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4011, 21, 'Ilópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4012, 21, 'Imbé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4013, 21, 'Imigrante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4014, 21, 'Independência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4015, 21, 'Inhacorá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4016, 21, 'Ipê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4017, 21, 'Ipiranga do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4018, 21, 'Iraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4019, 21, 'Itaara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4020, 21, 'Itacurubi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4021, 21, 'Itapuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4022, 21, 'Itaqui')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4023, 21, 'Itatiba do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4024, 21, 'Ivorá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4025, 21, 'Ivoti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4026, 21, 'Jaboticaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4027, 21, 'Jacutinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4028, 21, 'Jaguarão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4029, 21, 'Jaguari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4030, 21, 'Jaquirana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4031, 21, 'Jari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4032, 21, 'Jóia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4033, 21, 'Júlio de Castilhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4034, 21, 'Lagoa dos Três Cantos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4035, 21, 'Lagoa Vermelha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4036, 21, 'Lagoão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4037, 21, 'Lajeado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4038, 21, 'Lajeado do Bugre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4039, 21, 'Lavras do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4040, 21, 'Liberato Salzano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4041, 21, 'Lindolfo Collor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4042, 21, 'Linha Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4043, 21, 'Maçambara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4044, 21, 'Machadinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4045, 21, 'Mampituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4046, 21, 'Manoel Viana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4047, 21, 'Maquiné')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4048, 21, 'Maratá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4049, 21, 'Marau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4050, 21, 'Marcelino Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4051, 21, 'Mariana Pimentel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4052, 21, 'Mariano Moro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4053, 21, 'Marques de Souza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4054, 21, 'Mata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4055, 21, 'Mato Castelhano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4056, 21, 'Mato Leitão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4057, 21, 'Maximiliano de Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4058, 21, 'Minas do Leão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4059, 21, 'Miraguaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4060, 21, 'Montauri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4061, 21, 'Monte Alegre dos Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4062, 21, 'Monte Belo do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4063, 21, 'Montenegro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4064, 21, 'Mormaço')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4065, 21, 'Morrinhos do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4066, 21, 'Morro Redondo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4067, 21, 'Morro Reuter')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4068, 21, 'Mostardas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4069, 21, 'Muçum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4070, 21, 'Muitos Capões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4071, 21, 'Muliterno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4072, 21, 'Não-Me-Toque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4073, 21, 'Nicolau Vergueiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4074, 21, 'Nonoai')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4075, 21, 'Nova Alvorada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4076, 21, 'Nova Araçá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4077, 21, 'Nova Bassano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4078, 21, 'Nova Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4079, 21, 'Nova Bréscia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4080, 21, 'Nova Candelária')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4081, 21, 'Nova Esperança do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4082, 21, 'Nova Hartz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4083, 21, 'Nova Pádua')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4084, 21, 'Nova Palma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4085, 21, 'Nova Petrópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4086, 21, 'Nova Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4087, 21, 'Nova Ramada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4088, 21, 'Nova Roma do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4089, 21, 'Nova Santa Rita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4090, 21, 'Novo Barreiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4091, 21, 'Novo Cabrais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4092, 21, 'Novo Hamburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4093, 21, 'Novo Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4094, 21, 'Novo Tiradentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4095, 21, 'Osório')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4096, 21, 'Paim Filho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4097, 21, 'Palmares do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4098, 21, 'Palmeira das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4099, 21, 'Palmitinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4100, 21, 'Panambi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4101, 21, 'Pantano Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4102, 21, 'Paraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4103, 21, 'Paraíso do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4104, 21, 'Pareci Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4105, 21, 'Parobé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4106, 21, 'Passa Sete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4107, 21, 'Passo do Sobrado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4108, 21, 'Passo Fundo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4109, 21, 'Paverama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4110, 21, 'Pedro Osório')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4111, 21, 'Pejuçara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4112, 21, 'Pelotas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4113, 21, 'Picada Café')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4114, 21, 'Pinhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4115, 21, 'Pinhal Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4116, 21, 'Pinheirinho do Vale')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4117, 21, 'Pinheiro Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4118, 21, 'Pirapó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4119, 21, 'Piratini')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4120, 21, 'Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4121, 21, 'Poço das Antas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4122, 21, 'Pontão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4123, 21, 'Ponte Preta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4124, 21, 'Portão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4125, 21, 'Porto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4126, 21, 'Porto Lucena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4127, 21, 'Porto Mauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4128, 21, 'Porto Vera Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4129, 21, 'Porto Xavier')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4130, 21, 'Pouso Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4131, 21, 'Presidente Lucena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4132, 21, 'Progresso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4133, 21, 'Protásio Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4134, 21, 'Putinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4135, 21, 'Quaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4136, 21, 'Quevedos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4137, 21, 'Quinze de Novembro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4138, 21, 'Redentora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4139, 21, 'Relvado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4140, 21, 'Restinga Seca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4141, 21, 'Rio dos Índios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4142, 21, 'Rio Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4143, 21, 'Rio Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4144, 21, 'Riozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4145, 21, 'Roca Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4146, 21, 'Rodeio Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4147, 21, 'Rolante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4148, 21, 'Ronda Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4149, 21, 'Rondinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4150, 21, 'Roque Gonzales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4151, 21, 'Rosário do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4152, 21, 'Sagrada Família')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4153, 21, 'Saldanha Marinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4154, 21, 'Salto do Jacuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4155, 21, 'Salvador das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4156, 21, 'Salvador do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4157, 21, 'Sananduva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4158, 21, 'Santa Bárbara do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4159, 21, 'Santa Clara do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4160, 21, 'Santa Cruz do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4161, 21, 'Santa Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4162, 21, 'Santa Maria do Herval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4163, 21, 'Santa Rosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4164, 21, 'Santa Tereza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4165, 21, 'Santa Vitória do Palmar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4166, 21, 'Santana da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4167, 21, 'Santana do Livramento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4168, 21, 'Santiago')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4169, 21, 'Santo Ângelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4170, 21, 'Santo Antônio da Patrulha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4171, 21, 'Santo Antônio das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4172, 21, 'Santo Antônio do Palma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4173, 21, 'Santo Antônio do Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4174, 21, 'Santo Augusto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4175, 21, 'Santo Cristo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4176, 21, 'Santo Expedito do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4177, 21, 'São Borja')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4178, 21, 'São Domingos do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4179, 21, 'São Francisco de Assis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4180, 21, 'São Francisco de Paula')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4181, 21, 'São Gabriel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4182, 21, 'São Jerônimo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4183, 21, 'São João da Urtiga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4184, 21, 'São João do Polêsine')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4185, 21, 'São Jorge')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4186, 21, 'São José das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4187, 21, 'São José do Herval')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4188, 21, 'São José do Hortêncio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4189, 21, 'São José do Inhacorá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4190, 21, 'São José do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4191, 21, 'São José do Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4192, 21, 'São José dos Ausentes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4193, 21, 'São Leopoldo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4194, 21, 'São Lourenço do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4195, 21, 'São Luiz Gonzaga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4196, 21, 'São Marcos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4197, 21, 'São Martinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4198, 21, 'São Martinho da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4199, 21, 'São Miguel das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4200, 21, 'São Nicolau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4201, 21, 'São Paulo das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4202, 21, 'São Pedro da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4203, 21, 'São Pedro do Butiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4204, 21, 'São Pedro do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4205, 21, 'São Sebastião do Caí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4206, 21, 'São Sepé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4207, 21, 'São Valentim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4208, 21, 'São Valentim do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4209, 21, 'São Valério do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4210, 21, 'São Vendelino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4211, 21, 'São Vicente do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4212, 21, 'Sapiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4213, 21, 'Sapucaia do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4214, 21, 'Sarandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4215, 21, 'Seberi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4216, 21, 'Sede Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4217, 21, 'Segredo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4218, 21, 'Selbach')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4219, 21, 'Senador Salgado Filho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4220, 21, 'Sentinela do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4221, 21, 'Serafina Corrêa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4222, 21, 'Sério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4223, 21, 'Sertão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4224, 21, 'Sertão Santana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4225, 21, 'Sete de Setembro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4226, 21, 'Severiano de Almeida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4227, 21, 'Silveira Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4228, 21, 'Sinimbu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4229, 21, 'Sobradinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4230, 21, 'Soledade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4231, 21, 'Tabaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4232, 21, 'Tapejara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4233, 21, 'Tapera')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4234, 21, 'Tapes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4235, 21, 'Taquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4236, 21, 'Taquari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4237, 21, 'Taquaruçu do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4238, 21, 'Tavares')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4239, 21, 'Tenente Portela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4240, 21, 'Terra de Areia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4241, 21, 'Teutônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4242, 21, 'Tiradentes do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4243, 21, 'Toropi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4244, 21, 'Torres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4245, 21, 'Tramandaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4246, 21, 'Travesseiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4247, 21, 'Três Arroios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4248, 21, 'Três Cachoeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4249, 21, 'Três Coroas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4250, 21, 'Três de Maio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4251, 21, 'Três Forquilhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4252, 21, 'Três Palmeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4253, 21, 'Três Passos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4254, 21, 'Trindade do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4255, 21, 'Triunfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4256, 21, 'Tucunduva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4257, 21, 'Tunas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4258, 21, 'Tupanci do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4259, 21, 'Tupanciretã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4260, 21, 'Tupandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4261, 21, 'Tuparendi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4262, 21, 'Turuçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4263, 21, 'Ubiretama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4264, 21, 'União da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4265, 21, 'Unistalda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4266, 21, 'Uruguaiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4267, 21, 'Vacaria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4268, 21, 'Vale do Sol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4269, 21, 'Vale Real')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4270, 21, 'Vale Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4271, 21, 'Vanini')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4272, 21, 'Venâncio Aires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4273, 21, 'Vera Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4274, 21, 'Veranópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4275, 21, 'Vespasiano Correa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4276, 21, 'Viadutos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4277, 21, 'Viamão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4278, 21, 'Vicente Dutra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4279, 21, 'Victor Graeff')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4280, 21, 'Vila Flores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4281, 21, 'Vila Lângaro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4282, 21, 'Vila Maria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4283, 21, 'Vila Nova do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4284, 21, 'Vista Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4285, 21, 'Vista Alegre do Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4286, 21, 'Vista Gaúcha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4287, 21, 'Vitória das Missões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4288, 21, 'Xangri-lá')");


        /* **************************** R o n d ô n i a ***************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4289, 22, 'Alta Floresta d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4290, 22, 'Alto Alegre dos Parecis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4291, 22, 'Alto Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4292, 22, 'Alvorada d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4293, 22, 'Ariquemes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4294, 22, 'Buritis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4295, 22, 'Cabixi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4296, 22, 'Cacaulândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4297, 22, 'Cacoal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4298, 22, 'Campo Novo de Rondônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4299, 22, 'Candeias do Jamari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4300, 22, 'Castanheiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4301, 22, 'Cerejeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4302, 22, 'Chupinguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4303, 22, 'Colorado do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4304, 22, 'Corumbiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4305, 22, 'Costa Marques')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4306, 22, 'Cujubim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4307, 22, 'Espigão d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4308, 22, 'Governador Jorge Teixeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4309, 22, 'Guajará-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4310, 22, 'Itapuã do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4311, 22, 'Jaru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4312, 22, 'Ji-Paraná')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4313, 22, 'Machadinho d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4314, 22, 'Ministro Andreazza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4315, 22, 'Mirante da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4316, 22, 'Monte Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4317, 22, 'Nova Brasilândia d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4318, 22, 'Nova Mamoré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4319, 22, 'Nova União')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4320, 22, 'Novo Horizonte do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4321, 22, 'Ouro Preto do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4322, 22, 'Parecis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4323, 22, 'Pimenta Bueno')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4324, 22, 'Pimenteiras do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4325, 22, 'Porto Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4326, 22, 'Presidente Médici')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4327, 22, 'Primavera de Rondônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4328, 22, 'Rio Crespo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4329, 22, 'Rolim de Moura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4330, 22, 'Santa Luzia d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4331, 22, 'São Felipe d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4332, 22, 'São Francisco do Guaporé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4333, 22, 'São Miguel do Guaporé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4334, 22, 'Seringueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4335, 22, 'Teixeirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4336, 22, 'Theobroma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4337, 22, 'Urupá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4338, 22, 'Vale do Anari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4339, 22, 'Vale do Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4340, 22, 'Vilhena')");


        /* ***************************** R o r a i m a ****************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4341, 23, 'Alto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4342, 23, 'Amajari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4343, 23, 'Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4344, 23, 'Bonfim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4345, 23, 'Cantá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4346, 23, 'Caracaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4347, 23, 'Caroebe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4348, 23, 'Iracema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4349, 23, 'Mucajaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4350, 23, 'Normandia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4351, 23, 'Pacaraima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4352, 23, 'Rorainópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4353, 23, 'São João da Baliza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4354, 23, 'São Luiz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4355, 23, 'Uiramutã')");


        /* ********************** S a n t a   C a t a r i n a *********************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4356, 24, 'Abdon Batista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4357, 24, 'Abelardo Luz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4358, 24, 'Agrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4359, 24, 'Agronômica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4360, 24, 'Água Doce')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4361, 24, 'Águas de Chapecó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4362, 24, 'Águas Frias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4363, 24, 'Águas Mornas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4364, 24, 'Alfredo Wagner')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4365, 24, 'Alto Bela Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4366, 24, 'Anchieta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4367, 24, 'Angelina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4368, 24, 'Anita Garibaldi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4369, 24, 'Anitápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4370, 24, 'Antônio Carlos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4371, 24, 'Apiúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4372, 24, 'Arabutã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4373, 24, 'Araquari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4374, 24, 'Araranguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4375, 24, 'Armazém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4376, 24, 'Arroio Trinta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4377, 24, 'Arvoredo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4378, 24, 'Ascurra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4379, 24, 'Atalanta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4380, 24, 'Aurora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4381, 24, 'Balneário Arroio do Silva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4382, 24, 'Balneário Barra do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4383, 24, 'Balneário Camboriú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4384, 24, 'Balneário Gaivota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4385, 24, 'Bandeirante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4386, 24, 'Barra Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4387, 24, 'Barra Velha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4388, 24, 'Bela Vista do Toldo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4389, 24, 'Belmonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4390, 24, 'Benedito Novo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4391, 24, 'Biguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4392, 24, 'Blumenau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4393, 24, 'Bocaina do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4394, 24, 'Bom Jardim da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4395, 24, 'Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4396, 24, 'Bom Jesus do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4397, 24, 'Bom Retiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4398, 24, 'Bombinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4399, 24, 'Botuverá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4400, 24, 'Braço do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4401, 24, 'Braço do Trombudo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4402, 24, 'Brunópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4403, 24, 'Brusque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4404, 24, 'Caçador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4405, 24, 'Caibi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4406, 24, 'Calmon')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4407, 24, 'Camboriú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4408, 24, 'Campo Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4409, 24, 'Campo Belo do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4410, 24, 'Campo Erê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4411, 24, 'Campos Novos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4412, 24, 'Canelinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4413, 24, 'Canoinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4414, 24, 'Capão Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4415, 24, 'Capinzal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4416, 24, 'Capivari de Baixo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4417, 24, 'Catanduvas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4418, 24, 'Caxambu do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4419, 24, 'Celso Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4420, 24, 'Cerro Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4421, 24, 'Chapadão do Lageado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4422, 24, 'Chapecó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4423, 24, 'Cocal do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4424, 24, 'Concórdia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4425, 24, 'Cordilheira Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4426, 24, 'Coronel Freitas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4427, 24, 'Coronel Martins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4428, 24, 'Correia Pinto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4429, 24, 'Corupá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4430, 24, 'Criciúma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4431, 24, 'Cunha Porã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4432, 24, 'Cunhataí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4433, 24, 'Curitibanos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4434, 24, 'Descanso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4435, 24, 'Dionísio Cerqueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4436, 24, 'Dona Emma')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4437, 24, 'Doutor Pedrinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4438, 24, 'Entre Rios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4439, 24, 'Ermo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4440, 24, 'Erval Velho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4441, 24, 'Faxinal dos Guedes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4442, 24, 'Flor do Sertão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4443, 24, 'Florianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4444, 24, 'Formosa do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4445, 24, 'Forquilhinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4446, 24, 'Fraiburgo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4447, 24, 'Frei Rogério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4448, 24, 'Galvão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4449, 24, 'Garopaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4450, 24, 'Garuva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4451, 24, 'Gaspar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4452, 24, 'Governador Celso Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4453, 24, 'Grão Pará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4454, 24, 'Gravatal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4455, 24, 'Guabiruba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4456, 24, 'Guaraciaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4457, 24, 'Guaramirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4458, 24, 'Guarujá do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4459, 24, 'Guatambú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4460, 24, 'Herval d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4461, 24, 'Ibiam')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4462, 24, 'Ibicaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4463, 24, 'Ibirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4464, 24, 'Içara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4465, 24, 'Ilhota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4466, 24, 'Imaruí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4467, 24, 'Imbituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4468, 24, 'Imbuia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4469, 24, 'Indaial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4470, 24, 'Iomerê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4471, 24, 'Ipira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4472, 24, 'Iporã do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4473, 24, 'Ipuaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4474, 24, 'Ipumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4475, 24, 'Iraceminha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4476, 24, 'Irani')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4477, 24, 'Irati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4478, 24, 'Irineópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4479, 24, 'Itá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4480, 24, 'Itaiópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4481, 24, 'Itajaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4482, 24, 'Itapema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4483, 24, 'Itapiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4484, 24, 'Itapoá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4485, 24, 'Ituporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4486, 24, 'Jaborá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4487, 24, 'Jacinto Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4488, 24, 'Jaguaruna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4489, 24, 'Jaraguá do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4490, 24, 'Jardinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4491, 24, 'Joaçaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4492, 24, 'Joinville')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4493, 24, 'José Boiteux')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4494, 24, 'Jupiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4495, 24, 'Lacerdópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4496, 24, 'Lages')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4497, 24, 'Laguna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4498, 24, 'Lajeado Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4499, 24, 'Laurentino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4500, 24, 'Lauro Muller')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4501, 24, 'Lebon Régis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4502, 24, 'Leoberto Leal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4503, 24, 'Lindóia do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4504, 24, 'Lontras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4505, 24, 'Luiz Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4506, 24, 'Luzerna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4507, 24, 'Macieira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4508, 24, 'Mafra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4509, 24, 'Major Gercino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4510, 24, 'Major Vieira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4511, 24, 'Maracajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4512, 24, 'Maravilha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4513, 24, 'Marema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4514, 24, 'Massaranduba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4515, 24, 'Matos Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4516, 24, 'Meleiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4517, 24, 'Mirim Doce')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4518, 24, 'Modelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4519, 24, 'Mondaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4520, 24, 'Monte Carlo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4521, 24, 'Monte Castelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4522, 24, 'Morro da Fumaça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4523, 24, 'Morro Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4524, 24, 'Navegantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4525, 24, 'Nova Erechim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4526, 24, 'Nova Itaberaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4527, 24, 'Nova Trento')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4528, 24, 'Nova Veneza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4529, 24, 'Novo Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4530, 24, 'Orleans')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4531, 24, 'Otacílio Costa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4532, 24, 'Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4533, 24, 'Ouro Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4534, 24, 'Paial')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4535, 24, 'Painel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4536, 24, 'Palhoça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4537, 24, 'Palma Sola')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4538, 24, 'Palmeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4539, 24, 'Palmitos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4540, 24, 'Papanduva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4541, 24, 'Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4542, 24, 'Passo de Torres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4543, 24, 'Passos Maia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4544, 24, 'Paulo Lopes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4545, 24, 'Pedras Grandes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4546, 24, 'Penha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4547, 24, 'Peritiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4548, 24, 'Petrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4549, 24, 'Piçarras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4550, 24, 'Pinhalzinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4551, 24, 'Pinheiro Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4552, 24, 'Piratuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4553, 24, 'Planalto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4554, 24, 'Pomerode')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4555, 24, 'Ponte Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4556, 24, 'Ponte Alta do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4557, 24, 'Ponte Serrada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4558, 24, 'Porto Belo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4559, 24, 'Porto União')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4560, 24, 'Pouso Redondo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4561, 24, 'Praia Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4562, 24, 'Presidente Castelo Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4563, 24, 'Presidente Getúlio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4564, 24, 'Presidente Nereu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4565, 24, 'Princesa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4566, 24, 'Quilombo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4567, 24, 'Rancho Queimado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4568, 24, 'Rio das Antas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4569, 24, 'Rio do Campo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4570, 24, 'Rio do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4571, 24, 'Rio do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4572, 24, 'Rio dos Cedros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4573, 24, 'Rio Fortuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4574, 24, 'Rio Negrinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4575, 24, 'Rio Rufino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4576, 24, 'Riqueza')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4577, 24, 'Rodeio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4578, 24, 'Romelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4579, 24, 'Salete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4580, 24, 'Saltinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4581, 24, 'Salto Veloso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4582, 24, 'Sangão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4583, 24, 'Santa Cecília')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4584, 24, 'Santa Helena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4585, 24, 'Santa Rosa de Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4586, 24, 'Santa Rosa do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4587, 24, 'Santa Terezinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4588, 24, 'Santa Terezinha do Progresso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4589, 24, 'Santiago do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4590, 24, 'Santo Amaro da Imperatriz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4591, 24, 'São Bento do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4592, 24, 'São Bernardino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4593, 24, 'São Bonifácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4594, 24, 'São Carlos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4595, 24, 'São Cristovão do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4596, 24, 'São Domingos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4597, 24, 'São Francisco do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4598, 24, 'São João Batista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4599, 24, 'São João do Itaperiú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4600, 24, 'São João do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4601, 24, 'São João do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4602, 24, 'São Joaquim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4603, 24, 'São José')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4604, 24, 'São José do Cedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4605, 24, 'São José do Cerrito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4606, 24, 'São Lourenço do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4607, 24, 'São Ludgero')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4608, 24, 'São Martinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4609, 24, 'São Miguel da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4610, 24, 'São Miguel do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4611, 24, 'São Pedro de Alcântara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4612, 24, 'Saudades')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4613, 24, 'Schroeder')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4614, 24, 'Seara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4615, 24, 'Serra Alta')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4616, 24, 'Siderópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4617, 24, 'Sombrio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4618, 24, 'Sul Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4619, 24, 'Taió')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4620, 24, 'Tangará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4621, 24, 'Tigrinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4622, 24, 'Tijucas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4623, 24, 'Timbé do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4624, 24, 'Timbó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4625, 24, 'Timbó Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4626, 24, 'Três Barras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4627, 24, 'Treviso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4628, 24, 'Treze de Maio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4629, 24, 'Treze Tílias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4630, 24, 'Trombudo Central')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4631, 24, 'Tubarão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4632, 24, 'Tunápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4633, 24, 'Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4634, 24, 'União do Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4635, 24, 'Urubici')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4636, 24, 'Urupema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4637, 24, 'Urussanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4638, 24, 'Vargeão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4639, 24, 'Vargem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4640, 24, 'Vargem Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4641, 24, 'Vidal Ramos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4642, 24, 'Videira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4643, 24, 'Vitor Meireles')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4644, 24, 'Witmarsum')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4645, 24, 'Xanxerê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4646, 24, 'Xavantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4647, 24, 'Xaxim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4648, 24, 'Zortéa')");


        /* *************************** S ã o   P a u l o **************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4649, 25, 'Adamantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4650, 25, 'Adolfo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4651, 25, 'Aguaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4652, 25, 'Águas da Prata')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4653, 25, 'Águas de Lindóia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4654, 25, 'Águas de Santa Bárbara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4655, 25, 'Águas de São Pedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4656, 25, 'Agudos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4657, 25, 'Alambari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4658, 25, 'Alfredo Marcondes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4659, 25, 'Altair')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4660, 25, 'Altinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4661, 25, 'Alto Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4662, 25, 'Alumínio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4663, 25, 'Álvares Florence')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4664, 25, 'Álvares Machado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4665, 25, 'Álvaro de Carvalho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4666, 25, 'Alvinlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4667, 25, 'Americana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4668, 25, 'Américo Brasiliense')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4669, 25, 'Américo de Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4670, 25, 'Amparo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4671, 25, 'Analândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4672, 25, 'Andradina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4673, 25, 'Angatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4674, 25, 'Anhembi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4675, 25, 'Anhumas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4676, 25, 'Aparecida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4677, 25, 'Aparecida d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4678, 25, 'Apiaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4679, 25, 'Araçariguama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4680, 25, 'Araçatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4681, 25, 'Araçoiaba da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4682, 25, 'Aramina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4683, 25, 'Arandu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4684, 25, 'Arapeí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4685, 25, 'Araraquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4686, 25, 'Araras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4687, 25, 'Arco-Íris')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4688, 25, 'Arealva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4689, 25, 'Areias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4690, 25, 'Areiópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4691, 25, 'Ariranha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4692, 25, 'Artur Nogueira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4693, 25, 'Arujá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4694, 25, 'Aspásia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4695, 25, 'Assis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4696, 25, 'Atibaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4697, 25, 'Auriflama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4698, 25, 'Avaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4699, 25, 'Avanhandava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4700, 25, 'Avaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4701, 25, 'Bady Bassitt')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4702, 25, 'Balbinos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4703, 25, 'Bálsamo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4704, 25, 'Bananal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4705, 25, 'Barão de Antonina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4706, 25, 'Barbosa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4707, 25, 'Bariri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4708, 25, 'Barra Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4709, 25, 'Barra do Chapéu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4710, 25, 'Barra do Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4711, 25, 'Barretos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4712, 25, 'Barrinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4713, 25, 'Barueri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4714, 25, 'Bastos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4715, 25, 'Batatais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4716, 25, 'Bauru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4717, 25, 'Bebedouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4718, 25, 'Bento de Abreu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4719, 25, 'Bernardino de Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4720, 25, 'Bertioga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4721, 25, 'Bilac')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4722, 25, 'Birigui')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4723, 25, 'Biritiba-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4724, 25, 'Boa Esperança do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4725, 25, 'Bocaina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4726, 25, 'Bofete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4727, 25, 'Boituva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4728, 25, 'Bom Jesus dos Perdões')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4729, 25, 'Bom Sucesso de Itararé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4730, 25, 'Borá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4731, 25, 'Boracéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4732, 25, 'Borborema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4733, 25, 'Borebi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4734, 25, 'Botucatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4735, 25, 'Bragança Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4736, 25, 'Braúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4737, 25, 'Brejo Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4738, 25, 'Brodowski')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4739, 25, 'Brotas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4740, 25, 'Buri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4741, 25, 'Buritama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4742, 25, 'Buritizal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4743, 25, 'Cabrália Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4744, 25, 'Cabreúva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4745, 25, 'Caçapava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4746, 25, 'Cachoeira Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4747, 25, 'Caconde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4748, 25, 'Cafelândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4749, 25, 'Caiabu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4750, 25, 'Caieiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4751, 25, 'Caiuá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4752, 25, 'Cajamar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4753, 25, 'Cajati')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4754, 25, 'Cajobi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4755, 25, 'Cajuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4756, 25, 'Campina do Monte Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4757, 25, 'Campinas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4758, 25, 'Campo Limpo Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4759, 25, 'Campos do Jordão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4760, 25, 'Campos Novos Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4761, 25, 'Cananéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4762, 25, 'Canas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4763, 25, 'Cândido Mota')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4764, 25, 'Cândido Rodrigues')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4765, 25, 'Canitar')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4766, 25, 'Capão Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4767, 25, 'Capela do Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4768, 25, 'Capivari')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4769, 25, 'Caraguatatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4770, 25, 'Carapicuíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4771, 25, 'Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4772, 25, 'Casa Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4773, 25, 'Cássia dos Coqueiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4774, 25, 'Castilho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4775, 25, 'Catanduva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4776, 25, 'Catiguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4777, 25, 'Cedral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4778, 25, 'Cerqueira César')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4779, 25, 'Cerquilho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4780, 25, 'Cesário Lange')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4781, 25, 'Charqueada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4782, 25, 'Chavantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4783, 25, 'Clementina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4784, 25, 'Colina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4785, 25, 'Colômbia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4786, 25, 'Conchal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4787, 25, 'Conchas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4788, 25, 'Cordeirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4789, 25, 'Coroados')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4790, 25, 'Coronel Macedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4791, 25, 'Corumbataí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4792, 25, 'Cosmópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4793, 25, 'Cosmorama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4794, 25, 'Cotia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4795, 25, 'Cravinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4796, 25, 'Cristais Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4797, 25, 'Cruzália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4798, 25, 'Cruzeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4799, 25, 'Cubatão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4800, 25, 'Cunha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4801, 25, 'Descalvado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4802, 25, 'Diadema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4803, 25, 'Dirce Reis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4804, 25, 'Divinolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4805, 25, 'Dobrada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4806, 25, 'Dois Córregos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4807, 25, 'Dolcinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4808, 25, 'Dourado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4809, 25, 'Dracena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4810, 25, 'Duartina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4811, 25, 'Dumont')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4812, 25, 'Echaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4813, 25, 'Eldorado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4814, 25, 'Elias Fausto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4815, 25, 'Elisiário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4816, 25, 'Embaúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4817, 25, 'Embu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4818, 25, 'Embu-Guaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4819, 25, 'Emilianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4820, 25, 'Engenheiro Coelho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4821, 25, 'Espírito Santo do Pinhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4822, 25, 'Espírito Santo do Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4823, 25, 'Estiva Gerbi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4824, 25, 'Estrela d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4825, 25, 'Estrela do Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4826, 25, 'Euclides da Cunha Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4827, 25, 'Fartura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4828, 25, 'Fernando Prestes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4829, 25, 'Fernandópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4830, 25, 'Fernão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4831, 25, 'Ferraz de Vasconcelos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4832, 25, 'Flora Rica')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4833, 25, 'Floreal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4834, 25, 'Florínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4835, 25, 'Flórida Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4836, 25, 'Franca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4837, 25, 'Francisco Morato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4838, 25, 'Franco da Rocha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4839, 25, 'Gabriel Monteiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4840, 25, 'Gália')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4841, 25, 'Garça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4842, 25, 'Gastão Vidigal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4843, 25, 'Gavião Peixoto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4844, 25, 'General Salgado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4845, 25, 'Getulina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4846, 25, 'Glicério')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4847, 25, 'Guaiçara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4848, 25, 'Guaimbê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4849, 25, 'Guaíra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4850, 25, 'Guapiaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4851, 25, 'Guapiara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4852, 25, 'Guará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4853, 25, 'Guaraçaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4854, 25, 'Guaraci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4855, 25, 'Guarani d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4856, 25, 'Guarantã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4857, 25, 'Guararapes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4858, 25, 'Guararema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4859, 25, 'Guaratinguetá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4860, 25, 'Guareí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4861, 25, 'Guariba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4862, 25, 'Guarujá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4863, 25, 'Guarulhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4864, 25, 'Guatapará')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4865, 25, 'Guzolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4866, 25, 'Herculândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4867, 25, 'Holambra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4868, 25, 'Hortolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4869, 25, 'Iacanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4870, 25, 'Iacri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4871, 25, 'Iaras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4872, 25, 'Ibaté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4873, 25, 'Ibirá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4874, 25, 'Ibirarema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4875, 25, 'Ibitinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4876, 25, 'Ibiúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4877, 25, 'Icém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4878, 25, 'Iepê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4879, 25, 'Igaraçu do Tietê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4880, 25, 'Igarapava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4881, 25, 'Igaratá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4882, 25, 'Iguape')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4883, 25, 'Ilha Comprida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4884, 25, 'Ilha Solteira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4885, 25, 'Ilhabela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4886, 25, 'Indaiatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4887, 25, 'Indiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4888, 25, 'Indiaporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4889, 25, 'Inúbia Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4890, 25, 'Ipauçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4891, 25, 'Iperó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4892, 25, 'Ipeúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4893, 25, 'Ipiguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4894, 25, 'Iporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4895, 25, 'Ipuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4896, 25, 'Iracemápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4897, 25, 'Irapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4898, 25, 'Irapuru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4899, 25, 'Itaberá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4900, 25, 'Itaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4901, 25, 'Itajobi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4902, 25, 'Itaju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4903, 25, 'Itanhaém')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4904, 25, 'Itaóca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4905, 25, 'Itapecerica da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4906, 25, 'Itapetininga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4907, 25, 'Itapeva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4908, 25, 'Itapevi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4909, 25, 'Itapira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4910, 25, 'Itapirapuã Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4911, 25, 'Itápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4912, 25, 'Itaporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4913, 25, 'Itapuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4914, 25, 'Itapura')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4915, 25, 'Itaquaquecetuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4916, 25, 'Itararé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4917, 25, 'Itariri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4918, 25, 'Itatiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4919, 25, 'Itatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4920, 25, 'Itirapina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4921, 25, 'Itirapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4922, 25, 'Itobi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4923, 25, 'Itu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4924, 25, 'Itupeva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4925, 25, 'Ituverava')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4926, 25, 'Jaborandi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4927, 25, 'Jaboticabal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4928, 25, 'Jacareí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4929, 25, 'Jaci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4930, 25, 'Jacupiranga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4931, 25, 'Jaguariúna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4932, 25, 'Jales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4933, 25, 'Jambeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4934, 25, 'Jandira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4935, 25, 'Jardinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4936, 25, 'Jarinu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4937, 25, 'Jaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4938, 25, 'Jeriquara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4939, 25, 'Joanópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4940, 25, 'João Ramalho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4941, 25, 'José Bonifácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4942, 25, 'Júlio Mesquita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4943, 25, 'Jumirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4944, 25, 'Jundiaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4945, 25, 'Junqueirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4946, 25, 'Juquiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4947, 25, 'Juquitiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4948, 25, 'Lagoinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4949, 25, 'Laranjal Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4950, 25, 'Lavínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4951, 25, 'Lavrinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4952, 25, 'Leme')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4953, 25, 'Lençóis Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4954, 25, 'Limeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4955, 25, 'Lindóia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4956, 25, 'Lins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4957, 25, 'Lorena')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4958, 25, 'Lourdes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4959, 25, 'Louveira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4960, 25, 'Lucélia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4961, 25, 'Lucianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4962, 25, 'Luís Antônio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4963, 25, 'Luiziânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4964, 25, 'Lupércio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4965, 25, 'Lutécia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4966, 25, 'Macatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4967, 25, 'Macaubal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4968, 25, 'Macedônia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4969, 25, 'Magda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4970, 25, 'Mairinque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4971, 25, 'Mairiporã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4972, 25, 'Manduri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4973, 25, 'Marabá Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4974, 25, 'Maracaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4975, 25, 'Marapoama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4976, 25, 'Mariápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4977, 25, 'Marília')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4978, 25, 'Marinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4979, 25, 'Martinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4980, 25, 'Matão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4981, 25, 'Mauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4982, 25, 'Mendonça')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4983, 25, 'Meridiano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4984, 25, 'Mesópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4985, 25, 'Miguelópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4986, 25, 'Mineiros do Tietê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4987, 25, 'Mira Estrela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4988, 25, 'Miracatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4989, 25, 'Mirandópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4990, 25, 'Mirante do Paranapanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4991, 25, 'Mirassol')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4992, 25, 'Mirassolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4993, 25, 'Mococa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4994, 25, 'Mogi das Cruzes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4995, 25, 'Mogi Guaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4996, 25, 'Mogi-Mirim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4997, 25, 'Mombuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4998, 25, 'Monções')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (4999, 25, 'Mongaguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5000, 25, 'Monte Alegre do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5001, 25, 'Monte Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5002, 25, 'Monte Aprazível')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5003, 25, 'Monte Azul Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5004, 25, 'Monte Castelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5005, 25, 'Monte Mor')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5006, 25, 'Monteiro Lobato')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5007, 25, 'Morro Agudo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5008, 25, 'Morungaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5009, 25, 'Motuca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5010, 25, 'Murutinga do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5011, 25, 'Nantes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5012, 25, 'Narandiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5013, 25, 'Natividade da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5014, 25, 'Nazaré Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5015, 25, 'Neves Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5016, 25, 'Nhandeara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5017, 25, 'Nipoã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5018, 25, 'Nova Aliança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5019, 25, 'Nova Campina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5020, 25, 'Nova Canaã Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5021, 25, 'Nova Castilho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5022, 25, 'Nova Europa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5023, 25, 'Nova Granada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5024, 25, 'Nova Guataporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5025, 25, 'Nova Independência')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5026, 25, 'Nova Luzitânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5027, 25, 'Nova Odessa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5028, 25, 'Novais')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5029, 25, 'Novo Horizonte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5030, 25, 'Nuporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5031, 25, 'Ocauçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5032, 25, 'Óleo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5033, 25, 'Olímpia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5034, 25, 'Onda Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5035, 25, 'Oriente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5036, 25, 'Orindiúva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5037, 25, 'Orlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5038, 25, 'Osasco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5039, 25, 'Oscar Bressane')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5040, 25, 'Osvaldo Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5041, 25, 'Ourinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5042, 25, 'Ouro Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5043, 25, 'Ouroeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5044, 25, 'Pacaembu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5045, 25, 'Palestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5046, 25, 'Palmares Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5047, 25, 'Palmeira d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5048, 25, 'Palmital')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5049, 25, 'Panorama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5050, 25, 'Paraguaçu Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5051, 25, 'Paraibuna')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5052, 25, 'Paraíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5053, 25, 'Paranapanema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5054, 25, 'Paranapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5055, 25, 'Parapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5056, 25, 'Pardinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5057, 25, 'Pariquera-Açu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5058, 25, 'Parisi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5059, 25, 'Patrocínio Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5060, 25, 'Paulicéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5061, 25, 'Paulínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5062, 25, 'Paulistânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5063, 25, 'Paulo de Faria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5064, 25, 'Pederneiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5065, 25, 'Pedra Bela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5066, 25, 'Pedranópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5067, 25, 'Pedregulho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5068, 25, 'Pedreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5069, 25, 'Pedrinhas Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5070, 25, 'Pedro de Toledo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5071, 25, 'Penápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5072, 25, 'Pereira Barreto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5073, 25, 'Pereiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5074, 25, 'Peruíbe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5075, 25, 'Piacatu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5076, 25, 'Piedade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5077, 25, 'Pilar do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5078, 25, 'Pindamonhangaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5079, 25, 'Pindorama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5080, 25, 'Pinhalzinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5081, 25, 'Piquerobi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5082, 25, 'Piquete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5083, 25, 'Piracaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5084, 25, 'Piracicaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5085, 25, 'Piraju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5086, 25, 'Pirajuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5087, 25, 'Pirangi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5088, 25, 'Pirapora do Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5089, 25, 'Pirapozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5090, 25, 'Pirassununga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5091, 25, 'Piratininga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5092, 25, 'Pitangueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5093, 25, 'Planalto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5094, 25, 'Platina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5095, 25, 'Poá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5096, 25, 'Poloni')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5097, 25, 'Pompéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5098, 25, 'Pongaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5099, 25, 'Pontal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5100, 25, 'Pontalinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5101, 25, 'Pontes Gestal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5102, 25, 'Populina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5103, 25, 'Porangaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5104, 25, 'Porto Feliz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5105, 25, 'Porto Ferreira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5106, 25, 'Potim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5107, 25, 'Potirendaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5108, 25, 'Pracinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5109, 25, 'Pradópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5110, 25, 'Praia Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5111, 25, 'Pratânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5112, 25, 'Presidente Alves')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5113, 25, 'Presidente Bernardes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5114, 25, 'Presidente Epitácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5115, 25, 'Presidente Prudente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5116, 25, 'Presidente Venceslau')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5117, 25, 'Promissão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5118, 25, 'Quadra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5119, 25, 'Quatá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5120, 25, 'Queiroz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5121, 25, 'Queluz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5122, 25, 'Quintana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5123, 25, 'Rafard')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5124, 25, 'Rancharia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5125, 25, 'Redenção da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5126, 25, 'Regente Feijó')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5127, 25, 'Reginópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5128, 25, 'Registro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5129, 25, 'Restinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5130, 25, 'Ribeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5131, 25, 'Ribeirão Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5132, 25, 'Ribeirão Branco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5133, 25, 'Ribeirão Corrente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5134, 25, 'Ribeirão do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5135, 25, 'Ribeirão dos Índios')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5136, 25, 'Ribeirão Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5137, 25, 'Ribeirão Pires')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5138, 25, 'Ribeirão Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5139, 25, 'Rifaina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5140, 25, 'Rincão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5141, 25, 'Rinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5142, 25, 'Rio Claro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5143, 25, 'Rio das Pedras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5144, 25, 'Rio Grande da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5145, 25, 'Riolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5146, 25, 'Riversul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5147, 25, 'Rosana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5148, 25, 'Roseira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5149, 25, 'Rubiácea')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5150, 25, 'Rubinéia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5151, 25, 'Sabino')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5152, 25, 'Sagres')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5153, 25, 'Sales')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5154, 25, 'Sales Oliveira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5155, 25, 'Salesópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5156, 25, 'Salmourão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5157, 25, 'Saltinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5158, 25, 'Salto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5159, 25, 'Salto de Pirapora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5160, 25, 'Salto Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5161, 25, 'Sandovalina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5162, 25, 'Santa Adélia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5163, 25, 'Santa Albertina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5164, 25, 'Santa Bárbara d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5165, 25, 'Santa Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5166, 25, 'Santa Clara d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5167, 25, 'Santa Cruz da Conceição')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5168, 25, 'Santa Cruz da Esperança')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5169, 25, 'Santa Cruz das Palmeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5170, 25, 'Santa Cruz do Rio Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5171, 25, 'Santa Ernestina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5172, 25, 'Santa Fé do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5173, 25, 'Santa Gertrudes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5174, 25, 'Santa Isabel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5175, 25, 'Santa Lúcia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5176, 25, 'Santa Maria da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5177, 25, 'Santa Mercedes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5178, 25, 'Santa Rita d`Oeste')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5179, 25, 'Santa Rita do Passa Quatro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5180, 25, 'Santa Rosa de Viterbo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5181, 25, 'Santa Salete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5182, 25, 'Santana da Ponte Pensa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5183, 25, 'Santana de Parnaíba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5184, 25, 'Santo Anastácio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5185, 25, 'Santo André')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5186, 25, 'Santo Antônio da Alegria')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5187, 25, 'Santo Antônio de Posse')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5188, 25, 'Santo Antônio do Aracanguá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5189, 25, 'Santo Antônio do Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5190, 25, 'Santo Antônio do Pinhal')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5191, 25, 'Santo Expedito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5192, 25, 'Santópolis do Aguapeí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5193, 25, 'Santos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5194, 25, 'São Bento do Sapucaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5195, 25, 'São Bernardo do Campo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5196, 25, 'São Caetano do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5197, 25, 'São Carlos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5198, 25, 'São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5199, 25, 'São João da Boa Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5200, 25, 'São João das Duas Pontes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5201, 25, 'São João de Iracema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5202, 25, 'São João do Pau d`Alho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5203, 25, 'São Joaquim da Barra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5204, 25, 'São José da Bela Vista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5205, 25, 'São José do Barreiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5206, 25, 'São José do Rio Pardo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5207, 25, 'São José do Rio Preto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5208, 25, 'São José dos Campos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5209, 25, 'São Lourenço da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5210, 25, 'São Luís do Paraitinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5211, 25, 'São Manuel')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5212, 25, 'São Miguel Arcanjo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5213, 25, 'São Paulo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5214, 25, 'São Pedro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5215, 25, 'São Pedro do Turvo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5216, 25, 'São Roque')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5217, 25, 'São Sebastião')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5218, 25, 'São Sebastião da Grama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5219, 25, 'São Simão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5220, 25, 'São Vicente')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5221, 25, 'Sarapuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5222, 25, 'Sarutaiá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5223, 25, 'Sebastianópolis do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5224, 25, 'Serra Azul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5225, 25, 'Serra Negra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5226, 25, 'Serrana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5227, 25, 'Sertãozinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5228, 25, 'Sete Barras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5229, 25, 'Severínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5230, 25, 'Silveiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5231, 25, 'Socorro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5232, 25, 'Sorocaba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5233, 25, 'Sud Mennucci')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5234, 25, 'Sumaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5235, 25, 'Suzanápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5236, 25, 'Suzano')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5237, 25, 'Tabapuã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5238, 25, 'Tabatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5239, 25, 'Taboão da Serra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5240, 25, 'Taciba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5241, 25, 'Taguaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5242, 25, 'Taiaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5243, 25, 'Taiúva')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5244, 25, 'Tambaú')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5245, 25, 'Tanabi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5246, 25, 'Tapiraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5247, 25, 'Tapiratiba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5248, 25, 'Taquaral')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5249, 25, 'Taquaritinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5250, 25, 'Taquarituba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5251, 25, 'Taquarivaí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5252, 25, 'Tarabai')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5253, 25, 'Tarumã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5254, 25, 'Tatuí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5255, 25, 'Taubaté')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5256, 25, 'Tejupá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5257, 25, 'Teodoro Sampaio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5258, 25, 'Terra Roxa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5259, 25, 'Tietê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5260, 25, 'Timburi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5261, 25, 'Torre de Pedra')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5262, 25, 'Torrinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5263, 25, 'Trabiju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5264, 25, 'Tremembé')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5265, 25, 'Três Fronteiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5266, 25, 'Tuiuti')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5267, 25, 'Tupã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5268, 25, 'Tupi Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5269, 25, 'Turiúba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5270, 25, 'Turmalina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5271, 25, 'Ubarana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5272, 25, 'Ubatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5273, 25, 'Ubirajara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5274, 25, 'Uchoa')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5275, 25, 'União Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5276, 25, 'Urânia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5277, 25, 'Uru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5278, 25, 'Urupês')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5279, 25, 'Valentim Gentil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5280, 25, 'Valinhos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5281, 25, 'Valparaíso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5282, 25, 'Vargem')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5283, 25, 'Vargem Grande do Sul')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5284, 25, 'Vargem Grande Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5285, 25, 'Várzea Paulista')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5286, 25, 'Vera Cruz')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5287, 25, 'Vinhedo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5288, 25, 'Viradouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5289, 25, 'Vista Alegre do Alto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5290, 25, 'Vitória Brasil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5291, 25, 'Votorantim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5292, 25, 'Votuporanga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5293, 25, 'Zacarias')");


        /* ***************************** S e r g i p e ****************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5294, 26, 'Amparo de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5295, 26, 'Aquidabã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5296, 26, 'Aracaju')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5297, 26, 'Arauá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5298, 26, 'Areia Branca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5299, 26, 'Barra dos Coqueiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5300, 26, 'Boquim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5301, 26, 'Brejo Grande')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5302, 26, 'Campo do Brito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5303, 26, 'Canhoba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5304, 26, 'Canindé de São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5305, 26, 'Capela')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5306, 26, 'Carira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5307, 26, 'Carmópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5308, 26, 'Cedro de São João')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5309, 26, 'Cristinápolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5310, 26, 'Cumbe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5311, 26, 'Divina Pastora')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5312, 26, 'Estância')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5313, 26, 'Feira Nova')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5314, 26, 'Frei Paulo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5315, 26, 'Gararu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5316, 26, 'General Maynard')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5317, 26, 'Gracho Cardoso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5318, 26, 'Ilha das Flores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5319, 26, 'Indiaroba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5320, 26, 'Itabaiana')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5321, 26, 'Itabaianinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5322, 26, 'Itabi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5323, 26, 'Itaporanga d`Ajuda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5324, 26, 'Japaratuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5325, 26, 'Japoatã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5326, 26, 'Lagarto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5327, 26, 'Laranjeiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5328, 26, 'Macambira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5329, 26, 'Malhada dos Bois')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5330, 26, 'Malhador')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5331, 26, 'Maruim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5332, 26, 'Moita Bonita')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5333, 26, 'Monte Alegre de Sergipe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5334, 26, 'Muribeca')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5335, 26, 'Neópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5336, 26, 'Nossa Senhora Aparecida')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5337, 26, 'Nossa Senhora da Glória')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5338, 26, 'Nossa Senhora das Dores')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5339, 26, 'Nossa Senhora de Lourdes')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5340, 26, 'Nossa Senhora do Socorro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5341, 26, 'Pacatuba')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5342, 26, 'Pedra Mole')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5343, 26, 'Pedrinhas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5344, 26, 'Pinhão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5345, 26, 'Pirambu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5346, 26, 'Poço Redondo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5347, 26, 'Poço Verde')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5348, 26, 'Porto da Folha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5349, 26, 'Propriá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5350, 26, 'Riachão do Dantas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5351, 26, 'Riachuelo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5352, 26, 'Ribeirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5353, 26, 'Rosário do Catete')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5354, 26, 'Salgado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5355, 26, 'Santa Luzia do Itanhy')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5356, 26, 'Santa Rosa de Lima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5357, 26, 'Santana do São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5358, 26, 'Santo Amaro das Brotas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5359, 26, 'São Cristóvão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5360, 26, 'São Domingos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5361, 26, 'São Francisco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5362, 26, 'São Miguel do Aleixo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5363, 26, 'Simão Dias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5364, 26, 'Siriri')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5365, 26, 'Telha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5366, 26, 'Tobias Barreto')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5367, 26, 'Tomar do Geru')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5368, 26, 'Umbaúba')");


        /* *************************** T o c a n t i n s **************************** */

        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5369, 27, 'Abreulândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5370, 27, 'Aguiarnópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5371, 27, 'Aliança do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5372, 27, 'Almas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5373, 27, 'Alvorada')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5374, 27, 'Ananás')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5375, 27, 'Angico')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5376, 27, 'Aparecida do Rio Negro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5377, 27, 'Aragominas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5378, 27, 'Araguacema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5379, 27, 'Araguaçu')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5380, 27, 'Araguaína')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5381, 27, 'Araguanã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5382, 27, 'Araguatins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5383, 27, 'Arapoema')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5384, 27, 'Arraias')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5385, 27, 'Augustinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5386, 27, 'Aurora do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5387, 27, 'Axixá do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5388, 27, 'Babaçulândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5389, 27, 'Bandeirantes do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5390, 27, 'Barra do Ouro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5391, 27, 'Barrolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5392, 27, 'Bernardo Sayão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5393, 27, 'Bom Jesus do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5394, 27, 'Brasilândia do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5395, 27, 'Brejinho de Nazaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5396, 27, 'Buriti do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5397, 27, 'Cachoeirinha')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5398, 27, 'Campos Lindos')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5399, 27, 'Cariri do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5400, 27, 'Carmolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5401, 27, 'Carrasco Bonito')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5402, 27, 'Caseara')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5403, 27, 'Centenário')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5404, 27, 'Chapada da Natividade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5405, 27, 'Chapada de Areia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5406, 27, 'Colinas do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5407, 27, 'Colméia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5408, 27, 'Combinado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5409, 27, 'Conceição do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5410, 27, 'Couto de Magalhães')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5411, 27, 'Cristalândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5412, 27, 'Crixás do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5413, 27, 'Darcinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5414, 27, 'Dianópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5415, 27, 'Divinópolis do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5416, 27, 'Dois Irmãos do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5417, 27, 'Dueré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5418, 27, 'Esperantina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5419, 27, 'Fátima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5420, 27, 'Figueirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5421, 27, 'Filadélfia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5422, 27, 'Formoso do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5423, 27, 'Fortaleza do Tabocão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5424, 27, 'Goianorte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5425, 27, 'Goiatins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5426, 27, 'Guaraí')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5427, 27, 'Gurupi')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5428, 27, 'Ipueiras')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5429, 27, 'Itacajá')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5430, 27, 'Itaguatins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5431, 27, 'Itapiratins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5432, 27, 'Itaporã do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5433, 27, 'Jaú do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5434, 27, 'Juarina')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5435, 27, 'Lagoa da Confusão')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5436, 27, 'Lagoa do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5437, 27, 'Lajeado')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5438, 27, 'Lavandeira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5439, 27, 'Lizarda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5440, 27, 'Luzinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5441, 27, 'Marianópolis do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5442, 27, 'Mateiros')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5443, 27, 'Maurilândia do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5444, 27, 'Miracema do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5445, 27, 'Miranorte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5446, 27, 'Monte do Carmo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5447, 27, 'Monte Santo do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5448, 27, 'Muricilândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5449, 27, 'Natividade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5450, 27, 'Nazaré')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5451, 27, 'Nova Olinda')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5452, 27, 'Nova Rosalândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5453, 27, 'Novo Acordo')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5454, 27, 'Novo Alegre')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5455, 27, 'Novo Jardim')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5456, 27, 'Oliveira de Fátima')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5457, 27, 'Palmas')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5458, 27, 'Palmeirante')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5459, 27, 'Palmeiras do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5460, 27, 'Palmeirópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5461, 27, 'Paraíso do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5462, 27, 'Paranã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5463, 27, 'Pau d`Arco')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5464, 27, 'Pedro Afonso')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5465, 27, 'Peixe')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5466, 27, 'Pequizeiro')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5467, 27, 'Pindorama do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5468, 27, 'Piraquê')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5469, 27, 'Pium')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5470, 27, 'Ponte Alta do Bom Jesus')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5471, 27, 'Ponte Alta do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5472, 27, 'Porto Alegre do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5473, 27, 'Porto Nacional')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5474, 27, 'Praia Norte')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5475, 27, 'Presidente Kennedy')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5476, 27, 'Pugmil')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5477, 27, 'Recursolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5478, 27, 'Riachinho')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5479, 27, 'Rio da Conceição')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5480, 27, 'Rio dos Bois')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5481, 27, 'Rio Sono')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5482, 27, 'Sampaio')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5483, 27, 'Sandolândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5484, 27, 'Santa Fé do Araguaia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5485, 27, 'Santa Maria do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5486, 27, 'Santa Rita do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5487, 27, 'Santa Rosa do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5488, 27, 'Santa Tereza do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5489, 27, 'Santa Terezinha do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5490, 27, 'São Bento do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5491, 27, 'São Félix do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5492, 27, 'São Miguel do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5493, 27, 'São Salvador do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5494, 27, 'São Sebastião do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5495, 27, 'São Valério da Natividade')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5496, 27, 'Silvanópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5497, 27, 'Sítio Novo do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5498, 27, 'Sucupira')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5499, 27, 'Taguatinga')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5500, 27, 'Taipas do Tocantins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5501, 27, 'Talismã')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5502, 27, 'Tocantínia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5503, 27, 'Tocantinópolis')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5504, 27, 'Tupirama')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5505, 27, 'Tupiratins')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5506, 27, 'Wanderlândia')");
        $conn->executeQuery("INSERT INTO city (id, state_id, name) VALUES (5507, 27, 'Xambioá')");
    }

] + $updates ;
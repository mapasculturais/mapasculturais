<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;

/** @var Connection $conn */
$conn = $em->getConnection();


function __table_exists($table_name) {
    $app = App::i();
    $em = $app->em;
    /** @var Connection $conn */
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
    /** @var Connection $conn */
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
    /** @var Connection $conn */
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
    /** @var Connection $conn */
    $conn = $em->getConnection();

    try{
        $conn->executeQuery($sql);
    } catch (\Exception $ex) {
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
    // SCHEME CHANGES =========================================

    'create object_type enum type' => function () {
        $object_types = implode(',', array_map(function($el) {
            return "'$el'";
        }, DoctrineEnumTypes\ObjectType::values()));

        __exec("CREATE TYPE object_type AS ENUM($object_types)");
    }, 

    'UPDATING ENUM TYPES' => function() use($conn) {
        $reg = \Acelaya\Doctrine\Type\PhpEnumType::getTypeRegistry();
        
        foreach ($reg->getMap() as $enum_type => $type){
            if(get_class($type) == 'Acelaya\Doctrine\Type\PhpEnumType') {
                $values = $conn->fetchAll("
                    SELECT e.enumlabel AS value
                    FROM pg_type t 
                        JOIN pg_enum e ON t.oid = e.enumtypid  
                        JOIN pg_catalog.pg_namespace n ON n.oid = t.typnamespace 
                    WHERE t.typname = '{$enum_type}'");

                $actual_values = array_map(function($item) { return $item['value']; }, $values);
                
                $reflection = new \ReflectionObject($type);
                $property = $reflection->getProperty('enumClass');
                $property->setAccessible (true);
                $class = $property->getValue($type);
                
                foreach($class::toArray() as $value){
                    if(!in_array($value, $actual_values)) {
                        echo "\n- ALTER TYPE {$enum_type} ADD VALUE '$value'\n";
                        __exec("ALTER TYPE {$enum_type} ADD VALUE '$value'");
                    }
                }
            }
        }

        return false;
    },

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


    //Space_Relation
    'CREATE SEQUENCE REGISTRATION SPACE RELATION registration_space_relation_id_seq' => function() use($conn){
        $conn->executeQuery("CREATE SEQUENCE space_relation_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
    },

    'CREATE TABLE spacerelation' => function() use($conn){
        $conn->executeQuery("CREATE TABLE space_relation (id INT NOT NULL, space_id INT DEFAULT NULL, object_id INT NOT NULL, 
                             create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status SMALLINT DEFAULT NULL, 
                             object_type VARCHAR(255) NOT NULL, PRIMARY KEY(id));");

        $conn->executeQuery("CREATE INDEX IDX_1A0E9A3023575340 ON space_relation (space_id);");
        $conn->executeQuery("CREATE INDEX IDX_1A0E9A30232D562B ON space_relation (object_id);");
        $conn->executeQuery("ALTER TABLE space_relation ADD CONSTRAINT FK_1A0E9A3023575340 FOREIGN KEY (space_id) REFERENCES space (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        $conn->executeQuery("ALTER TABLE space_relation ADD CONSTRAINT FK_1A0E9A30232D562B FOREIGN KEY (object_id) REFERENCES registration (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
    },

    //Adiciona coluna space_data com metadados do espaço vinculado à inscrição
    'ALTER TABLE registration' => function() use($conn){
        $conn->executeQuery("ALTER TABLE registration ADD space_data text DEFAULT NULL;");
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


    'ALTER TABLE file ADD private and update' => function () use ($conn) {
        if(__column_exists('file', 'private')){
            return true;
        }

        $conn->executeQuery("ALTER TABLE file ADD private BOOLEAN NOT NULL DEFAULT FALSE;");
        
        $conn->executeQuery("UPDATE file SET private = true WHERE grp LIKE 'rfc_%' OR grp = 'zipArchive'");
        
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

    'alter table registration add column valuers_exceptions_list' => function() use($conn){
        if(!__column_exists('registration', 'valuers_exceptions_list')){
            $conn->executeQuery("ALTER TABLE registration ADD valuers_exceptions_list TEXT NOT NULL DEFAULT '{\"include\": [], \"exclude\": []}';");
        }
    },

    'create event attendance table' => function() use($conn) {
        if(!__table_exists('event_attendance')){
            $conn->executeQuery("
                CREATE TABLE event_attendance (
                    id INT NOT NULL, 
                    user_id INT NOT NULL, 
                    event_occurrence_id INT NOT NULL, 
                    event_id INT NOT NULL, 
                    space_id INT NOT NULL, 
                    type VARCHAR(255) NOT NULL, 
                    reccurrence_string TEXT DEFAULT NULL, 
                    start_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    end_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    PRIMARY KEY(id));");

            $conn->executeQuery("CREATE INDEX IDX_350DD4BEA76ED395 ON event_attendance (user_id);");
            $conn->executeQuery("CREATE INDEX IDX_350DD4BE140E9F00 ON event_attendance (event_occurrence_id);");
            $conn->executeQuery("CREATE INDEX IDX_350DD4BE71F7E88B ON event_attendance (event_id);");
            $conn->executeQuery("CREATE INDEX IDX_350DD4BE23575340 ON event_attendance (space_id);");
            $conn->executeQuery("CREATE INDEX event_attendance_type_idx ON event_attendance (type);");
            $conn->executeQuery("CREATE SEQUENCE event_attendance_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
            $conn->executeQuery("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BEA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE140E9F00 FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE23575340 FOREIGN KEY (space_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        }
    },

    'create procuration table' => function() use($conn) {
        if(!__table_exists('procuration')){
            $conn->executeQuery("
                CREATE TABLE procuration (
                    token VARCHAR(32) NOT NULL, 
                    usr_id INT NOT NULL, 
                    attorney_user_id INT NOT NULL, 
                    action VARCHAR(255) NOT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    valid_until_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(token));");
                
            $conn->executeQuery("CREATE INDEX procuration_usr_idx ON procuration (usr_id);");
            $conn->executeQuery("CREATE INDEX procuration_attorney_idx ON procuration (attorney_user_id);");
            $conn->executeQuery("ALTER TABLE procuration ADD CONSTRAINT FK_D7BAE7FC69D3FB FOREIGN KEY (usr_id) REFERENCES usr (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            $conn->executeQuery("ALTER TABLE procuration ADD CONSTRAINT FK_D7BAE7F3AEB2ED7 FOREIGN KEY (attorney_user_id) REFERENCES usr (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
            
        }
    },

    'alter table registration_field_configuration add column config' => function() use($conn){
        if(!__column_exists('registration_field_configuration', 'config')){
            __exec("
                ALTER TABLE registration_field_configuration 
                ADD config TEXT;
            ");
        }
    },

    'create permission_action enum type' => function () {
        $permission_actions = implode(',', array_map(function($el) {
            return "'$el'";
        }, DoctrineEnumTypes\PermissionAction::values()));

        __exec("CREATE TYPE permission_action AS ENUM($permission_actions)");
    }, 

    'alter tables to use enum types' => function() {
        __exec("ALTER TABLE pcache ALTER COLUMN object_type TYPE object_type USING object_type::object_type");
        __exec("ALTER TABLE pcache ALTER COLUMN action TYPE permission_action USING action::permission_action");

        __exec("ALTER TABLE file ALTER COLUMN object_type TYPE object_type USING object_type::object_type");
        __exec("ALTER TABLE agent_relation ALTER COLUMN object_type TYPE object_type USING object_type::object_type");
        __exec("ALTER TABLE term_relation ALTER COLUMN object_type TYPE object_type USING object_type::object_type");
        __exec("ALTER TABLE entity_revision ALTER COLUMN object_type TYPE object_type USING object_type::object_type");
        __exec("ALTER TABLE metadata ALTER COLUMN object_type TYPE object_type USING object_type::object_type");

    },

    'alter table permission_cache_pending add column status' => function() use($conn) {
        if (!__column_exists('permission_cache_pending', 'status')) {
            $conn->executeQuery("ALTER TABLE permission_cache_pending ADD status smallint DEFAULT 0");
        }
    },

    'ALTER TABLE metalist ALTER value TYPE TEXT' => function () {
        __exec("ALTER TABLE metalist ALTER value TYPE TEXT;");
    },

    'Add metadata to Agent Relation' => function () use($conn) {
        if(__column_exists('agent_relation', 'metadata')){
            return true;
        }
        $conn->executeQuery("ALTER TABLE agent_relation ADD COLUMN metadata json;");
    },

    'add timestamp columns to registration_evaluation' => function () {
        if (__column_exists('registration_evaluation', 'create_timestamp') &&
            __column_exists('registration_evaluation', 'update_timestamp')) {
            echo "ALREADY APPLIED";
            return true;
        }
        __exec("ALTER TABLE registration_evaluation ADD create_timestamp TIMESTAMP DEFAULT NOW() NOT NULL;");
        __exec("ALTER TABLE registration_evaluation ADD update_timestamp TIMESTAMP DEFAULT NULL;");
        return true;
    },

    'create chat tables' => function () {
        if (!__sequence_exists("chat_thread_id_seq")) {
            __exec("CREATE SEQUENCE chat_thread_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        }
        if (!__table_exists("chat_thread")) {
            __exec("CREATE TABLE chat_thread (
                id INT NOT NULL,
                object_id INT NOT NULL,
                object_type VARCHAR(255) NOT NULL,
                type VARCHAR(255) NOT NULL,
                identifier VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                last_message_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                status INT NOT NULL,
                PRIMARY KEY(id))");
            __exec("COMMENT ON COLUMN chat_thread.object_type IS '(DC2Type:object_type)'");
        }
        if (!__sequence_exists("chat_message_id_seq")) {
            __exec("CREATE SEQUENCE chat_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1");
        }
        if (!__table_exists("chat_message")) {
            __exec("CREATE TABLE chat_message (
                id INT NOT NULL,
                chat_thread_id INT NOT NULL,
                parent_id INT DEFAULT NULL,
                user_id INT NOT NULL,
                payload TEXT NOT NULL,
                create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id))");
            __exec("CREATE INDEX IDX_FAB3FC16C47D5262 ON chat_message (chat_thread_id)");
            __exec("CREATE INDEX IDX_FAB3FC16727ACA70 ON chat_message (parent_id)");
            __exec("CREATE INDEX IDX_FAB3FC16A76ED395 ON chat_message (user_id)");
            __exec("ALTER TABLE chat_message ADD
                CONSTRAINT FK_FAB3FC16C47D5262
                FOREIGN KEY (chat_thread_id) REFERENCES chat_thread (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
            __exec("ALTER TABLE chat_message ADD
                CONSTRAINT FK_FAB3FC16727ACA70
                FOREIGN KEY (parent_id) REFERENCES chat_message (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
            __exec("ALTER TABLE chat_message ADD
                CONSTRAINT FK_FAB3FC16A76ED395
                FOREIGN KEY (user_id) REFERENCES usr (id)
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE");
        }
    },    

    'create table job' => function () use($conn) {
        __exec("CREATE TABLE job (
                    id VARCHAR(255) NOT NULL, 
                    name VARCHAR(32) NOT NULL, 
                    iterations INT NOT NULL, 
                    iterations_count INT NOT NULL, 
                    interval_string VARCHAR(255) NOT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                    next_execution_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                    last_execution_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    metadata JSON NOT NULL, 
                    status SMALLINT NOT NULL, 
                    PRIMARY KEY(id)
                );");


        __exec("COMMENT ON COLUMN job.metadata IS '(DC2Type:json_array)';");

        __exec("CREATE INDEX job_next_execution_timestamp_idx ON job (next_execution_timestamp);");
        __exec("CREATE INDEX job_search_idx ON job (next_execution_timestamp, iterations_count, status);");
    },

    'alter job.metadata comment' => function () {
        __exec("COMMENT ON COLUMN job.metadata IS '(DC2Type:json)';");
    },
    "Adiciona coluna avaliableEvaluationFields na tabela opportunity" => function() use ($conn){
        if(!__column_exists('opportunity', 'avaliable_evaluation_fields')) {
            __exec("ALTER TABLE opportunity ADD avaliable_evaluation_fields JSON DEFAULT NULL;");
        }
    },
    "Adiciona coluna publish_timestamp na tabela opportunity" => function() use ($conn){
        if(!__column_exists('opportunity', 'publish_timestamp')) {
            __exec("ALTER TABLE opportunity ADD publish_timestamp timestamp DEFAULT NULL;");
        }
    },
    "Adiciona coluna auto_publish na tabela opportunity" => function () {
        if(!__column_exists('opportunity', 'auto_publish')) {
            __exec("ALTER TABLE opportunity ADD auto_publish BOOLEAN DEFAULT 'false' NOT NULL;");
        }
    },
    "Adiciona coluna evaluation_from e evaluation_to na tabela evaluation_method_configuration" => function() use ($conn){
        if(!__column_exists('evaluation_method_configuration', 'evaluation_from')) {
            __exec("ALTER TABLE evaluation_method_configuration ADD evaluation_from timestamp DEFAULT NULL;");
        }
        if(!__column_exists('evaluation_method_configuration', 'evaluation_to')) {
            __exec("ALTER TABLE evaluation_method_configuration ADD evaluation_to timestamp DEFAULT NULL;");
        }
    },
    
    "adiciona coluna name na tabela evaluation_method_configuration" => function () use($conn) {
        if (!__column_exists('evaluation_method_configuration', 'name')) {
            __exec("ALTER TABLE evaluation_method_configuration ADD name VARCHAR(255) DEFAULT NULL;");
        }
    },

    "Renomeia colunas registrationFrom e registrationTo da tabela de projetod" => function() use ($conn){
        if (__column_exists('project', 'registration_from') && !__column_exists('project', 'starts_on')){
            __exec("ALTER TABLE project RENAME registration_from TO starts_on;");
        }
        if (__column_exists('project', 'registration_to') && !__column_exists('project', 'ends_on')){
            __exec("ALTER TABLE project RENAME registration_to TO ends_on;");
        }
    },

    "Adiciona novas coluna na tabela registration_field_configuration" => function() use ($conn){
        __exec("ALTER TABLE registration_field_configuration ADD conditional  BOOLEAN;");
        __exec("ALTER TABLE registration_field_configuration ADD conditional_field  VARCHAR(255);");
        __exec("ALTER TABLE registration_field_configuration ADD conditional_value  VARCHAR(255);");
    },
    "Adiciona novas coluna na tabela registration_file_configuration" => function() use ($conn){
        __exec("ALTER TABLE registration_file_configuration ADD conditional  BOOLEAN;");
        __exec("ALTER TABLE registration_file_configuration ADD conditional_field  VARCHAR(255);");
        __exec("ALTER TABLE registration_file_configuration ADD conditional_value  VARCHAR(255);");
    },


    'alter seal add column locked_fields' => function () {
        if(!__column_exists('seal', 'locked_fields')) {
            __exec("ALTER TABLE seal ADD locked_fields JSON DEFAULT '[]'");
        }
    },

    'Adiciona a coluna description para a descrição da ocorrência' => function() {
        if(!__column_exists('event_occurrence', 'description')) {
            __exec("ALTER TABLE event_occurrence ADD description TEXT DEFAULT NULL;");
        }
    },
    'Adiciona a coluna price para a o valor de entrada da ocorrência' => function() {
        if(!__column_exists('event_occurrence', 'price')) {
            __exec("ALTER TABLE event_occurrence ADD price TEXT DEFAULT NULL;");
        }
    },
    'Adiciona a coluna priceInfo para a informações sobre o valor de entrada da ocorrência' => function() {
        if(!__column_exists('event_occurrence', 'priceinfo')) {
            __exec("ALTER TABLE event_occurrence ADD priceinfo TEXT DEFAULT NULL;");
        }
    },
    

    "Cria colunas proponent_type e registration na tabela registration" => function() use ($conn){
        if(!__column_exists('registration', 'proponent_type')) {
            __exec("ALTER TABLE registration ADD COLUMN proponent_type VARCHAR(255) NULL");
        }

        if(!__column_exists('registration', 'range')) {
            __exec("ALTER TABLE registration ADD COLUMN range VARCHAR(255) NULL");
        }
    },

    "Cria colunas registration_proponent_types e registration_ranges na tabela opportunity" => function() use ($conn){
        if(!__column_exists('opportunity', 'registration_proponent_types')) {
            __exec("ALTER TABLE opportunity ADD COLUMN registration_proponent_types JSON NULL");
        }

        if(!__column_exists('opportunity', 'registration_ranges')) {
            __exec("ALTER TABLE opportunity ADD COLUMN registration_ranges JSON NULL");
        }
    },

    "Cria colunas registration_ranges e proponent_types na tabela registration_field_configuration" => function() use ($conn){
        if(!__column_exists('registration_field_configuration', 'registration_ranges')) {
            __exec("ALTER TABLE registration_field_configuration ADD COLUMN registration_ranges JSON NULL");
        }

        if(!__column_exists('registration_field_configuration', 'proponent_types')) {
            __exec("ALTER TABLE registration_field_configuration ADD COLUMN proponent_types JSON NULL");
        }
    },

    "Cria colunas registration_ranges e proponent_types na tabela registration_file_configuration" => function() use ($conn){
        if(!__column_exists('registration_file_configuration', 'registration_ranges')) {
            __exec("ALTER TABLE registration_file_configuration ADD COLUMN registration_ranges JSON NULL");
        }

        if(!__column_exists('registration_file_configuration', 'proponent_types')) {
            __exec("ALTER TABLE registration_file_configuration ADD COLUMN proponent_types JSON NULL");
        }
    },
    
    "Cria colunas score e eligible na entidade Registration - correcao" => function() use ($conn){
       if(!__column_exists('registration', 'score')) {
            __exec("ALTER TABLE registration ADD COLUMN score FLOAT NULL");
        } 
        if(!__column_exists('registration', 'eligible')) {
            __exec("ALTER TABLE registration ADD COLUMN eligible BOOLEAN NULL");
        }
    },

    'Adiciona as colunas subsite_id e user_id à tabela job' => function () {
        __exec("ALTER TABLE job ADD COLUMN subsite_id INTEGER NULL");
        __exec("ALTER TABLE job ADD COLUMN user_id INTEGER NULL");
    },
    
    'Cria coluna send_timestamp para registrar o envio das avaliações' => function() use($conn) {
        if(!__column_exists('registration_evaluation', 'sent_timestamp')) {
            __exec("ALTER TABLE registration_evaluation ADD sent_timestamp TIMESTAMP NULL");
        }
    },

    'Define os valores da nova coluna sent_timestamp na tabela de avaliações' => function() use($conn) {
        if (__column_exists('registration_evaluation', 'sent_timestamp')) {
            __exec("
                WITH er_data AS (
                    SELECT er.object_id, er.create_timestamp
                    FROM entity_revision er
                    JOIN entity_revision_revision_data errd ON errd.revision_id = er.id
                    JOIN entity_revision_data rd ON rd.id = errd.revision_data_id
                    WHERE rd.key = 'status' AND rd.value = '2' AND er.object_type = 'MapasCulturais\Entities\RegistrationEvaluation'
                    ORDER BY er.create_timestamp DESC
                )
                UPDATE registration_evaluation
                SET sent_timestamp = er_data.create_timestamp
                FROM er_data
                WHERE registration_evaluation.id = er_data.object_id;
            ");
        }
    },

    "Cria colunas editableUntil editSentTimestamp e editableFields na tabela registration" => function() use ($conn){
        if(!__column_exists('registration', 'editable_until')) {
             __exec("ALTER TABLE registration ADD COLUMN editable_until TIMESTAMP NULL");
        }
        if(!__column_exists('registration', 'edit_sent_timestamp')) {
            __exec("ALTER TABLE registration ADD COLUMN edit_sent_timestamp TIMESTAMP NULL");
        }
        if(!__column_exists('registration', 'editable_fields')) {
            __exec("ALTER TABLE registration ADD COLUMN editable_fields JSON NULL");
        }
    },

    'create table system_role' => function () {
        __exec("CREATE SEQUENCE system_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1;");
        __exec("CREATE TABLE system_role (
                    id INT NOT NULL, 
                    slug VARCHAR(64) NOT NULL, 
                    name VARCHAR(255) NOT NULL, 
                    subsite_context BOOLEAN NOT NULL, 
                    permissions JSON DEFAULT NULL, 
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, 
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, 
                    status SMALLINT NOT NULL, 
                    PRIMARY KEY(id));");
        __exec("COMMENT ON COLUMN system_role.permissions IS '(DC2Type:json_array)';");
    },

    'alter system_role.permissions comment' => function () {
        __exec("COMMENT ON COLUMN system_role.permissions IS '(DC2Type:json)';");
    },

    'Corrige constraint enforce_geotype_geom da tabela geo_division' => function() use($conn) {
        __try("ALTER TABLE geo_division DROP CONSTRAINT enforce_geotype_geom");

        __try(
            "ALTER TABLE 
                geo_division 
            ADD CONSTRAINT 
                enforce_geotype_geom
            CHECK 
                (geometrytype(geom) = 'MULTIPOLYGON'::text OR 
                geometrytype(geom) = 'POLYGON'::text OR geom IS NULL)
        ");
    },

    'cria funções para o cast automático de ponto para varchar' => function () { 
        __exec("CREATE OR REPLACE FUNCTION pg_catalog.text(point) RETURNS text STRICT IMMUTABLE LANGUAGE SQL AS 'SELECT $1::VARCHAR;';");
        __try("CREATE CAST (point AS text) WITH FUNCTION pg_catalog.text(point) AS IMPLICIT;");
        __exec("COMMENT ON FUNCTION pg_catalog.text(point) IS 'convert point to text';");
    },

    'cria coluna is_tiebreaker na tabela registration_evaluation' => function () {
        if(!__column_exists('registration_evaluation', 'is_tiebreaker')) {
            __exec("ALTER TABLE registration_evaluation ADD is_tiebreaker BOOLEAN DEFAULT FALSE");
        }
    },

    'create table workplan' => function () {
        __exec("CREATE TABLE registration_workplan (
          id SERIAL PRIMARY KEY,
          agent_id INTEGER,          
          registration_id INTEGER,
          create_timestamp timestamp without time zone NOT NULL,
          update_timestamp timestamp(0) without time zone
      )");

        __exec("ALTER TABLE registration_workplan ADD FOREIGN KEY (registration_id) REFERENCES registration(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_meta (
            object_id integer NOT NULL,
            key character varying(255) NOT NULL,
            value text,
            id SERIAL NOT NULL
        );");
        __exec("ALTER TABLE registration_workplan_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan(id) ON DELETE CASCADE");
    },

    'create table workplan goal' => function () {
        __exec("CREATE TABLE registration_workplan_goal (
            id SERIAL PRIMARY KEY,
            agent_id INTEGER,          
            workplan_id INTEGER,
            create_timestamp timestamp without time zone NOT NULL,
            update_timestamp timestamp(0) without time zone
        )");

        __exec("ALTER TABLE registration_workplan_goal ADD FOREIGN KEY (workplan_id) REFERENCES registration_workplan(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan_goal ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_goal meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_goal_meta (
              object_id integer NOT NULL,
              key character varying(255) NOT NULL,
              value text,
              id SERIAL NOT NULL
          );");
        __exec("ALTER TABLE registration_workplan_goal_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan_goal(id) ON DELETE CASCADE");
    },
    'create table workplan goal delivery' => function () {
        __exec("CREATE TABLE registration_workplan_goal_delivery (
            id SERIAL PRIMARY KEY,
            agent_id INTEGER,     
            goal_id INTEGER,     
            create_timestamp timestamp without time zone NOT NULL,
            update_timestamp timestamp(0) without time zone
        )");

        __exec("ALTER TABLE registration_workplan_goal_delivery ADD FOREIGN KEY (goal_id) REFERENCES registration_workplan_goal(id) ON DELETE CASCADE");
        __exec("ALTER TABLE registration_workplan_goal_delivery ADD FOREIGN KEY (agent_id) REFERENCES agent(id) ON DELETE CASCADE");
    },
    'create table workplan_goal delivery meta' => function () {
        __exec("CREATE TABLE public.registration_workplan_goal_delivery_meta (
              object_id integer NOT NULL,
              key character varying(255) NOT NULL,
              value text,
              id SERIAL NOT NULL
          );");
        __exec("ALTER TABLE registration_workplan_goal_delivery_meta ADD FOREIGN KEY (object_id) REFERENCES registration_workplan_goal_delivery(id) ON DELETE CASCADE");
    },

    "Adiciona coluna status na tabela registration_workplan_goal" => function() use ($conn){
        if(!__column_exists('registration_workplan_goal', 'status')) {
            __exec("ALTER TABLE registration_workplan_goal ADD status smallint DEFAULT 0;");
        }
    },

    "Adiciona coluna status na tabela registration_workplan_goal_delivery" => function() use ($conn){
        if(!__column_exists('registration_workplan_goal_delivery', 'status')) {
            __exec("ALTER TABLE registration_workplan_goal_delivery ADD status smallint DEFAULT 0;");
        }
    },

    'altera tipo da coluna chat_message.payload para json' => function () {
        __exec("ALTER TABLE chat_message ALTER COLUMN payload SET DATA TYPE JSON USING to_jsonb(payload)::JSON");
    },
    'define default para as colunas ids das tabelas sem default' => function() {
        __exec("ALTER TABLE agent_meta ALTER column id SET DEFAULT nextval('agent_meta_id_seq');");
        __exec("ALTER TABLE space_meta ALTER column id SET DEFAULT nextval('space_meta_id_seq');");
        __exec("ALTER TABLE project_meta ALTER column id SET DEFAULT nextval('project_meta_id_seq');");
        __exec("ALTER TABLE event_meta ALTER column id SET DEFAULT nextval('event_meta_id_seq');");
        __exec("ALTER TABLE subsite_meta ALTER column id SET DEFAULT nextval('subsite_meta_id_seq');");
        __exec("ALTER TABLE evaluationmethodconfiguration_meta ALTER column id SET DEFAULT nextval('evaluationmethodconfiguration_meta_id_seq');");
    },
    
    'Criação da coluna update timestemp' => function() use($conn) {

        if(!__column_exists('registration', 'update_timestamp')){
            __exec("ALTER TABLE registration ADD COLUMN update_timestamp TIMESTAMP");
        }

        $conn->executeQuery("
           UPDATE registration r
            SET update_timestamp = recent_revision.create_timestamp
            FROM (
                SELECT DISTINCT ON (object_id) object_id, create_timestamp
                FROM entity_revision
                WHERE object_type = 'MapasCulturais\Entities\Registration'
                ORDER BY object_id, id DESC
            ) AS recent_revision
            WHERE r.id = recent_revision.object_id;
        ");
    },

    'define a coluna id da tabela permission_cache_pending como auto incremet' => function() {
        __exec("ALTER TABLE permission_cache_pending ALTER column id SET DEFAULT nextval('permission_cache_pending_seq');");
    },

    'altera o tipo da coluna valuers_exceptions_list da tabela registration para jsonb' => function () {
        __exec("ALTER TABLE registration ALTER COLUMN valuers_exceptions_list DROP DEFAULT;"); 
        __exec("ALTER TABLE registration ALTER COLUMN valuers_exceptions_list TYPE JSONB USING valuers_exceptions_list::JSONB");
        __exec("ALTER TABLE registration ALTER COLUMN valuers_exceptions_list SET DEFAULT '{\"include\": [], \"exclude\": []}'::jsonb;"); 
        __exec("CREATE INDEX registration_valuers_index ON registration USING GIN((valuers_exceptions_list->'include') jsonb_path_ops)");
    },

    'adiciona coluna valuers à tabela registration' => function () {
        if(!__column_exists('registration', 'valuers')) {
            __exec("ALTER TABLE registration ADD COLUMN valuers JSONB DEFAULT '{}'::jsonb NOT NULL");
            __exec("CREATE INDEX registration_valuers_idx ON registration USING GIN((valuers) jsonb_path_ops)");
        }
    },

    'adiciona coluna committee à tabela registration_evaluation' => function () {
        if(!__column_exists('registration_evaluation', 'committee')) {
            __exec("ALTER TABLE registration_evaluation ADD COLUMN committee VARCHAR(255)");

            // define o valor da coluna committee
            __exec("UPDATE registration_evaluation
                    SET committee = com.committee,
                        is_tiebreaker = (com.committee = '@tiebreaker')
                    FROM
                        (
                            SELECT re.id, ar.type AS committee
                            FROM registration_evaluation re
                                LEFT JOIN usr u on u.id = re.user_id
                                LEFT JOIN registration r on r.id = re.registration_id
                                LEFT JOIN opportunity o on o.id = r.opportunity_id
                                LEFT JOIN evaluation_method_configuration emc on emc.opportunity_id = o.id
                                LEFT JOIN agent_relation ar on ar.object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration'
                                    AND ar.object_id = emc.id
                                    AND ar.agent_id = u.profile_id
                        ) AS com
                    WHERE registration_evaluation.id = com.id;");
        }
    },

    "Cria coluna continuous_flow na tabela opportunity" => function() use ($conn) {
        if (!__column_exists('opportunity', 'continuous_flow')) {
            __exec("ALTER TABLE opportunity ADD COLUMN continuous_flow TIMESTAMP NULL");
        }
    },
    'Cria a tabela da entidade RegistrationStep' => function () {
        $app = App::i();
        $em = $app->em;

        $conn = $em->getConnection();

        if (!__table_exists('registration_step')) {
            if (!__sequence_exists('registration_step_seq')) {
                $conn->executeQuery("CREATE SEQUENCE registration_step_seq START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;");
            }

            __exec("CREATE TABLE registration_step (
                    id INT NOT NULL DEFAULT nextval('registration_step_seq'),
                    name VARCHAR DEFAULT NULL,
                    display_order INT NOT NULL DEFAULT 0,
                    opportunity_id INT NOT NULL,
                    create_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    update_timestamp TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                    PRIMARY KEY(id)
                );"
            );

            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_step__step_id ON registration_step (id);");
            __try("ALTER TABLE registration_step ADD CONSTRAINT FK_registration_step__opportunity FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_step__opportunity_id ON registration_step (opportunity_id);");
        }

        if (!__column_exists('registration_field_configuration', "step_id")) {
            __exec("ALTER TABLE registration_field_configuration ADD COLUMN step_id INT NULL;");
            __try("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_registration_field_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_field_configuration__step_id ON registration_field_configuration (step_id);");
        }

        if (!__column_exists('registration_file_configuration', "step_id")) {
            __exec("ALTER TABLE registration_file_configuration ADD COLUMN step_id INT NULL;");
            __try("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_registration_file_configuration__registration_step FOREIGN KEY (step_id) REFERENCES registration_step (id) ON DELETE CASCADE DEFERRABLE INITIALLY IMMEDIATE;");
            __exec("CREATE INDEX IF NOT EXISTS IDX_registration_file_configuration__step_id ON registration_file_configuration (step_id);");
        }
    },
    
    'Adiciona coluna de metadados na tabela da entidade RegistrationStep' => function () {
        if (!__column_exists('registration_step', 'metadata')) {
            __try("ALTER TABLE registration_step ADD COLUMN metadata json DEFAULT '{}'::json NOT NULL");
        }
    },

     'Implementa db-update para remoção de metadados duplicados em todas as tabelas auxiliares' => function () {
        $app = App::i();
        $em = $app->em;
        $conn = $em->getConnection();

        $tabelas = [
            'agent_meta' => 'agente',
            'registration_meta' => 'inscrição',
            'opportunity_meta' => 'oportunidade',
            'space_meta' => 'espaço',
            'project_meta' => 'projeto',
            'event_meta' => 'evento',
            'evaluationmethodconfiguration_meta' => 'avaliaçao',
            'notification_meta' => 'notificação',
            'seal_meta' => 'selo',
            'subsite_meta' => 'subsite',
            'user_meta' => 'usuário',
        ];

        foreach ($tabelas as $tabela => $tipo_entidade) {
            __exec("
                DELETE FROM $tabela T1
                USING $tabela T2
                WHERE
                    T1.id < T2.id AND
                    T1.object_id = T2.object_id AND
                    T1.key = T2.key AND
                    T1.value = T2.value
            ");

            $duplicates = $conn->fetchAllAssociative("
                SELECT key, object_id
                FROM {$tabela}
                GROUP BY key, object_id
                HAVING COUNT(*) > 1
            ");

            foreach ($duplicates as $dup) {
                $key = $dup['key'];
                $object_id = $dup['object_id'];

                $rows = $conn->fetchAllAssociative("
                    SELECT id, key
                    FROM {$tabela}
                    WHERE key = :key AND object_id = :object_id
                    ORDER BY id DESC
                ", ['key' => $key, 'object_id' => $object_id]);

                if (count($rows) < 2) {
                    continue;
                }

                $rows_to_update = array_slice($rows, 1);

                foreach ($rows_to_update as $row) {
                    $new_key = $row['key'] . '_' . $row['id'];

                    $conn->update($tabela, [
                        'key' => $new_key
                    ], [
                        'id' => $row['id']
                    ]);

                    $app->log->debug("Metadado {$key} atualizado para {$new_key} na {$tipo_entidade} de id {$object_id} (tabela {$tabela})");
                }
            }
        }
    },

    'Aplica indices UNIQUE nas tabelas auxiliares' => function () {
        $app = App::i();

        $aux_tables = [
            'agent_meta',
            'registration_meta',
            'opportunity_meta',
            'space_meta',
            'project_meta',
            'event_meta',
            'evaluationmethodconfiguration_meta',
            'notification_meta',
            'seal_meta',
            'subsite_meta',
            'user_meta',
        ];

        foreach ($aux_tables as $table) {
            __exec("CREATE UNIQUE INDEX {$table}_unique_object_id_key_value ON {$table} (object_id, key)");
            $app->log->debug("Aplicado Índice Único na tabela auxiliar {$table}");
        }
    },
    /// MIGRATIONS - DATA CHANGES =========================================

    'migrate gender' => function() use ($conn) {
        $conn->executeQuery("UPDATE agent_meta SET value='Homem' WHERE key='genero' AND value='Masculino'");
        $conn->executeQuery("UPDATE agent_meta SET value='Mulher' WHERE key='genero' AND value='Feminino'");
    },

    'remove orphan events again' => function() use($conn){
        $conn->executeQuery("DELETE FROM event_meta WHERE object_id IN (SELECT id FROM event WHERE agent_id IS NULL)");
        $conn->executeQuery("DELETE FROM event WHERE agent_id IS NULL");
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
        if($id = $conn->fetchScalar("SELECT id FROM seal WHERE id = 1")){
            return true;
        }
        $agent_id = $conn->fetchScalar("select profile_id
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
        $agent_id = $conn->fetchScalar("select profile_id
                    from usr
                    where id = (
                        select min(usr_id)
                        from role
                        where name = 'superAdmin'
                    )");
        $conn->executeQuery("UPDATE seal_relation SET owner_id = '$agent_id' WHERE owner_id IS NULL;");
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

    
    "corrigindo status da fila de criação de cache de permissão" => function() {
        __exec("UPDATE permission_cache_pending SET status = 0;");
        return false;
    },

    'fix opportunity type 35' => function(){
        __exec("UPDATE opportunity SET type = 45 WHERE type = 35");
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

    'fix subsite verifiedSeals array' => function() use($app){
        $subsites = $app->repo('Subsite')->findAll();
        foreach($subsites as $subsite){
            $subsite->setVerifiedSeals($subsite->verifiedSeals);
            $subsite->save(true);
        }
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

    'recreate ALL FKs' => function () use($conn) {
        $sql = "
DO
    $$
    DECLARE r record;
    BEGIN
    FOR r IN (SELECT constraint_name, table_name FROM information_schema.table_constraints WHERE table_schema = 'public' AND constraint_type='FOREIGN KEY') LOOP
        raise info '%','dropping '||r.constraint_name;
        execute CONCAT('ALTER TABLE \"public\".'||r.table_name||' DROP CONSTRAINT '||r.constraint_name);
    END LOOP;
END;
$$
;";
        __exec($sql);

        __exec("ALTER TABLE subsite ADD CONSTRAINT FK_B0F67B6F3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE agent ADD CONSTRAINT FK_268B9C9D727ACA70 FOREIGN KEY (parent_id) REFERENCES agent (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE agent ADD CONSTRAINT FK_268B9C9DA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE agent ADD CONSTRAINT FK_268B9C9DBDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE role ADD CONSTRAINT FK_57698A6AC69D3FB FOREIGN KEY (usr_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE role ADD CONSTRAINT FK_57698A6ABDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE subsite_meta ADD CONSTRAINT FK_780702F5232D562B FOREIGN KEY (object_id) REFERENCES subsite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE file ADD CONSTRAINT FK_8C9F3610727ACA70 FOREIGN KEY (parent_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE usr ADD CONSTRAINT FK_1762498CCCFA12B8 FOREIGN KEY (profile_id) REFERENCES agent (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space ADD CONSTRAINT FK_2972C13A727ACA70 FOREIGN KEY (parent_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space ADD CONSTRAINT FK_2972C13A3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space ADD CONSTRAINT FK_2972C13ABDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE727ACA70 FOREIGN KEY (parent_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EEBDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D7727ACA70 FOREIGN KEY (parent_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE opportunity ADD CONSTRAINT FK_8389C3D73414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA73414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7BDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE agent_meta ADD CONSTRAINT FK_7A69AED6232D562B FOREIGN KEY (object_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE agent_relation ADD CONSTRAINT FK_54585EDD3414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE term_relation ADD CONSTRAINT FK_EDDF39FDE2C35FC FOREIGN KEY (term_id) REFERENCES term (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal_relation ADD CONSTRAINT FK_487AF65154778145 FOREIGN KEY (seal_id) REFERENCES seal (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal_relation ADD CONSTRAINT FK_487AF6513414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal_relation ADD CONSTRAINT FK_487AF6517E3C61F9 FOREIGN KEY (owner_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE pcache ADD CONSTRAINT FK_3D853098A76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE procuration ADD CONSTRAINT FK_D7BAE7FC69D3FB FOREIGN KEY (usr_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE procuration ADD CONSTRAINT FK_D7BAE7F3AEB2ED7 FOREIGN KEY (attorney_user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE user_meta ADD CONSTRAINT FK_AD7358FC232D562B FOREIGN KEY (object_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal ADD CONSTRAINT FK_2E30AE303414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal ADD CONSTRAINT FK_2E30AE30BDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_occurrence ADD CONSTRAINT FK_E61358DC71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_occurrence ADD CONSTRAINT FK_E61358DC23575340 FOREIGN KEY (space_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space_meta ADD CONSTRAINT FK_BC846EBF232D562B FOREIGN KEY (object_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A79A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A73414710B FOREIGN KEY (agent_id) REFERENCES agent (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A7BDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE evaluation_method_configuration ADD CONSTRAINT FK_330CB54C9A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_meta ADD CONSTRAINT FK_C839589E232D562B FOREIGN KEY (object_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE project_meta ADD CONSTRAINT FK_EE63DC2D232D562B FOREIGN KEY (object_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_file_configuration ADD CONSTRAINT FK_209C792E9A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_field_configuration ADD CONSTRAINT FK_60C85CB19A34590F FOREIGN KEY (opportunity_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE opportunity_meta ADD CONSTRAINT FK_2BB06D08232D562B FOREIGN KEY (object_id) REFERENCES opportunity (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE seal_meta ADD CONSTRAINT FK_A92E5E22232D562B FOREIGN KEY (object_id) REFERENCES seal (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE request ADD CONSTRAINT FK_3B978F9FBA78F12A FOREIGN KEY (requester_user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_occurrence_recurrence ADD CONSTRAINT FK_388ECCB140E9F00 FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE entity_revision_revision_data ADD CONSTRAINT FK_9977A8521DFA7C8F FOREIGN KEY (revision_id) REFERENCES entity_revision (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE entity_revision_revision_data ADD CONSTRAINT FK_9977A852B4906F58 FOREIGN KEY (revision_data_id) REFERENCES entity_revision_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE user_app ADD CONSTRAINT FK_22781144A76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE user_app ADD CONSTRAINT FK_22781144BDDFBE89 FOREIGN KEY (subsite_id) REFERENCES subsite (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BEA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE140E9F00 FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_attendance ADD CONSTRAINT FK_350DD4BE23575340 FOREIGN KEY (space_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE evaluationmethodconfiguration_meta ADD CONSTRAINT FK_D7EDF8B2232D562B FOREIGN KEY (object_id) REFERENCES evaluation_method_configuration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space_relation ADD CONSTRAINT FK_1A0E9A3023575340 FOREIGN KEY (space_id) REFERENCES space (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE space_relation ADD CONSTRAINT FK_1A0E9A30232D562B FOREIGN KEY (object_id) REFERENCES registration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE event_occurrence_cancellation ADD CONSTRAINT FK_A5506736140E9F00 FOREIGN KEY (event_occurrence_id) REFERENCES event_occurrence (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_meta ADD CONSTRAINT FK_18CC03E9232D562B FOREIGN KEY (object_id) REFERENCES registration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE notification_meta ADD CONSTRAINT FK_6FCE5F0F232D562B FOREIGN KEY (object_id) REFERENCES notification (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_evaluation ADD CONSTRAINT FK_2E186C5C833D8F43 FOREIGN KEY (registration_id) REFERENCES registration (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE registration_evaluation ADD CONSTRAINT FK_2E186C5CA76ED395 FOREIGN KEY (user_id) REFERENCES usr (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16C47D5262 FOREIGN KEY (chat_thread_id) REFERENCES public.chat_thread (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16727ACA70 FOREIGN KEY (parent_id) REFERENCES public.chat_message (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        __exec("ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES public.usr (id) MATCH SIMPLE ON UPDATE NO ACTION ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;");
        
    },

    'DROP MATERIALIZED VIEW evaluations!' => function () {
        __try("DROP MATERIALIZED VIEW evaluations");
    },

    'Recria view evaluations!!!!!!!' => function() use($conn) {
        __try("DROP VIEW IF EXISTS evaluations");

        $conn->executeQuery("
            CREATE VIEW evaluations AS (
                SELECT 
                    registration_id,
                    registration_sent_timestamp,
                    registration_number,
                    registration_category,
                    registration_agent_id,
                    opportunity_id,
                    valuer_user_id,
                    valuer_agent_id,
                    valuer_committee,
                    max(evaluation_id) AS evaluation_id,
                    max(evaluation_result) AS evaluation_result,
                    max(evaluation_status) AS evaluation_status
                FROM (
                    SELECT 
                        r.id AS registration_id, 
                        r.sent_timestamp AS registration_sent_timestamp,
                        r.number AS registration_number, 
                        r.category AS registration_category, 
                        r.agent_id AS registration_agent_id, 
                        re.user_id AS valuer_user_id, 
                        u.profile_id AS valuer_agent_id, 
                        r.valuers ->> u.id::varchar as valuer_committee,
                        r.opportunity_id,
                        re.id AS evaluation_id,
                        re.result AS evaluation_result,
                        re.status AS evaluation_status
                    FROM registration r 
                        JOIN registration_evaluation re 
                            ON re.registration_id = r.id 
                        JOIN usr u 
                            ON u.id = re.user_id
                    WHERE 
                        r.status > 0
                UNION
                    SELECT 
                        r2.id AS registration_id, 
                        r2.sent_timestamp AS registration_sent_timestamp,
                        r2.number AS registration_number, 
                        r2.category AS registration_category,
                        r2.agent_id AS registration_agent_id, 
                        u2.id AS valuer_user_id, 
                        u2.profile_id AS valuer_agent_id, 
                        r2.valuers ->> u2.id::varchar as valuer_committee,
                        r2.opportunity_id,
                        NULL AS evaluation_id,
                        NULL AS evaluation_result,
                        NULL AS evaluation_status
                    
                    FROM registration r2 
                        JOIN usr u2 
                            on jsonb_exists(r2.valuers, u2.id::varchar)
                        JOIN evaluation_method_configuration emc
                            ON emc.opportunity_id = r2.opportunity_id
                    WHERE                          
                        r2.status >= 1
                ) AS evaluations_view 
                GROUP BY
                    registration_id,
                    registration_sent_timestamp,
                    registration_number,
                    registration_category,
                    registration_agent_id,
                    valuer_user_id,
                    valuer_agent_id,
                    valuer_committee,
                    opportunity_id
            )
        ");
    },

    'delete job de refresh materialized view evaluations' => function() use($conn) {
        __exec("DELETE FROM job WHERE name = 'RefreshViewEvaluations'");
    },

    'adiciona oportunidades na fila de reprocessamento de cache' => function () use($conn) {
        $sql = "SELECT id from opportunity where parent_id is null and status > 0";
        foreach($conn->fetchAll($sql) as $em) {
            __exec("
                INSERT INTO permission_cache_pending (
                    id,
                    object_type,
                    object_id,
                    status
                ) VALUES (
                    nextval('permission_cache_pending_seq'::regclass), 
                    'MapasCulturais\Entities\Opportunity',
                    {$em['id']},
                    0
                )");
        }
    },

    'adiciona novos indices a tabela agent_relation' => function ()  { 
        __try("DROP INDEX agent_relation_all;");
        __try("CREATE INDEX agent_relation_owner_type ON agent_relation (object_type);");
        __try("CREATE INDEX agent_relation_owner_id ON agent_relation (object_id);");
        __try("CREATE INDEX agent_relation_owner ON agent_relation (object_type, object_id);");
        __try("CREATE INDEX agent_relation_owner_agent ON agent_relation (object_type, object_id, agent_id);");
        __try("CREATE INDEX agent_relation_has_control ON agent_relation (has_control);");
        __try("CREATE INDEX agent_relation_status ON agent_relation (status);");
        __try("ALTER INDEX idx_54585edd3414710b RENAME TO agent_relation_agent;");
    },

    'valuer disabling refactor' => function() use($conn) {
        $conn->executeQuery("
            UPDATE 
                agent_relation 
            SET 
                status = 8, 
                has_control = true 
            WHERE
                object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration' AND
                has_control IS false");
    },

    'clean existing orphans' => function () {
        __exec("CREATE OR REPLACE FUNCTION pg_temp.tempfn_clean_orphans(tbl name, ctype name, cid name)
                    RETURNS VOID
                    LANGUAGE 'plpgsql' AS $$
                    BEGIN
                        EXECUTE format('DELETE FROM %1\$I WHERE (
                                %2\$I=''MapasCulturais\Entities\Agent'' AND
                                %3\$I NOT IN (SELECT id FROM agent)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\ChatMessage'' AND
                                %3\$I NOT IN (SELECT id FROM chat_message)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\ChatThread'' AND
                                %3\$I NOT IN (SELECT id FROM chat_thread)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\EvaluationMethodConfiguration'' AND
                                %3\$I NOT IN (SELECT id FROM evaluation_method_configuration)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Event'' AND
                                %3\$I NOT IN (SELECT id FROM event)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Notification'' AND
                                %3\$I NOT IN (SELECT id FROM notification)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Opportunity'' AND
                                %3\$I NOT IN (SELECT id FROM opportunity)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Project'' AND
                                %3\$I NOT IN (SELECT id FROM project)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Registration'' AND
                                %3\$I NOT IN (SELECT id FROM registration)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\RegistrationFileConfiguration'' AND
                                %3\$I NOT IN (SELECT id FROM registration_file_configuration)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Space'' AND
                                %3\$I NOT IN (SELECT id FROM space)
                            ) OR (
                                %2\$I=''MapasCulturais\Entities\Subsite'' AND
                                %3\$I NOT IN (SELECT id FROM subsite)
                            )', tbl, ctype, cid);
                    END; $$;");
        __exec("SELECT pg_temp.tempfn_clean_orphans('agent_relation', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('seal_relation', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('space_relation', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('term_relation', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('metalist', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('file', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('chat_thread', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('pcache', 'object_type', 'object_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('request', 'origin_type', 'origin_id')");
        __exec("SELECT pg_temp.tempfn_clean_orphans('request', 'destination_type', 'destination_id')");
    },

    'add triggers for orphan cleanup' => function () {
        __exec("CREATE OR REPLACE FUNCTION fn_clean_orphans()
                    RETURNS trigger
                    LANGUAGE 'plpgsql'
                    COST 100
                    VOLATILE NOT LEAKPROOF AS $$
                        DECLARE _p_type VARCHAR;
                    BEGIN
                        _p_type=TG_ARGV[0];
                        DELETE FROM agent_relation WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM seal_relation WHERE
                            object_type=_p_type AND object_id=OLD.id;   
                        DELETE FROM space_relation WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM term_relation WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM metalist WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM file WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM chat_thread WHERE
                            object_type=_p_type AND object_id=OLD.id;
                        DELETE FROM pcache WHERE
                            object_type::varchar=_p_type AND object_id=OLD.id;
                        DELETE FROM request WHERE
                            (origin_type=_p_type AND origin_id=OLD.id) OR
                            (destination_type=_p_type AND destination_id=OLD.id);
                        RETURN NULL;
                    END; $$;");

        __try("CREATE TRIGGER trigger_clean_orphans_agent
                    AFTER DELETE ON agent
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Agent')");

        __try("CREATE TRIGGER trigger_clean_orphans_chat_message
                    AFTER DELETE ON chat_message
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\ChatMessage')");
        __try("CREATE TRIGGER trigger_clean_orphans_chat_thread
                    AFTER DELETE ON chat_thread
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\ChatThread')");
        __try("CREATE TRIGGER trigger_clean_orphans_evaluation_method_configuration
                    AFTER DELETE ON evaluation_method_configuration
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\EvaluationMethodConfiguration')");
        __try("CREATE TRIGGER trigger_clean_orphans_event
                    AFTER DELETE ON event
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Event')");
        __try("CREATE TRIGGER trigger_clean_orphans_notification
                    AFTER DELETE ON notification
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Notification')");
        __try("CREATE TRIGGER trigger_clean_orphans_opportunity
                    AFTER DELETE ON opportunity
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Opportunity')");
        __try("CREATE TRIGGER trigger_clean_orphans_project
                    AFTER DELETE ON project
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Project')");
        __try("CREATE TRIGGER trigger_clean_orphans_registration
                    AFTER DELETE ON registration
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Registration')");
        __try("CREATE TRIGGER trigger_clean_orphans_registration_file_configuration
                    AFTER DELETE ON registration_file_configuration
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\RegistrationFileConfiguration')");
        __try("CREATE TRIGGER trigger_clean_orphans_space
                    AFTER DELETE ON space
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Space')");
        __try("CREATE TRIGGER trigger_clean_orphans_subsite
                    AFTER DELETE ON subsite
                    FOR EACH ROW
                    EXECUTE PROCEDURE fn_clean_orphans('MapasCulturais\Entities\Subsite')");
    },
    "Remove lixo angular registration_meta" => function() use ($conn){

        $clean_meta = function($meta, $clean_meta){
            if(is_array($meta)){
                foreach($meta as $key => $value){
                    $meta[$key] = $clean_meta($value, $clean_meta);
                }
            }else if(is_object($meta)){
                foreach($meta as $key => $value){
                    if($key == '$$hashKey'){
                        unset($meta->$key);
                    }
                }
            }
            return $meta;
        };

        $metas = $conn->fetchAll("SELECT * FROM registration_meta WHERE value LIKE '%\$\$hashKey%'");
        foreach($metas as $i => $meta){
            $raw_value = json_decode($meta['value']);            
            $value = json_encode($clean_meta($raw_value, $clean_meta));

            $meta['value'] = ($value == "[{}]") ? "[]" : $value;

            $conn->update("registration_meta", $meta, ['id' => $meta['id']]);
            
            echo "\nRemovido hashKey registration_meta id {$meta['id']}";
        }
    },

    "popula as colunas name, evaluation_from e evaluation_to da tabela evaluation_method_configuration" => function () use ($conn, $app){
        if (empty($app->getRegisteredEvaluationMethods())) {
            return false;
        }

        $rs = $conn->fetchAll("
            SELECT e.id, e.type, op.name, op.registration_from, op.registration_to 
            FROM evaluation_method_configuration e LEFT JOIN opportunity op ON op.id = e.opportunity_id
        ");
        
        $num = count($rs);
        foreach ($rs as $i => $r) {
            $data = ['name' => $r['name']];
            if ($method = $app->getRegisteredEvaluationMethodBySlug($r['type'])) {
                $data['name'] = $r['type'] == 'simple' ? i::__('Avaliação') : $method->name;
            }

            if ($r['registration_from']) {
                $data['evaluation_from'] = $r['registration_from'];
            }

            if ($r['registration_to']) {
                $data['evaluation_to'] = $r['registration_to'];
            }
            echo "\n({$i}/{$num}) populando tabela evaluation_method_configuration ({$r['id']}): ";
            print_r($data);
            echo "\n----------------------";
            $conn->update('evaluation_method_configuration', $data, ['id' => $r['id']]);
        }
    },

    'Ajusta as colunas registration_proponent_types, registration_ranges e registration_categories das oportuniodades para setar um array vazio quando as mesmas estiverem null' => function() use ($conn, $app){
        __exec("UPDATE opportunity set registration_proponent_types = '[]' WHERE registration_proponent_types IS null OR registration_proponent_types::VARCHAR = '\"\"'");
        __exec("UPDATE opportunity set registration_ranges = '[]' WHERE registration_ranges IS null OR registration_ranges::VARCHAR = '\"\"'");
        __exec("UPDATE opportunity set registration_categories = '[]' WHERE registration_categories IS null OR registration_categories::VARCHAR = '\"\"'");
    },

    "Consede permissão em todos os campo para todos os avaliadores da oportunidade" => function() use ($conn, $app){
        $opportunity_ids = $conn->fetchAll("SELECT id FROM opportunity WHERE status <> 0 AND status >= -1");

        $fields = [];
        foreach($opportunity_ids as $key => $id){
            
            $cont = $key+1;

            $opp = $app->repo("Opportunity")->findOneBy(['id' => $id['id']]);

            if($opp->avaliableEvaluationFields){
                $app->log->debug("{$cont} - Oportunidade {$opp->id} já tem configuração definida para os avaliadores");
                continue;
            }

            if($opp){
                $prop = [
                    'category' => "true",
                    'projectName' => "true",
                    'agentsSummary' => "true",
                    'spaceSummary' => "true",
                ];

                $fields_conf = $opp->getRegistrationFieldConfigurations();
                $files_conf = $opp->getRegistrationFileConfigurations();

                foreach($fields_conf as $field){
                    $fields["field_".$field->id] = "true";
                }

                foreach($files_conf as $field){
                    $fields["rfc_".$field->id] = "true";
                }

                $fields+= $prop;

                $opp->avaliableEvaluationFields = $fields;
                $opp->save(true);
                $app->em->clear();
                $app->log->debug("{$cont} - Configuração de permissão dos avaliadores fetuada na oportunidade {$opp->id}");
            }
        
        }

    }, 
    'corrige metadados criados por erro em inscricoes de fases' => function () use ($conn, $app) {
        $opp_ids = $conn->fetchAll("SELECT id FROM opportunity WHERE parent_id IS NOT NULL");
        foreach ($opp_ids as $opportunity) {
            $opportunity_id = $opportunity['id'];
            
            $conn->exec("
                UPDATE registration_meta 
                SET key = CONCAT('__BKP__', key) 
                WHERE 
                    key LIKE 'field_%' AND 
                    key NOT IN (
                        SELECT concat('field_',id) 
                        FROM registration_field_configuration 
                        WHERE opportunity_id = {$opportunity_id}
                    ) AND 
                    object_id IN (
                        SELECT id 
                        FROM registration 
                        WHERE opportunity_id = {$opportunity_id}
                    );");
        }
    },
    
    'Apaga registro do db-update de "Definição dos cammpos cpf e cnpj com base no documento" para que rode novamente' => function() use ($conn, $app){
        if($conn->fetchAll("SELECT * FROM db_update WHERE name = 'Definição dos cammpos cpf e cnpj com base no documento'")){
            $conn->executeQuery("DELETE FROM db_update WHERE name = 'Definição dos cammpos cpf e cnpj com base no documento'");
        }
    },
    'Corrige config dos campos na entidade registration_fields_configurarion' => function() use ($conn, $app){

        $registration_fields_Types = $app->getRegisteredRegistrationFieldTypes();

        $field_types = [];
        foreach($registration_fields_Types as $type => $values){
            if(preg_match('/^@[a-zA-Z0-9\- ]{1,90}/', $values->name)){
                $field_types[] = "'".trim($values->slug)."'";
            }
        }
        $_field_types = implode(",", $field_types);
    
        $fields = $conn->fetchAll("SELECT * FROM registration_field_configuration WHERE field_type NOT IN ({$_field_types}) AND config LIKE '%entityField%'");
        
        $txt = "";
        foreach($fields as $field){
            $_field = $app->repo("RegistrationFieldConfiguration")->find($field['id']);
            $config = $_field->config;
            $txt.='['.$_field->id.' => '.serialize($_field->config).']\n';
            unset($config['entityField']);
            array_filter($config);
            $_field->config = $config;
            $_field->save(true);
            $app->log->debug("db-update executado no campo field_{$_field->id}");
            $app->em->clear();
        }

        $fileName = "dbupdate_RegistrationFieldConfiguration.txt";
        $dir = PRIVATE_FILES_PATH . "dbupdate_documento";
        if (!file_exists($dir)) {
            mkdir($dir, 775);
        }

        $path = $dir . "/" . $fileName;
        $fp = fopen($path, "wb");
        fwrite($fp, $txt);
        fclose($fp);
    },
    "seta como vazio campo escolaridade do agent caso esteja com valor não informado" => function() use ($conn, $app){
        /** @var App $app */
        $app= App::i();
        $conn = $app->em->getConnection();
        if($agent_ids = $conn->fetchAll("SELECT am.object_id as id  FROM agent_meta am WHERE am.key = 'escolaridade' AND am.value = 'Não Informar'")){
            $app->disableAccessControl();
            foreach($agent_ids as $value){
                $agent = $app->repo("Agent")->find($value['id']);
                $agent->escolaridade =  null;
                $agent->save(true);
            }
            $app->enableAccessControl();
        }
    },
    'altera tipo da coluna description na tabela file' => function() use ($conn, $app){
        $conn->executeQuery("ALTER TABLE file ALTER COLUMN description TYPE text;");
    },
    "faz com que o updateTimestamp seja igual ao createTimestamp na criacão da entidade" => function() use ($conn){
        __exec("UPDATE agent SET update_timestamp = create_timestamp WHERE update_timestamp IS NULL");
        __exec("UPDATE space SET update_timestamp = create_timestamp WHERE update_timestamp IS NULL");
        __exec("UPDATE project SET update_timestamp = create_timestamp WHERE update_timestamp IS null");
        __exec("UPDATE opportunity SET update_timestamp = create_timestamp WHERE update_timestamp IS NULL");
        __exec("UPDATE EVENT SET update_timestamp = create_timestamp WHERE update_timestamp IS NULL");
    },
    "migra valores das colunas do tipo array para do tipo json" => function() use ($conn) {
        $fields = $conn->fetchAll("SELECT id, config, field_options, categories from registration_field_configuration");
        $count = count($fields);

        $json_validate = function (string $string): bool {
            json_decode($string);
            return json_last_error() === JSON_ERROR_NONE;
        };
        
        $check_serialize = function($value) use ($json_validate) {
            if((is_string($value) && $json_validate($value)) || !$value) {
                return $value;
            }

            return json_encode(unserialize($value));
        };

        foreach($fields as $i => $field) {
            echo "migrando registration_field_configuration ({$i} / $count)\n";
            $field['config'] = $check_serialize($field['config']);
            $field['field_options'] = $check_serialize($field['field_options']);
            $field['categories'] = $check_serialize($field['categories']);

            $conn->executeQuery("
                UPDATE registration_field_configuration 
                SET 
                    config = :config, 
                    field_options = :field_options, 
                    categories = :categories
                WHERE id = :id", $field);
        }

        $files = $conn->fetchAll("SELECT id, categories from registration_file_configuration");
        $count = count($files);
        foreach($files as $i => $file) {
            echo "migrando registration_file_configuration ({$i} / $count)\n";
            $file['categories'] = $check_serialize($file['categories']);

            $conn->executeQuery("
                UPDATE registration_file_configuration 
                SET categories = :categories
                WHERE id = :id", $file);
        }

        $requests = $conn->fetchAll("SELECT id, metadata from request");
        $count = count($requests);
        foreach($requests as $i => $request) {
            echo "migrando request ({$i} / $count)\n";
            $id = $request['id'];
            $metadata = $check_serialize($request['metadata']);

            $conn->executeQuery("
                UPDATE request 
                SET metadata = :metadata
                WHERE id = $id", ['metadata'=>$metadata]);
        }
    },

    'corrige permissão de avaliadores que tem avaliação mas não possui permissão de avaliar pela regra configurada' => function() use($conn) {
        $regs = $conn->fetchFirstColumn(" 
            SELECT 
                r.id
            FROM registration r
                JOIN pcache p1 ON p1.object_id = r.id AND p1.action = 'evaluateOnTime'
                LEFT JOIN pcache p2 ON p2.object_id = r.id AND p2.action = 'view' AND p2.user_id = p1.user_id
            WHERE 
                p1.action IS NOT NULL AND p2.action IS NULL");

        $app = App::i();
        
        $count = count($regs);
        foreach($regs as $i => $reg) {
            $registration = $app->repo('Registration')->find($reg);
            echo "\n$i / $count ---- $registration";
            $app->enqueueEntityToPCacheRecreation($registration);
        }

        $app->persistPCachePendingQueue();
    },

    'adiciona coluna user_id à tabela pending_permission_cache' => function() use($conn) {
        __exec('ALTER TABLE permission_cache_pending ADD usr_id INT DEFAULT NULL;');
    },

    'limpeza da tabela de pcache' => function() use($conn) {
        __exec("
            DELETE FROM pcache p1 
                USING pcache p2 
            WHERE 
                p1.id > p2.id AND 
                p1.user_id = p2.user_id AND 
                p1.object_type = p2.object_type AND 
                p1.object_id = p2.object_id AND 
                p1.action = p2.action;");
    },

    'create trigger to update children opportunities' => function () {
        __exec("CREATE OR REPLACE FUNCTION fn_propagate_opportunity_update()
                    RETURNS TRIGGER
                    LANGUAGE plpgsql
                    COST 100
                    VOLATILE NOT LEAKPROOF AS $$
                    BEGIN
                        UPDATE opportunity
                        SET 
                            registration_ranges = NEW.registration_ranges,
                            registration_categories = NEW.registration_categories,
                            registration_proponent_types = NEW.registration_proponent_types
                        WHERE parent_id = OLD.id
                        AND (
                            registration_ranges::jsonb IS DISTINCT FROM NEW.registration_ranges::jsonb OR
                            registration_categories::jsonb IS DISTINCT FROM NEW.registration_categories::jsonb OR
                            registration_proponent_types::jsonb IS DISTINCT FROM NEW.registration_proponent_types::jsonb
                        );
                        RETURN NEW;
                    END; $$;");

        __try("CREATE TRIGGER trigger_propagate_opportunity_update
                    AFTER UPDATE ON opportunity
                    FOR EACH ROW
                    EXECUTE FUNCTION fn_propagate_opportunity_update()");
    },

    'recreate trigger to insert opportunity data to new children' => function () {
        __exec("CREATE OR REPLACE FUNCTION fn_propagate_opportunity_insert()
                    RETURNS TRIGGER
                    LANGUAGE plpgsql
                    COST 100
                    VOLATILE NOT LEAKPROOF AS $$
                    BEGIN
                        NEW.registration_ranges = (SELECT coalesce((SELECT registration_ranges FROM opportunity WHERE id = NEW.parent_id)::json, '[]'::json));
                        NEW.registration_categories = (SELECT coalesce((SELECT registration_categories FROM opportunity WHERE id = NEW.parent_id), '[]'));
                        NEW.registration_proponent_types = (SELECT coalesce((SELECT registration_proponent_types FROM opportunity WHERE id = NEW.parent_id)::json, '[]'::json));
                        RETURN NEW;
                    END; $$;");

        __try("CREATE TRIGGER trigger_propagate_opportunity_insert
                    BEFORE INSERT ON opportunity
                    FOR EACH ROW
                    EXECUTE FUNCTION fn_propagate_opportunity_insert()");
    },
    'renomeia metadados da funcionalidade AffirmativePollices' => function() use ($conn, $app) {
        $entity_metadada = [
            [
                'entity' => 'evaluationMethodConfiguration_meta',
                'old' => 'affirmativePolicies',
                'new' => 'pointReward'
            ],
            [
                'entity' => 'evaluationMethodConfiguration_meta',
                'old' => 'isActiveAffirmativePolicies',
                'new' => 'isActivePointReward'
            ],
            [
                'entity' => 'evaluationMethodConfiguration_meta',
                'old' => 'affirmativePoliciesRoof',
                'new' => 'pointRewardRoof'
            ],
            [
                'entity' => 'registration_meta',
                'old' => 'appliedAffirmativePolicy',
                'new' => 'appliedPointReward'
            ],
        ];

        foreach ($entity_metadada as $metadata) {
           
            $entity = trim($metadata['entity']);
            $old = trim($metadata['old']);
            $new = trim($metadata['new']);

            if($values = $conn->fetchAll("SELECT id FROM {$entity} WHERE key = '{$old}'")) {
                $total = count($values);
                foreach ($values as $key => $value) {
                    $_key = $key + 1;
                    $id = $value['id'];
                    __exec("UPDATE {$entity} SET key = '{$new}' WHERE id = {$id}");
    
                    $app->log->debug("{$_key} de {$total} - Metadado {$old} alterado para {$new} na entidade {$entity}");
                }
            } else {
                    $app->log->debug("Metadado {$old} não encontrado");
            }
        }
    },

    'corrige os valores da distribuição de avaliação por categorias - correção' => function() use ($conn, $app) {
        if($values = $conn->fetchAll("SELECT * FROM evaluationmethodconfiguration_meta WHERE key = 'fetchCategories'")) {
            
            foreach($values as $value) {
                if($fetchCategories = json_decode($value['value'], true)) {
                    $data = [];
                    $id = $value['id'];
                    $val_id = $value['object_id'];
                    $users = [];
                    foreach($fetchCategories as $user => $fetchCategorie ) {
                        if(!is_array($fetchCategorie)) {
                            $categories = explode(";",$fetchCategorie);
                        
                            $data[$user] = $categories;
                            
                            $_data = json_encode($data);
                            __exec("UPDATE evaluationmethodconfiguration_meta SET value = '{$_data}' WHERE id = {$id}");
                            $users[] = $app->repo("User")->find($user);
                            $app->log->debug("Campo fetchCategories atualizado na avaliação {$val_id}");
                        }
                       
                    }

                    $em = $app->repo('EvaluationMethodConfiguration')->find($value['object_id']);
                    $em->owner->enqueueToPCacheRecreation($users);
                    $app->em->clear();
                }
            }
        }
    },

    'adiciona índices nas tabelas de revisões de entidades' => function () {
        __try('CREATE INDEX entity_revision_object_type ON entity_revision (object_type)');
        __try('CREATE INDEX entity_revision_object_id ON entity_revision (object_id)');
        __try('CREATE INDEX entity_revision_object ON entity_revision (object_type, object_id)');

        __try('CREATE INDEX entity_revision_revision_data_data_id ON entity_revision_revision_data (revision_data_id)');
        __try('CREATE INDEX entity_revision_revision_data_revision_id ON entity_revision_revision_data (revision_id)');

        __try('CREATE INDEX entity_revision_data_id ON entity_revision_data (id)');
        __try('CREATE INDEX entity_revision_data_key ON entity_revision_data (key)');

    },

    'adiciona índice para a coluna action da tabela pcache' => function () {
        __try('CREATE INDEX pcache_action_idx ON pcache (action)');
    },

    'adiciona novos índices na tabela registration' => function () {
        __try('CREATE INDEX registration_number_idx ON registration (number)');
        __try('CREATE INDEX registration_category_idx ON registration (category)');
        __try('CREATE INDEX registration_range_idx ON registration (range)');
        __try('CREATE INDEX registration_proponent_type_idx ON registration (proponent_type)');
        __try('CREATE INDEX registration_status_idx ON registration (status)');
        __try('CREATE INDEX registration_score_idx ON registration (score)');
        __try('CREATE INDEX registration_eligible_idx ON registration (eligible)');
    },

    'adiciona novos índices na tabela file' => function () {
        __try('CREATE INDEX file_parent_idx ON file (parent_id)');
        __try('CREATE INDEX file_parent_object_type_idx ON file (parent_id, object_type)');
    },

    'deleta requests com valores dos da coluna metadata inválidos' => function() use($conn) {
        __exec("delete from request where metadata = ':metadata'");
    },
    
    "Renomeia a comissão de avaliação" => function () use($conn) {
        $name = i::__('Comissão de avaliação');
        $conn->executeQuery("
            UPDATE agent_relation 
            SET type = :type 
            WHERE 
                type = 'group-admin' AND 
                object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration'
        ", ['type' => $name]);
    },

    "Ajusta estrutura de avaliação para interpretar as comissões" => function () use ($conn) {
        $name = i::__('Comissão de avaliação');
        $conn->executeQuery("
            UPDATE registration
            SET valuers = (
                SELECT jsonb_object_agg(key, 
                    CASE value
                        WHEN 'group-admin' THEN :type
                        ELSE value
                    END
                )
                FROM jsonb_each_text(valuers)
            )
            WHERE valuers::text LIKE '%group-admin%'
        ", ['type' => $name]);

        $conn->executeQuery("UPDATE registration_evaluation set committee = :type WHERE committee = 'group-admin'", ['type' => $name]);
    },

    'Ajusta distribuição de avaliações caso nao exista regra de distribuição anteriormente definida' => function () use ($conn, $app) {
        $sql = "
        SELECT em.*
        FROM evaluationmethodconfiguration_meta em
        JOIN evaluation_method_configuration emc ON em.object_id = emc.id
        JOIN opportunity_meta o ON emc.opportunity_id = o.id
        WHERE em.key IN ('fetch', 'fetchCategories', 'fetchRanges', 'fetchProponentTypes')
          AND EXISTS (
              SELECT 1
              FROM agent_relation ar
              WHERE 
                  ar.object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration'
                  AND ar.object_id = emc.id
                  AND (ar.create_timestamp < (
                      SELECT exec_time 
                      FROM db_update 
                      WHERE name = 'Ajusta estrutura de avaliação para interpretar as comissões'
                      LIMIT 1
                  ) or ar.create_timestamp is null)
          )
          AND o.id IN (
              SELECT r.opportunity_id 
              FROM registration r 
              WHERE r.valuers_exceptions_list = '{\"exclude\": [], \"include\": []}'
          )";

        $rows = $conn->fetchAll($sql);

        if (!$rows) {
            return false;
        }

        // Agrupa os registros por object_id
        $dataByObject = [];
        foreach ($rows as $row) {
            $dataByObject[$row['object_id']][$row['key']] = $row['value'];
        }


        $isAllEmpty = function (array $keys): bool {
            foreach (['fetch', 'fetchCategories', 'fetchRanges', 'fetchProponentTypes'] as $key) {
                $json = $keys[$key] ?? null;

                if (!$json) {
                    continue;
                }
                $decoded = json_decode($json, true);

                if (is_array($decoded) && !empty(array_filter($decoded))) {
                    return false;
                }
            }
            return true;
        };

        $object_ids = [];
        foreach ($dataByObject as $object_id => $keys) {

            if ($isAllEmpty($keys)) {
                $object_ids[] = $object_id;
            }
        }

        foreach ($object_ids as $object_id) {
            $committee = $conn->fetchAll("
            SELECT 
                a.user_id 
            FROM 
                agent_relation ar 
            LEFT JOIN agent a ON a.id = ar.agent_id
            WHERE 
                ar.object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration' AND ar.object_id = {$object_id}");

            if (!$committee) {
                continue;
            }

            $data = [];
            foreach ($committee as $val) {
                $data[$val['user_id']] = "00-99";
            }

            $jsonData = json_encode($data);

            $conn->executeQuery("UPDATE evaluationmethodconfiguration_meta SET value = '{$jsonData}' WHERE key = 'fetch' AND object_id = {$object_id}");
        }

        return false;
    },

    'Ajusta distribuição de avaliações caso nao exista regra de distribuição anteriormente definida Parte 2' => function () use ($conn, $app) {
        $sql = "
            SELECT emm.*
            FROM evaluationmethodconfiguration_meta emm
            JOIN evaluation_method_configuration emc ON emm.object_id = emc.id
            JOIN opportunity_meta o ON emc.opportunity_id = o.id
            WHERE 
                emm.object_id NOT in (
                    SELECT emm2.object_id
                    FROM evaluationmethodconfiguration_meta emm2
                    where emm2.key IN ('fetch', 'fetchCategories', 'fetchRanges', 'fetchProponentTypes')
                )
                AND o.id IN (
                    SELECT r.opportunity_id 
                    FROM registration r 
                    WHERE r.valuers_exceptions_list = '{\"exclude\": [], \"include\": []}'
                )";

        $rows = $conn->fetchAll($sql);

        if (!$rows) {
            return false;
        }

        $dataByObject = [];
        foreach ($rows as $row) {
            $dataByObject[$row['object_id']] = $row['object_id'];
        }

        foreach ($dataByObject as $object_id) {
            $committee = $conn->fetchAll("
            SELECT 
                a.user_id 
            FROM 
                agent_relation ar 
            LEFT JOIN agent a ON a.id = ar.agent_id
            WHERE 
                ar.object_type = 'MapasCulturais\Entities\EvaluationMethodConfiguration' AND ar.object_id = {$object_id}");


            if (!$committee) {
                continue;
            }

            $data = [];
            foreach ($committee as $val) {
                $data[$val['user_id']] = "00-99";
            }

            $jsonData = json_encode($data);
            $insert = "INSERT INTO evaluationmethodconfiguration_meta (object_id, key, value) VALUES ({$object_id}, 'fetch', '{$jsonData}' )";
            $conn->executeQuery($insert);
        }
    },

    'Limpa entradas duplicadas na tabela pcache e cria novos indices' => function() use($conn) {
        __exec("DELETE 
                FROM 
                    pcache T1
                USING 
                    pcache T2 
                WHERE 
                    T1.id < T2.id AND
                    T1.object_type = T2.object_type AND
                    T1.object_id = T2.object_id AND
                    T1.action = T2.action AND
                    T1.user_id = T2.user_id
        ");

        __exec("CREATE UNIQUE INDEX unique_object_action ON pcache (object_type, object_id, action, user_id)");
    },

    'Atualiza coluna parent_id do agente com id do agente principal' => function(){
        __exec("UPDATE agent SET parent_id = (SELECT profile_id FROM usr WHERE id = agent.user_id AND profile_id <> agent.id)");
    },

    'Apaga entradas duplicadas na tabela de avaliação e cria indice unique para a avaliação vs avaliador' => function(){

        __exec("DELETE 
                FROM 
                    registration_evaluation T1
                USING 
                    registration_evaluation T2 
                WHERE 
                    T1.id < T2.id AND
                    T1.registration_id = T2.registration_id AND
                    T1.user_id = T2.user_id
        ");

        __exec("CREATE UNIQUE INDEX unique_evaluation_user_id ON registration_evaluation (registration_id, user_id)");
    },

    'cria novos índices em diversas tabelas ' => function() {
        __exec('CREATE INDEX idx_usr_profile ON usr (profile_id);');
        __exec('CREATE INDEX id_agent_relation_agent ON agent_relation (agent_id);');
        __exec('CREATE INDEX idx_space_agent_id ON space (agent_id);');
        __exec('CREATE INDEX idx_event_agent_id ON event (agent_id);');
        __exec('CREATE INDEX idx_seal_relation_agent_id ON seal_relation (agent_id);');
        __exec('CREATE INDEX idx_seal_relation_owner_id ON seal_relation (owner_id);');
        __exec('CREATE INDEX idx_seal_relation_object ON seal_relation (object_type, object_id);');
        __exec('CREATE INDEX idx_project_agent_id ON project (agent_id);');
        __exec('CREATE INDEX idx_project_type ON project (type);');
        __exec('CREATE INDEX idx_registration_meta_key ON registration_meta (key);');
        __exec('CREATE INDEX idx_opportunity_meta_key ON registration_meta (key);');
        __exec('CREATE INDEX idx_agent_usr ON agent (user_id);');
    },

    'Adiciona novas áreas de atuação' => function() {
        __try("
        WITH areas_novas(name) AS (
            VALUES ('Acervos'), ('Acessibilidade Cultural'), ('Agrofloresta'), ('Animação'), ('Áreas Técnicas'), ('Arte Gráfica'), ('Arte Santeira'), ('Artesanato com Reciclados'), ('Artesanato em Cerâmica'), ('Artesanato em Fibra Vegetal'), ('Artesanato em Fio'), ('Artesanato em Madeira'), ('Artesanato em Metal'), ('Artesanato em Pedra'), ('Artesanato em Tecido'), ('Audiolivro'), ('Audiovisual Expandido'), ('Axé'), ('Baião'), ('Baião (dança)'), ('Ballet'), ('Banda Sinfônica'), ('Bandas de Congo e Ticumbi'), ('Biblioteca tombada'), ('Biblioteconomia'), ('Bioconstrução'), ('Biografia e Autobiografia'), ('Bloco de Carnaval'), ('Boi Bumbá'), ('Bordado'), ('Breakdance'), ('Brega Funk'), ('Caboclinho'), ('Caçada da Rainha'), ('Calypso'), ('Canto'), ('Canto Coral'), ('Carimbó'), ('Carimbó (Dança)'), ('Cavalhadas'), ('Cavalo Marinho'), ('Centro de Memória e patrimônio'), ('Cinemateca'), ('Ciranda'), ('Circo Contemporâneo'), ('Circo de Rua'), ('Circo Itinerante'), ('Circo Tradicional'), ('Coco'), ('Comédia'), ('Congada'), ('Congado'), ('Contação de histórias'), ('Conteúdo Audiovisual por Demanda'), ('Conto'), ('Cordel'), ('Criação literária'), ('Crochê'), ('Crônica'), ('Cultivo e extração tradicional'), ('Cultura Alemã'), ('Cultura Alimentar'), ('Cultura da juventude de povos e comunidades tradicionais'), ('Cultura das comunidades de fundo e fecho de pasto'), ('Cultura das comunidades quilombolas'), ('Cultura das quebradeiras de coco babaçu'), ('Cultura DEF'), ('Cultura do Povo Arara'), ('Cultura do Povo Araweté'), ('Cultura do Povo Ashaninka'), ('Cultura do Povo Bororo'), ('Cultura do Povo Cultura Fulni-ô'), ('Cultura do Povo Enawenê-Nawê'), ('Cultura do Povo Gavião'), ('Cultura do Povo Guarani'), ('Cultura do Povo Ikpeng'), ('Cultura do Povo Javari'), ('Cultura do Povo Kalapalo'), ('Cultura do Povo Kanamari'), ('Cultura do Povo Karajá'), ('Cultura do Povo Karipuna'), ('Cultura do Povo Kaxinawá (Huni Kuin)'), ('Cultura do Povo Kayabi'), ('Cultura do Povo Kayapó'), ('Cultura do Povo Korubo'), ('Cultura do Povo Krahô'), ('Cultura do Povo Maxakali'), ('Cultura do Povo Munduruku'), ('Cultura do Povo Ofaié'), ('Cultura do Povo Panará'), ('Cultura do Povo Pankararu'), ('Cultura do Povo Pareci'), ('Cultura do Povo Paresí'), ('Cultura do Povo Pataxó'), ('Cultura do povo pomerano'), ('Cultura do Povo Suruí'), ('Cultura do Povo Tembé'), ('Cultura do Povo Terena'), ('Cultura do Povo Tikuna'), ('Cultura do Povo Timbira'), ('Cultura do Povo Tukano'), ('Cultura do Povo Tupi'), ('Cultura do Povo Wai Wai'), ('Cultura do Povo Waimiri-Atroari'), ('Cultura do Povo Xavante'), ('Cultura do Povo Xerente'), ('Cultura do Povo Xikrin'), ('Cultura do Povo Yanomami'), ('Cultura do Povo Yawalapiti'), ('Cultura do Povo Yawanawá'), ('Cultura do Povo Zoró'), ('Cultura dos andirobeiros'), ('Cultura dos apanhadores de flores sempre vivas'), ('Cultura dos benzedeiros'), ('Cultura dos caatingueiros'), ('Cultura dos caboclos'), ('Cultura dos caiçaras'), ('Cultura dos catadores de mangaba'), ('Cultura dos cipozeiros'), ('Cultura dos extrativistas'), ('Cultura dos extrativistas costeiros e marinhos'), ('Cultura dos faxinalenses'), ('Cultura dos geraizeiros'), ('Cultura dos ilhéus'), ('Cultura dos morroquianos'), ('Cultura dos pantaneiros'), ('Cultura dos pescadores artesanais'), ('Cultura dos povos ciganos'), ('Cultura dos povos e comunidades de terreiro/povos e comunidades de matriz africana'), ('Cultura dos Povos Nômades'), ('Cultura dos raizeiros'), ('Cultura dos retireiros do Araguaia'), ('Cultura dos ribeirinhos'), ('Cultura dos vazanteiros'), ('Cultura dos veredeiros'), ('Cultura e Bem Viver'), ('Cultura e Comunicação'), ('Cultura e decolonialidade'), ('Cultura e Desenvolvimento Social'), ('Cultura e Direitos Humanos'), ('Cultura e Juventudes'), ('Cultura e Lazer'), ('Cultura e Política'), ('Cultura e Território'), ('Cultura Hip Hop'), ('Cultura Italiana'), ('Cultura Japonesa'), ('Cultura Urbana'), ('Cultura, Infância e Adolescência'), ('Culturas Afrobrasileiras'), ('Culturas dos Povos Originários'), ('Culturas Tradicionais'), ('Culturas Tradicionais e Populares'), ('Dança Afro'), ('Dança Contemporânea'), ('Dança de Salão'), ('Dança do Ventre'), ('Dança Moderna'), ('Dança Silvestre'), ('Danças Clássicas'), ('Danças Contemporâneas'), ('Danças Estrangeiras'), ('Danças Populares'), ('Danças Urbanas'), ('Declamação'), ('Desenho Industrial'), ('Design de Interiores'), ('Design de Jóias'), ('Design e Serviços Criativos'), ('Design Gráfico'), ('Design Paisagístico'), ('Design para a Sociobioeconomia'), ('Diário'), ('Direito'), ('Documentário'), ('Drama'), ('Economia, Produção e Áreas Técnicas da Cultura'), ('Ensaios'), ('Epístola'), ('Epopeia'), ('Escola de Samba'), ('Escultura (Artes Visuais)'), ('Escultura (Artesanato)'), ('Espetáculo de Circo'), ('Fábula'), ('Fandango'), ('Fanfarra'), ('Festa do Divino'), ('Festas Populares'), ('Festejos Juninos'), ('Ficção'), ('Filme-ensaio'), ('Filologia'), ('Folia de Reis'), ('Fomento editorial'), ('Forró'), ('Forró (Dança)'), ('Frevo'), ('Frevo (dança)'), ('Funk'), ('Fuxico'), ('Geografia Humana'), ('Gestão criativa'), ('Grafite'), ('Gravura'), ('Guarânia'), ('Humanidades'), ('Imóvel tombado'), ('Internet Podcasting'), ('Interseccionalidades'), ('Intervenção Urbana'), ('Jazz (Dança)'), ('Jongo'), ('K-pop Dance'), ('Lambada'), ('Lapidação'), ('Lenda'), ('Letras e Literatura (Humanidades)'), ('Lidas Campeiras'), ('Linguística'), ('Literatura Infantil'), ('Livro, Leitura e Literatura'), ('Macramê'), ('Maracatu'), ('Maracatu (Dança)'), ('Marujada'), ('Mediação de Leitura'), ('Memórias'), ('Mito'), ('Moda de Viola'), ('Mosaico'), ('Movimento Sound System'), ('Museologia'), ('Museu tombado'), ('Música de Câmara'), ('Música de Concerto'), ('Música Eletrônica'), ('Música Instrumental'), ('Música Popular'), ('Musical'), ('Novela'), ('Obra Seriada'), ('Oktober Fest'), ('Orquestra Sinfônica'), ('Ourivesaria'), ('Outras Danças'), ('Outras Danças Clássicas'), ('Outras Danças Modernas'), ('Outras Danças Populares'), ('Paisagens Culturais'), ('Patrimônio Histórico Edificado'), ('Performance'), ('Performance Literária'), ('Permacultura'), ('Pintura'), ('Poesia'), ('Políticas e Gestão Culturais'), ('Procissão do Fogaréu'), ('Produção Audiovisual'), ('Produção de Eventos'), ('Psicologia'), ('Punk'), ('Rádio Comunitária'), ('Rádio e TV'), ('Rap'), ('Rasqueado'), ('Realidade Virtual'), ('Reggae'), ('Reisado'), ('Renda'), ('Renda de Bilro'), ('Repente'), ('Rima e improviso'), ('Rock'), ('Romance'), ('Romaria do Divino Pai Eterno'), ('Salsa (Dança)'), ('Salvaguarda do Patrimônio Cultural Imaterial'), ('Samba'), ('Samba (Dança)'), ('Samba de Roda'), ('Sapateado'), ('Sátira'), ('Sertanejo'), ('Sítio Arqueológico'), ('Sítios Históricos e Arqueológicos'), ('Slam'), ('Stand-up Comedy'), ('Street Jazz'), ('Tambor de Crioula'), ('Tango (Dança)'), ('Teatro de Bonecos'), ('Teatro de Improviso'), ('Teatro de Máscaras'), ('Teatro de Rua'), ('Teatro de Sombras'), ('Teatro do Absurdo'), ('Teatro do Oprimido'), ('Teatro Experimental'), ('Teatro Infantil'), ('Tecelagem'), ('Tecnobrega'), ('Teologia'), ('Tradução e Interpretação'), ('Tragédia'), ('Trançagem'), ('Transversalidades'), ('Vídeo'), ('Vídeo Experimental'), ('Vídeo Performance'), ('Vídeo por demanda'), ('Video Teatro'), ('Videoarte'), ('Videocast'), ('Videoclipe'), ('Videodança'), ('Webdesign'), ('Websérie'), ('Xaxado'), ('Xilogravura'), ('Mídias Interativas')
        )
        INSERT INTO term(id, taxonomy, term)
        SELECT nextval('term_id_seq') as id,
                'area' as taxonomy,
                areas_novas.name as term
            FROM areas_novas;
            ");
    },
    'Atualizando áreas de atuação que tiveram alteração no nome' => function() {
        __try("UPDATE term SET term = 'Arquitetura e Urbanismo' WHERE taxonomy = 'area' AND term = 'Arquitetura-Urbanismo';");
        __try("UPDATE term SET term = 'Arquivos' WHERE taxonomy = 'area' AND term = 'Arquivo';");
        __try("UPDATE term SET term = 'Audiovisual e Mídias Interativas' WHERE taxonomy = 'area' AND term = 'Audiovisual';");
        __try("UPDATE term SET term = 'Cultura dos Povos Originários' WHERE taxonomy = 'area' AND term = 'Cultura Indígena';");
        __try("UPDATE term SET term = 'Cultura e Educação' WHERE taxonomy = 'area' AND term = 'Educação';");
        __try("UPDATE term SET term = 'Cultura e Esporte' WHERE taxonomy = 'area' AND term = 'Esporte';");
        __try("UPDATE term SET term = 'Cultura e Saúde' WHERE taxonomy = 'area' AND term = 'Saúde';");
        __try("UPDATE term SET term = 'Cultura e Turismo' WHERE taxonomy = 'area' AND term = 'Turismo';");
        __try("UPDATE term SET term = 'Cultura LGBTQIAPN+' WHERE taxonomy = 'area' AND term = 'Cultura LGBT';");
        __try("UPDATE term SET term = 'Cultura, Meio Ambiente e Sustentabilidade' WHERE taxonomy = 'area' AND term = 'Meio Ambiente';");
        __try("UPDATE term SET term = 'Culturas Populares' WHERE taxonomy = 'area' AND term = 'Cultura Popular';");
        __try("UPDATE term SET term = 'Design de Moda' WHERE taxonomy = 'area' AND term = 'Moda';");
        __try("UPDATE term SET term = 'Economia Criativa e da Cultura' WHERE taxonomy = 'area' AND term = 'Economia Criativa';");
        __try("UPDATE term SET term = 'Jogos eletrônicos/Games' WHERE taxonomy = 'area' AND term = 'Jogos Eletrônicos';");
        __try("UPDATE term SET term = 'Jornais e outros periódicos' WHERE taxonomy = 'area' AND term = 'Jornalismo';");
        __try("UPDATE term SET term = 'Museu (Patrimônio Material)' WHERE taxonomy = 'area' AND term = 'Museu';");
        __try("UPDATE term SET term = 'Patrimônio Cultural Imaterial' WHERE taxonomy = 'area' AND term = 'Patrimônio Imaterial';");
        __try("UPDATE term SET term = 'Patrimônio Cultural Material' WHERE taxonomy = 'area' AND term = 'Patrimônio Material';");
        __try("UPDATE term SET term = 'Outra' WHERE taxonomy = 'area' AND term = 'Outros';");
    },
    'Adiciona novas funções' => function() {
        __try("
        WITH novas_funcoes(name) AS (
            values ('Acrobata'), ('Adestrador circense'), ('Animador'), ('Antipodista'), ('Apresentador de Eventos'), ('Apresentador de Festas Populares'), ('Apresentador de Programas de Televisão'), ('Apresentador/Animador'), ('Aramista'), ('Arqueólogo(a)'), ('Arquiteto(a) e Urbanista'), ('Arranjador'), ('Arte-Finalista'), ('Artesão com Material Reciclável'), ('Artesão Confeccionador de Biojóias e Ecojóias'), ('Artesão Crocheteiro'), ('Artesão do Couro'), ('Artesão Modelador'), ('Artesão Moveleiro (exceto reciclado)'), ('Artesão Rendeiro'), ('Artesão Tecelão'), ('Artesão Trançador'), ('Artesão Tricoteiro'), ('Artesão(ã)'), ('Artista'), ('Artista Aéreo'), ('Artista Circense'), ('Artista de Rua'), ('Artista Urbano'), ('Artista Visual'), ('Assessor de imprensa'), ('Assistente de comunicação'), ('Assistente de direção'), ('Atendente Artístico-Cultural'), ('Atirador de Facas'), ('Ator/Atriz'), ('Avaliador de Joias'), ('Baianas do Acarajé'), ('Bailarino(a)'), ('Bate-folha a máquina'), ('Benzedeiro(a)/Rezadeiro(a)'), ('Bibliotecário'), ('Bonequeiro(a)'), ('Brincante'), ('Cantor'), ('Capoeirista'), ('Caricaturista'), ('Carnavalesco(a)'), ('Cartunista'), ('Ceramista'), ('Chargista'), ('Chef/Cozinheiro(a)'), ('Colecionador'), ('Comedor de Espada'), ('Comedor de Fogo (pirofagista)'), ('Cômico de Circo'), ('Compositor'), ('Comunicador'), ('Confeccionador de Acordeão'), ('Confeccionador de Instrumentos de Corda'), ('Confeccionador de Instrumentos de Percussão'), ('Confeccionador de Instrumentos de Sopro (madeira)'), ('Confeccionador de Instrumentos de Sopro (metal)'), ('Confeccionador de Órgão'), ('Confeccionador de Piano'), ('Conservador/Restaurador'), ('Consultor(a) cultural'), ('Contador(a) de Estórias/Histórias'), ('Contorcionista'), ('Contra-regra'), ('Coreógrafo'), ('Cravador/Cravejador de Joias'), ('Criador(a) de Conteúdos Criativos Digitais'), ('Curador(a)'), ('Dançarino de Rua'), ('Dançarino de Salão'), ('Dançarino Popular, de Danças Folclóricas, Parafolclóricas, Tradicionais ou de Raiz'), ('Dançarino(a)'), ('Decorador de Eventos'), ('Desenhista'), ('Desenhista de Croqui'), ('Desenhista Detalhista'), ('Desenhista Técnico'), ('Desenhista Técnico da Indústria Têxtil'), ('Desenhista Técnico de Embalagens, Maquetes e Leiautes'), ('Desenhista Técnico de Mobiliário'), ('Desenvolvedor(a) de Jogos Eletrônicos'), ('Desenvolvedor(a) de Sistemas Informacionais'), ('Desenvolvedor(a) de Software'), ('Designer de Brinquedos'), ('Designer de Interiores'), ('Designer de Jóias'), ('Designer de Moda'), ('Designer de Som'), ('Designer Gráfico'), ('Designer Paisagístico'), ('Diretor Artístico'), ('Diretor de Arte'), ('Diretor Musical'), ('Dj (disc jockey)'), ('Domador de Animais'), ('Dramaturgo(a)'), ('Dublador(a)'), ('Editor de Texto e Imagem'), ('Editor(a) de Livros'), ('Editor(a) de TV e Vídeo'), ('Educador Museal'), ('Educador Social'), ('Educador(a) artistico-cultural'), ('Enólogo(a)'), ('Equilibrista'), ('Escritor'), ('Escultor'), ('Facilitador'), ('Faquir'), ('Finalizador'), ('Fotógrafo'), ('Fotógrafo Lambe-Lambe'), ('Funâmbulo'), ('Fundidor (joalheria e ourivesaria)'), ('Galerista'), ('Gambista'), ('Gestor Cultural'), ('Gestor de Redes Sociais'), ('Gestor(a) de Inovação'), ('Globista'), ('Grafiteiro(a)'), ('Gravador de Joias'), ('Gravador, à mão (encadernação)'), ('Guia de Turismo'), ('Hacker'), ('Homem-bala'), ('Icarista'), ('Ilustrador(a)'), ('Instrumentista'), ('Instrutor(a)'), ('Intérprete'), ('Inventor'), ('Joalheiro (reparações)'), ('Jornalista'), ('Laminador de metais preciosos a mão'), ('Lapidador de Jóias'), ('Maestro'), ('Mágico'), ('Malabarista'), ('Maquetista'), ('Maquetista na Marcenaria'), ('Maquinista de Cinema e Vídeo'), ('Maquinista de Teatro e Espetáculos'), ('Marceneiro'), ('Marcheteiro'), ('Mestre da cultura afro-brasileira'), ('Mestre da Cultura Popular'), ('Mestre da Cultura Tradicional'), ('Mestre de cultura ayahuasqueira'), ('Mestre de cultura indígena'), ('Mestre Vidreiro'), ('Mestre-sala'), ('Mestre(a) de bateria'), ('Mestres artífices'), ('Microfonista'), ('Mímico'), ('Miniaturista'), ('Modelador de Madeira'), ('Modelista de Calçados'), ('Modelista de Roupas'), ('Monociclista de circo'), ('Multiplicador'), ('Muralista'), ('Museólogo(a)'), ('Oficineiro(a)'), ('Operador de Central de Rádio'), ('Operador de Microfone (boom man)'), ('Operador de Projetor Cinematográfico'), ('Operador de Transmissor de Rádio'), ('Operador(a) de Áudio de Continuidade (rádio)'), ('Operador(a) de Câmera'), ('Operador(a) de Controle Mestre (rádio)'), ('Operador(a) de Externa'), ('Operador(a) de Gravação de Rádio'), ('Palhaço/Clown'), ('Paneleiro(a)'), ('Parteira Indígena'), ('Parteiro(a)'), ('Passista'), ('Patinador(a)'), ('Perna de Pau (ou Pernalta)'), ('Pesquisador(a)'), ('Pirofagista'), ('Poeta'), ('Poeta de Slam'), ('Porta-bandeira/Porta-estandarte'), ('Produtor Audiovisual'), ('Produtor Musical'), ('Produtor(a) Cultural'), ('Professor'), ('Profissional da crítica'), ('Programador(a)'), ('Projecionista'), ('Projetista de Móveis'), ('Projetista de Sistemas de Áudio'), ('Quadrilheiro(a)'), ('Quadrinista'), ('Queijista Artesanal'), ('Rapper'), ('Redator(a)'), ('Rendeiro(a)'), ('Repórter'), ('Restaurador de Livros e Documentos'), ('Restaurador(a)'), ('Revisor(a)'), ('Ritmista'), ('Roteirista'), ('Rumbeira de Circo'), ('Sacerdote de religiosidade popular'), ('Saltimbanco'), ('Sambista'), ('Sineiro'), ('Tacacazeiro(a)'), ('Tanoeiro(a)'), ('Tecelão(ã)'), ('Técnico (a) em biblioteconomia'), ('Técnico em Masterização de Áudio'), ('Técnico em Mixagem de Áudio'), ('Técnico em Operação de Equipamentos de Transmissão/Recepção de Televisão'), ('Técnico(a) audiovisual'), ('Técnico(a) de arquivo'), ('Técnico(a) de sonorização'), ('Técnico(a) em Cenografia'), ('Técnico(a) em Gravação de Áudio'), ('Técnico(a) em Instalação de Equipamentos de Áudio'), ('Técnico(a) em Museologia'), ('Titeriteiro'), ('Trabalhador de Confecções de Bijuterias e Jóias de Fantasia'), ('Tradutor(a)'), ('Trapezista'), ('Trefilador (joalheria e ourivesaria)'), ('Turismólogo(a)'), ('Ventríloquo'), ('Violeiro'), ('VJ'), ('Web Designer')
        )
        INSERT INTO term(id, taxonomy, term)
        SELECT nextval('term_id_seq') AS id,
                'funcao' AS taxonomy,
                novas_funcoes.name AS term
            FROM novas_funcoes;
        ");
    },
    'Atualizando as funções que tiveram alterações no nome' => function() {
        __try("UPDATE term SET term = 'Afinador de Instrumentos Musicais' WHERE taxonomy = 'funcao' AND term = 'Afinador de Instrumentos';");
        __try("UPDATE term SET term = 'Apresentador de Programas de Rádio' WHERE taxonomy = 'funcao' AND term = 'Radialista';");
        __try("UPDATE term SET term = 'Assistente de palco' WHERE taxonomy = 'funcao' AND term = 'Técnico(a) de Palco';");
        __try("UPDATE term SET term = 'Assistente de produção' WHERE taxonomy = 'funcao' AND term = 'Assistente de Produção em geral';");
        __try("UPDATE term SET term = 'Cenotécnico (cinema, vídeo, televisão, teatro e espetáculos)' WHERE taxonomy = 'funcao' AND term = 'Cenotécnico(a)';");
        __try("UPDATE term SET term = 'Designer de Vitrines (Vitrinista)' WHERE taxonomy = 'funcao' AND term = 'Vitrinista';");
        __try("UPDATE term SET term = 'Engenheiro de Som' WHERE taxonomy = 'funcao' AND term = 'Engenheiro(a) de Som';");
        __try("UPDATE term SET term = 'Ensaiador' WHERE taxonomy = 'funcao' AND term = 'Ensaiador(a)';");
        __try("UPDATE term SET term = 'Mediador' WHERE taxonomy = 'funcao' AND term = 'Mediador(a)';");
        __try("UPDATE term SET term = 'Mestre de Pista' WHERE taxonomy = 'funcao' AND term = 'Mestre(a) de Pista';");
        __try("UPDATE term SET term = 'Montador' WHERE taxonomy = 'funcao' AND term = 'Montador(a)';");
        __try("UPDATE term SET term = 'Montador de Palco' WHERE taxonomy = 'funcao' AND term = 'Montador(a) de Palco';");
        __try("UPDATE term SET term = 'Músico/Musicista' WHERE taxonomy = 'funcao' AND term = 'Músico-Musicista';");
        __try("UPDATE term SET term = 'Operador de Som' WHERE taxonomy = 'funcao' AND term = 'Operador(a) de Som';");
        __try("UPDATE term SET term = 'Secretário de Frente' WHERE taxonomy = 'funcao' AND term = 'Secretario(a) de Frente';");
        __try("UPDATE term SET term = 'Outra' WHERE taxonomy = 'funcao' AND term = 'Outra função técnica';");
    },
    'Atualizando a função Maquiador para Maquiador(a)' => function() {
        __try("UPDATE term_relation SET term_id = 500041 WHERE term_id = 500040;");
        __try("DELETE FROM term WHERE id = 500040;");
    },
    'Atualizando a função Técnico de Luz|Técnico(a) de Luz para Técnico(a) de Iluminação' => function() {
        __try("UPDATE term_relation SET term_id = 500412 WHERE term_id = 500284");
        __try("DELETE FROM term WHERE id = 500284;");
        __try("UPDATE term SET term = 'Técnico(a) de iluminação' WHERE id = 500412;");
    },
    'Atualizando a função de Iluminador para Iluminador(a)' => function() {
        __try("UPDATE term_relation SET term_id = 500039 WHERE term_id = 500037;");
        __try("DELETE FROM term WHERE id = 500037;");
    },
    "Adiciona as Etnias" => function() {
        __try("
        WITH etnias(name) AS (
            VALUES ('Aikanã'), ('Aikewara'), ('Akuntsu'), ('Amanayé'), ('Amondawa'), ('Anacé'), ('Anambé'), ('Aparai'), ('Apiaká'), ('Apinayé'), ('Apurinã'), ('Aranã'), ('Arapaso'), ('Arapium'), ('Arara'), ('AraradaVoltaGrandedoXingu'), ('AraradoRioAmônia'), ('AraradoRioBranco'), ('AraraShawãdawa'), ('Araweté'), ('Arikapú'), ('Aruá'), ('Ashaninka'), ('AsurinidoTocantins'), ('AsurinidoXingu'), ('Atikum'), ('Avá-Canoeiro'), ('AwaGuajá'), ('Aweti'), ('Bakairi'), ('Banawá'), ('Baniwa'), ('Bará'), ('Barasana'), ('Baré'), ('Baré(Boe)'), ('Borari'), ('Bororo'), ('CanelaApanyekrá'), ('CanelaMemortumré'), ('Chiquitano'), ('Cintalarga'), ('Coripaco'), ('Dâw'), ('Deni'), ('Desana'), ('Djeoromitxí'), ('Dow'), ('Enawenê-nawê'), ('Fulni-ô'), ('GalibiKa''lina'), ('Galibi-Marworno'), ('Gamela'), ('GaviãoAkrãtikatêjê'), ('GaviãoKykatejê'), ('GaviãoParkatêjê'), ('GaviãoPykopjê'), ('Guajajara'), ('Guarani'), ('Guató'), ('Hixkaryana'), ('HuniKuin'), ('Hupda'), ('HupdaIkolenIkpengIngarikóIny'), ('Jamamadi'), ('Jaraqui'), ('Jarawara'), ('Javaé'), ('Jenipapo-Kanindé'), ('Jiahui'), ('Jiripancó'), ('Juma'), ('Ka''apor'), ('Kadiwéu'), ('Kaimbé'), ('Kaingang'), ('Kaiowá'), ('Kaixana'), ('Kakwa'), ('Kalankó'), ('Kalapalo'), ('Kamaiurá'), ('Kambeba'), ('Kambiwá'), ('Kanamari'), ('Kanoê'), ('Kantaruré'), ('Kapinawa'), ('KarajádoNorte'), ('KarajáIranxe'), ('Karapanã'), ('Karapotó'), ('KaripunadeRondônia'), ('KaripunadoAmapá'), ('Kariri-Xokó'), ('Karitiana'), ('Karo'), ('Kassupá'), ('Katuenayana'), ('KatukinadoRioBiá'), ('KatukinaPano'), ('Katxuyana'), ('Kawaiwete(Kaiabi)'), ('Kaxarari'), ('Kaxixó'), ('Kinikinau'), ('Kiriri'), ('Kisêdjê'), ('Kokama'), ('Koripako'), ('Korubo'), ('Kotiria'), ('Krahô'), ('Krahô-Kanela'), ('Krenak'), ('Krenyê'), ('Krikatí'), ('Kubeo'), ('Kuikuro'), ('Kujubim'), ('Kulina'), ('KulinaPano'), ('Kuruaya'), ('Kwazá'), ('Macuxi'), ('Makuna'), ('Makurap'), ('Manchineri'), ('Manoki'), ('Marubo'), ('Matipu'), ('Matis'), ('Matsés'), ('Maxakali'), ('Mbya'), ('MebengôkreKayapó'), ('Mehinako'), ('MenkyManoki'), ('Migueleno'), ('Miranha'), ('Mirity-tapuya'), ('Mukurin'), ('Munduruku'), ('Mura'), ('Nadöb'), ('Nahukuá'), ('Nambikwara'), ('Ñandeva'), ('Naruvotu'), ('Nawa'), ('Nukak'), ('Nukini'), ('Ofaié'), ('OroWin'), ('Palikur'), ('Panará'), ('Pankaiuká'), ('Pankará'), ('Pankararé'), ('Pankararu'), ('Pankaru'), ('Parakanã'), ('Paresí'), ('Parintintin'), ('Patamona'), ('Pataxó'), ('PataxóHã-Hã-Hãe'), ('Paumari'), ('PipipãPira-tapuya'), ('Pirahã'), ('Pira-tapuya'), ('Pitaguary'), ('Potiguara'), ('Puruborá'), ('Puyanawa'), ('Rikbaktsa'), ('Sakurabiat'), ('SateréMawé'), ('Shanenawa'), ('Siriano'), ('SuruiPaiter'), ('Suruwaha'), ('Tabajara'), ('Taiwano'), ('Tapajó'), ('Tapayuna'), ('Tapeba'), ('Tapirapé'), ('Tapuia'), ('Tariana'), ('Tatuyo'), ('Taurepang'), ('Tembé'), ('Tenharim'), ('Terena'), ('Ticuna'), ('TinguiBotó'), ('Tiriyó'), ('Torá'), ('Tremembé'), ('Truká'), ('Trumai'), ('Tsohom-dyapa'), ('Tukano'), ('Tumbalalá'), ('Tunayana'), ('Tupari'), ('Tupinambá'), ('Tupiniquim'), ('Turiwara'), ('Tuxá'), ('Tuyuka'), ('Umutina'), ('Uru-Eu-Wau-Wau'), ('WaimiriAtroari'), ('Waiwai'), ('Wajãpi'), ('Wajuru'), ('Wapichana'), ('Warekena'), ('Wari''Wassu'), ('Wauja'), ('Wayana'), ('Witoto'), ('Xakriabá'), ('Xavante'), ('Xerente'), ('Xetá'), ('Xikrin(Mebengôkre)'), ('Xingu'), ('Xipaya'), ('Xokleng'), ('Xokó'), ('Xukuru'), ('Xukuru-Kariri'), ('Yaminawá'), ('Yanomami'), ('Yawalapiti'), ('Yawanawá'), ('Ye''kwana'), ('Yudja'), ('Yuhupde'), ('Yuruti'), ('Zo''é'), ('Zoró'), ('Outra')
        )
        INSERT INTO term(id, taxonomy, term)
             SELECT nextval('term_id_seq') AS id,
                    'etnia' AS taxonomy,
                    etnias.name AS term
               FROM etnias;
        ");
    },
    
    'atualizar o type para continuous onde o type for appeal-phase' => function() {
        __exec("UPDATE evaluation_method_configuration SET type = 'continuous' WHERE type = 'appeal-phase';");
    },

    'refatoração dos índices da tabela pcache' => function () {
        __exec('CREATE INDEX pcache_object_user_action_idx ON pcache (user_id, object_type, action)');

        // remove índice duplicado
        // "pcache_permission_user_idx" btree (object_type, object_id, action, user_id)
        // "unique_object_action" UNIQUE, btree (object_type, object_id, action, user_id)
        __exec('DROP INDEX pcache_permission_user_idx');
    },

    'remove entradas da tabela pcache não mais utilizadas' => function () {
        __exec("
            DELETE FROM pcache 
            WHERE action NOT IN (
                '@control',
                'modify',
                'view',
                'applySeal',
                'support',
                'viewUserEvaluation',
                'evaluateOnTime',
                'createEvents',
                'requestEventRelation');");
    },
    
    "Removendo os campos e anexos de formulário erroneamente duplicados pela funcionalidade 'Duplicar Oportunidade'" => function() {
        __try("DELETE FROM registration_field_configuration rfc
                     USING registration_step rs
                     WHERE rs.id = rfc.step_id
                       AND rs.opportunity_id != rfc.opportunity_id;");

        __try("DELETE FROM registration_file_configuration rfc
                     USING registration_step rs
                     WHERE rs.id = rfc.step_id
                       AND rs.opportunity_id != rfc.opportunity_id;");
    },

    'define valores default para as colunas ids das tabelas sem default' => function() {
        __exec("ALTER TABLE agent_meta ALTER column id SET DEFAULT nextval('agent_meta_id_seq');");
        __exec("ALTER TABLE space_meta ALTER column id SET DEFAULT nextval('space_meta_id_seq');");
        __exec("ALTER TABLE project_meta ALTER column id SET DEFAULT nextval('project_meta_id_seq');");
        __exec("ALTER TABLE event_meta ALTER column id SET DEFAULT nextval('event_meta_id_seq');");
        __exec("ALTER TABLE subsite_meta ALTER column id SET DEFAULT nextval('subsite_meta_id_seq');");
        __exec("ALTER TABLE evaluationmethodconfiguration_meta ALTER column id SET DEFAULT nextval('evaluationmethodconfiguration_meta_id_seq');");
        __exec("ALTER TABLE permission_cache_pending ALTER column id SET DEFAULT nextval('permission_cache_pending_seq');");
    },

    'refatoração dos índices da tabela pcache' => function () {
        __exec('CREATE INDEX pcache_object_user_action_idx ON pcache (user_id, object_type, action)');

        // remove índice duplicado
        // "pcache_permission_user_idx" btree (object_type, object_id, action, user_id)
        // "unique_object_action" UNIQUE, btree (object_type, object_id, action, user_id)
        __exec('DROP INDEX pcache_permission_user_idx');
    },

    'remove entradas da tabela pcache não mais utilizadas' => function () {
        __exec("
            DELETE FROM pcache 
            WHERE action NOT IN (
                '@control',
                'modify',
                'view',
                'applySeal',
                'support',
                'viewUserEvaluation',
                'evaluateOnTime',
                'evaluateRegistrations',
                'createEvents',
                'requestEventRelation');");
    },

    'Normalização dos campos do tipo checkbox nas inscrições' => function() {
        __exec("UPDATE registration_meta rm
		           SET value = '1'
                  FROM registration r
                  JOIN (SELECT opportunity_id, array_agg('field_' || rfc.id) AS fields
                          FROM registration_field_configuration rfc
                         WHERE rfc.field_type = 'checkbox'
                      GROUP BY opportunity_id
                       ) AS towcf ON towcf.opportunity_id = r.opportunity_id
                 WHERE rm.object_id = r.id
                   AND rm.value != '1'
                   AND rm.key = ANY(towcf.fields);");
    },

    // SEMPRE ENCERRAR O ÚLTIMO ITEM COM VÍRGULA A FIM DE
    // MINIMIZAR RISCO DE ERRO NA INSERÇÃO OU MERGE DE NOVOS ITENS
    'Atualiza o consolidated_result das inscrições com valores salvos em portuguêss' => function () {
        __exec("
            UPDATE registration r
            SET consolidated_result = CASE
                WHEN r.consolidated_result = 'Habilitado' THEN 'valid'
                WHEN r.consolidated_result = 'Inabilitado' THEN 'invalid'
                ELSE r.consolidated_result
            END
            FROM evaluation_method_configuration emc
            WHERE r.opportunity_id = emc.opportunity_id
                AND emc.type = 'qualification'
                AND r.consolidated_result IN ('Habilitado', 'Inabilitado')
        ");
    },

    "Removendo os campos e anexos de formulário erroneamente duplicados pela funcionalidade 'Duplicar Oportunidade'" => function() {
        __try("DELETE FROM registration_field_configuration rfc
                     USING registration_step rs
                     WHERE rs.id = rfc.step_id
                       AND rs.opportunity_id != rfc.opportunity_id;");

        __try("DELETE FROM registration_file_configuration rfc
                     USING registration_step rs
                     WHERE rs.id = rfc.step_id
                       AND rs.opportunity_id != rfc.opportunity_id;");
    }
    
] + $updates ;   

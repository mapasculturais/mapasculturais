<?php
$start_time = date('H:i:s');
$start_microtime = microtime(true);
$NUMBER_OF_PROCESSES = $argv[1];
$PROCESS_NUM = $argv[2];
define('NUMBER_OF_PROCESSES', intval($argv[1]));
define('PROCESS_NUM', $argv[2]);
define('UPDATE_NAME', $argv[3]);


require __DIR__ . '/../application/bootstrap.php';
use MapasCulturais\App;
use MapasCulturais\Entities;

MapasCulturais\App::i()->disableAccessControl();

class DB_UPDATE {
    const STEP = 50;
    
    static $query = [];

    static $exceptions = [];
    
    static function enqueue($entity_class, $where, $cb){
        $entity_class = strpos($entity_class, 'MapasCulturais\Entities\\') === 0 ? $entity_class : 'MapasCulturais\Entities\\' . $entity_class;

        $app = App::i();
        
        $table = $app->em->getClassMetadata($entity_class)->getTableName();

        self::$query[] = [
            'name' => self::$current_update,
            'class' => $entity_class,
            'table' => $table,
            'where' => $where,
            'cb' => $cb
        ];
    }
    static $current_update = '';
    
    static function save(){
        foreach(self::$save as $name => $q){
            if($q['save'] && $q['result'] !== false){
                MapasCulturais\App::i()->disableAccessControl();

                $up = new Entities\DbUpdate();
                $up->name = $name;
                $up->save(true);
            }
        }
    }
    
    static $save = [];
    
    static function load($name, $function, $save = true){

        try{
            self::$current_update = $name;
            self::$save[$name]['result'] = $function();
            self::$save[$name]['save'] = $save;
            self::$save[$name]['name'] = $name;
            
        }catch(\Exception $e){
            echo "\nERROR " . $e . "\n";
        }
    }
    
    static function loadUpdates(){
        $app = App::i();
        $updates = include __DIR__ . '/../mc-updates.php';
        
        $executed_updates = [];

        foreach($app->repo('DbUpdate')->findAll() as $up){
            $executed_updates[] = $up->name;
        }
        
        if(UPDATE_NAME){
            if(isset($updates[UPDATE_NAME])){
                self::load(UPDATE_NAME, $updates[UPDATE_NAME], !in_array(UPDATE_NAME, $executed_updates));
            }
        } else {
            foreach($updates as $name => $function){
                if(!in_array($name, $executed_updates)){
                    self::load($name, $function);
                }
            }
        }
    }
    
    static function execute(){
        $errors = [];

        $app = App::i();
        $conn = $app->em->getConnection();

        foreach(self::$query as $__query){
            if(!isset($__query['class'])){
                \dump($__query); die;
            }
            $__name = $__query['name'];
            $__class = $__query['class'];
            $__table = $__query['table'];
            $__where = $__query['where'];
            $__callback = $__query['cb'];
            
            $__num_entities = $conn->fetchColumn("SELECT COUNT(id) FROM {$__table} WHERE {$__where}");
            
            $__limit = ceil($__num_entities / NUMBER_OF_PROCESSES);
            $__offset = $__limit * (PROCESS_NUM - 1);
            
            $remaining_ids = $conn->fetchAll("SELECT id FROM {$__table} WHERE {$__where} ORDER BY id ASC LIMIT $__limit OFFSET $__offset");
            
            if($remaining_ids){
                while(count($remaining_ids) > 0){
                    $app->em->clear();
                    $c = count($remaining_ids);
                    
                    $__per = min([100, $c*NUMBER_OF_PROCESSES / $__num_entities * 100]);
                    $__per = str_pad(number_format($__per, 2),6, ' ', STR_PAD_LEFT);
                    
                    $__faltam = str_pad($c, 7);
                    
                    $__ptable = str_pad($__table, 15, ' ', STR_PAD_BOTH);
                    
                    echo " $__name #" . str_pad(PROCESS_NUM, 2) . " ($__ptable) FALTAM: $__faltam (" . $__per . "%)\n";
                    
                    $slice = array_splice($remaining_ids, 0, self::STEP);
                    
                    $ids = implode(',', array_map(function($e) { return $e['id']; } , $slice));
                    
                    $query = $app->em->createQuery("SELECT e FROM $__class e WHERE e.id IN ({$ids})");

                    $entities = $query->getResult();
                    
                    foreach($entities as $entity){
                        $app->disableAccessControl();

                        try{
                            $__callback($entity);
                        } catch (\Error $e){
                            self::$exceptions[] = [
                                'update' => $__name,
                                'exception' => $e,
                                'entity' => $entity,
                            ];
                        } catch (\Exception $e){
                            self::$exceptions[] = [
                                'update' => $__name,
                                'exception' => $e,
                                'entity' => $entity,
                            ];
                        }
                    }
                    
                    $app->em->flush();
                }
            }
        }

        if(self::$exceptions){
            echo "\n\n=================================\nEXCEPTIONS: \n";
            foreach(self::$exceptions as $e){
                echo "\n {$e['update']}: {$e['entity']} \n\t{$e['exception']}\n";
            }
        }
    }
}



DB_UPDATE::loadUpdates();

DB_UPDATE::execute();

if(PROCESS_NUM == 1){
    DB_UPDATE::save();
}

$finish_time = date('H:i:s');
$finish_microtime = microtime(true);

$execution_time = number_format($finish_microtime - $start_microtime, 4);

$t = gmdate("H:i:s", (int) $execution_time);

echo "
==========================================================================================
     PROCESSO {$PROCESS_NUM} de {$NUMBER_OF_PROCESSES} executado das $start_time às $finish_time ($t)
------------------------------------------------------------------------------------------
";
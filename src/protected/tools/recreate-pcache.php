<?php
$delete = false;

$start_timestamp = date('H:i:s');
$start_microtime = microtime(true);

if(isset($argv[1]) && $argv[1] == 'delete'){
    $delete = true;
} else if(isset($argv[1]) && isset($argv[2])){
    $number_of_processes = $argv[1];
    $process_number = $argv[2];
} else {
    $number_of_processes = 1;
    $process_number = 1;
}

require __DIR__ . '/../application/bootstrap.php';

$app = MapasCulturais\App::i();
$conn = $app->em->getConnection();

if($delete){
    echo "\nDELETANDO CACHE EXISTENTE.... ";
    $conn->executeQuery('DELETE FROM pcache');
    echo "FEITO\n\n\n";
    exit;
}

$log_file = "/tmp/pcache-log-{$process_number}.log";
echo "\n iniciando o processo $process_number de $number_of_processes (log to $log_file)";


$entities = [
    'agent' => 'MapasCulturais\Entities\Agent',
    'space' => 'MapasCulturais\Entities\Space',
    'project' => 'MapasCulturais\Entities\Project',
    'event' => 'MapasCulturais\Entities\Event',
    'seal' => 'MapasCulturais\Entities\Seal',
    'registration' => 'MapasCulturais\Entities\Registration',
    'notification' => 'MapasCulturais\Entities\Notification',
    'request' => 'MapasCulturais\Entities\Request',
];

$count = [];
$limits = [];
$offsets = [];

$total = 0;

foreach($entities as $table => $class){
    $num = $conn->fetchColumn("SELECT COUNT(id) FROM $table");
    $limit = intval(ceil($num/$number_of_processes));
    $offset = $limit * ($process_number - 1);
    
    $count[$table] = $num;
    $limits[$table] = $limit;
    $offsets[$table] = $offset;
    
    $total += $limit;
    
}

if($process_number == 1){
    print_r($limits);
    print_r($offsets);
}

$processed_total = 0;

foreach($entities as $table => $class){
    $processed_entity = 0;
    $num = $count[$table];
    $limit = $limits[$table];
    $offset = $offsets[$table];
    
    $next = $limit > 0;
    $i = 0;
    $step = 75;
    while($next){
        $app->em->clear();

        $q = $app->em->createQuery("SELECT e FROM $class e ORDER BY e.id");

        $first_result = $offset + $i * $step;
        if($step + $first_result > $limit){
            $mx = $step - ($first_result - $limit);
        } else {
            $mx = $step
        }
        $q->setMaxResults($mx);
        $q->setFirstResult($first_result);
        
        $i++;
        
        $next = $i * $step < $limit;
        
        $entities = $q->getResult();

        $flush_each = 100;
        $current = 0;

        $num_entities = count($entities);

        foreach($entities as $entity){
            $_st = microtime(true);
            
            $processed_total++;
            $processed_entity++;

            $_total = round($processed_total / $total * 100,2);
            $_entity = round($processed_entity / $limit * 100,2);
            $__processed_entity = str_pad($processed_entity,5,'0',STR_PAD_LEFT);
            $__processed_total = str_pad($processed_total,5,'0',STR_PAD_LEFT);
            $__process_number = str_pad($process_number,2,' ', STR_PAD_LEFT);
            
            $__total = str_pad(number_format($_total, 2), 6, ' ', STR_PAD_LEFT);
            $__entity = str_pad(number_format($_entity, 2), 6, ' ', STR_PAD_LEFT);

            $entity->createPermissionsCacheForUsers(null, false, false);
            $current++;
            
            $_total_time = intval(microtime(true) - $start_microtime);
            
            $s = number_format(microtime(true) - $_st, 3);
            
            $rel = str_pad(intval(1/$s*$number_of_processes),5, ' ', STR_PAD_LEFT);
            
            $__table = str_pad($table, 12);
            
            $__limit = str_pad($limit, 5, ' ', STR_PAD_LEFT);

            echo "\n P({$__process_number}) :: $__table ({$__processed_entity} / {$__limit} = {$__entity}%) :: TOTAL ({$__processed_total} / {$total} = {$__total}%) exec {$s}s | $rel | {$_total_time}s";
            
            if($current > $flush_each){
                $current = 0;

                $app->em->flush();
            }

            $app->em->flush();
        }
    }
}

$finish_timestamp = date('H::i:s');
$finish_microtime = microtime(true);
$execution_time = number_format($finish_microtime - $start_microtime, 4);

echo "
========================    
inicial às $start_timestamp e terminado às $finish_timestamp
    
EXECUTADO em $execution_time segundos
        
";
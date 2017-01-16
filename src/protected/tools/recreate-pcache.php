<?php
$delete = false;
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
    
    $q = $app->em->createQuery("SELECT e FROM $class e ORDER BY e.id");
    
    $q->setMaxResults($limit);
    $q->setFirstResult($offset);
    
    $entities = $q->getResult();
    
    $flush_each = 100;
    $current = 0;
    
    $num_entities = count($entities);
    
    foreach($entities as $entity){
        $processed_total++;
        $processed_entity++;
        
        $_total = round($processed_total / $total * 100,2);
        $_entity = round($processed_entity / $limit * 100,2);
        
        echo "\n P({$process_number}) :: $table ({$processed_entity} / {$num_entities} = {$_entity}%) :: TOTAL ({$processed_total} / {$total} = {$_total}%)";
        $entity->createPermissionsCacheForUsers(null, false, false);
        $current++;
        
        if($current > $flush_each){
            $current = 0;
            
            $app->em->flush();
        }
        
        $app->em->flush();
    }
}

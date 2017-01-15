<?php
if(isset($argv[1]) && isset($argv[2])){
    $number_of_processes = $argv[1];
    $process_number = $argv[2];
} else {
    $number_of_processes = 1;
    $process_number = 1;
}


require __DIR__ . '/../application/bootstrap.php';

$log_file = "/tmp/pcache-log-{$process_number}.log";
echo "\n iniciando o processo $process_number de $number_of_processes (log to $log_file)";

$app = MapasCulturais\App::i();

$conn = $app->em->getConnection();

$entities = [
    'agent' => 'MapasCulturais\Entities\Agent',
    'space' => 'MapasCulturais\Entities\Space',
    'project' => 'MapasCulturais\Entities\Project',
    'Event' => 'MapasCulturais\Entities\Event',
    'Seal' => 'MapasCulturais\Entities\Seal',
    'registration' => 'MapasCulturais\Entities\Registration',
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

$processed_total = 0;
foreach($entities as $table => $class){
    $processed_entity = 0;
    
    $num = $count[$table];
    $limit = $limits[$table];
    $offset = $offsets[$table];
    
    $q = $app->em->createQuery("SELECT e FROM $class e ORDER BY e.id");
    
    $q->setMaxResults($limit);
    $q->setFirstResult($offset + 1);
    
    $entities = $q->getResult();
    
    $flush_each = 100;
    $current = 0;
    
    
    foreach($entities as $entity){
        $processed_total++;
        $processed_entity++;
        
        $_total = round($processed_total / $total * 100,2);
        $_entity = round($processed_entity / $limit * 100,2);
        
        echo "\n P({$process_number}) :: $table ({$processed_entity} / {$limit} = {$_entity}%) :: TOTAL ({$processed_total} / {$total} = {$_total}%)";
        $entity->createPermissionsCacheForUsers();
        $current++;
        
        if($current > $flush_each){
            $current = 0;
            
            $app->em->flush();
        }
        
        $app->em->flush();
    }
}

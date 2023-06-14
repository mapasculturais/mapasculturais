<?php
set_time_limit(0);
ini_set('memory_limit', '2048M');

define('DB_UPDATES_FILE', realpath(__DIR__ . '/../' ) . '/db-updates.php');
$time_start = microtime(true);
$save_log = isset($argv[1]) && $argv[1];
if($save_log)
    ob_start();

require __DIR__ . '/../application/bootstrap.php';

if($save_log)
    $log = ob_get_clean();

$time_end = microtime(true);

$execution_time = number_format($time_end - $time_start, 4);


$exec_time = "
=================================================
db updates executed in {$execution_time} seconds.
=================================================\n";

if($save_log && $log){
    $log_path = MapasCulturais\App::i()->config['app.log.path'];
    $log_filename = 'db-updates-' . date('Y.m.d-H.i.s') . '.log';
    file_put_contents( $log_path . $log_filename, $exec_time . $log);
}else{
    echo $exec_time;
}
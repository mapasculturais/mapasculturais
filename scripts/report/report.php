<?php
require __DIR__ . '/../../src/protected/application/bootstrap.php';

@unlink('output/agents.csv');
@unlink('output/spaces.csv');
@unlink('output/projects.csv');
@unlink('output/events.csv');
@unlink('output/occurrences.csv');


$app = MapasCulturais\App::i();

$conn = $app->em->getConnection();

/**
  * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
  * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
  */
function arrayToCsv( array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');

    $output = array();
    foreach ( $fields as $field ) {
        if ($field === null && $nullToMysqlNull) {
            $output[] = 'NULL';
            continue;
        }

        // Enclose fields containing $delimiter, $enclosure or whitespace
        if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
        }
        else {
            $output[] = $field;
        }
    }

    return implode( $delimiter, $output );
}


function fetchTerms($taxonomy, $entity_class, $entity_id) {
    $conn = MapasCulturais\App::i()->em->getConnection();
    $terms = $conn->fetchAll(""
            . "SELECT t.term "
            . "FROM term t, term_relation tr "
            . "WHERE t.id = tr.term_id AND t.taxonomy = {$taxonomy} AND tr.object_type = 'MapasCulturais\Entities\\{$entity_class}' AND tr.object_id = {$entity_id}");
    
    return implode(', ', array_map(function($e){ return $e['term']; }, $terms));
}



/* ========== AGENTES ========== */
$sql_agents = file_get_contents(__DIR__ . '/_agents.sql');
$rs = $conn->fetchAll($sql_agents);

foreach($rs as $i => $obj){
    $obj = (array) $obj;
    
    $obj['area'] = fetchTerms(2,'Agent', $obj['id']);
    
    if($i == 0){
        $obj_keys = array_keys($obj);
        file_put_contents('output/agents.csv', arrayToCsv($obj_keys) . "\n", FILE_APPEND);
    }
    file_put_contents('output/agents.csv', arrayToCsv($obj) . "\n", FILE_APPEND);
}

/* ========== ESPAÇOS ========== */
$sql_spaces = file_get_contents(__DIR__ . '/_spaces.sql');
$rs = $conn->fetchAll($sql_spaces);

foreach($rs as $i => $obj){
    $type = $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Space', $obj['type']);
    $obj['type'] = $type->name;
    $obj['area'] = fetchTerms(2, 'Space', $obj['id']);
    
    if($i == 0){
        $obj_keys = array_keys($obj);
        file_put_contents('output/spaces.csv', arrayToCsv($obj_keys) . "\n", FILE_APPEND);
    }
    file_put_contents('output/spaces.csv', arrayToCsv($obj) . "\n", FILE_APPEND);
}

/* ========== PROJETOS ========== */
$sql_projects = file_get_contents(__DIR__ . '/_projects.sql');
$rs = $conn->fetchAll($sql_projects);


foreach($rs as $i => $obj){
    $type = $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Project', $obj['type']);
    $obj['type'] = $type->name;
    
    if($i == 0){
        $obj_keys = array_keys($obj);
        file_put_contents('output/projects.csv', arrayToCsv($obj_keys) . "\n", FILE_APPEND);
    }
    file_put_contents('output/projects.csv', arrayToCsv($obj) . "\n", FILE_APPEND);
}

/* ========== EVENTOS ========== */
$sql_events = file_get_contents(__DIR__ . '/_events.sql');
$rs = $conn->fetchAll($sql_events);


foreach($rs as $i => $obj){
    $obj['linguagem'] = fetchTerms(3,'Event',$obj['id']);
    
    if($i == 0){
        $obj_keys = array_keys($obj);
        file_put_contents('output/events.csv', arrayToCsv($obj_keys) . "\n", FILE_APPEND);
    }
    file_put_contents('output/events.csv', arrayToCsv($obj) . "\n", FILE_APPEND);
}

/* ========== OCORRENCIAS ========== */
$sql_events = file_get_contents(__DIR__ . '/_occurrences.sql');
$rs = $conn->fetchAll($sql_events);


foreach($rs as $i => $obj){
    $obj['evento_linguagens'] = fetchTerms(3,'Event',$obj['event_id']);
    $obj['espaco_areas'] = fetchTerms(3,'Event',$obj['space_id']);
    
    unset($obj['event_id']);
    unset($obj['space_id']);
    
    if($i == 0){
        $obj_keys = array_keys($obj);
        file_put_contents('output/occurrences.csv', arrayToCsv($obj_keys) . "\n", FILE_APPEND);
    }
    file_put_contents('output/occurrences.csv', arrayToCsv($obj) . "\n", FILE_APPEND);
}

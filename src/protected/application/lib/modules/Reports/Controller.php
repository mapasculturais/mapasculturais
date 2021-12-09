<?php

namespace Reports;

use DateTime;
use DateInterval;
use MapasCulturais\i;
use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Traits;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\MetaList;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

class Controller extends \MapasCulturais\Controller
{

    use Traits\ControllerAPI;
    protected function fetch($sql, $params = [])
    {
        $app = App::i();

        $conn = $app->em->getConnection();

        $conn->fetchAll($sql, $params);
    }

    public function GET_agents()
    {
        $props = ['_type'];
        $meta = ['En_Estado', 'En_Cidade', 'acessibilidade'];

        $daily_data = $this->getEntityDailyData('agent', Agent::class, $props, $meta);
        $total_data = $this->getEntityDailyData('agent', Agent::class, $props, $meta);

        $this->render('agents', ['total_data' => $total_data, 'daily_data' => $daily_data]);
    }
    public function GET_spaces()
    {
        $props = ['_type'];
        $meta = ['En_Estado', 'En_Cidade', 'acessibilidade'];

        $daily_data = $this->getEntityDailyData('space', Space::class, $props, $meta);
        $total_data = $this->getEntityDailyData('space', Space::class, $props, $meta);

        $this->render('spaces', ['total_data' => $total_data, 'daily_data' => $daily_data]);
    }
    public function GET_events()
    {
        $this->entityReport('event', Event::class);
    }
    public function GET_projects()
    {
        $this->entityReport('project', Project::class);
    }
    public function GET_opportunities()
    {
        $this->entityReport('opportunity', Opportunity::class);
    }
    public function GET_files()
    {
        $this->entityReport('file', File::class);
    }
    public function GET_registrations()
    {
        $this->entityReport('registration', Registration::class);
    }

    public function getEntityFilters($entity_class)
    {
        $metadata = $entity_class::getPropertiesMetadata(true);

        $filters = [];
        if (isset($metadata['status'])) {
            $filters[] = 'e.status > 0';
        }

        return $filters;
    }

    /**
     * Página de impressão de dos reports
     */
    public function GET_printReports()
    {
        $this->requireAuthentication();
        
        $opp = $this->getOpportunity();
        
        $request = $this->data;
       
        $this->render('print-reports', ['opportunity' => $opp, 'status' => $request['status']]);
    }

    /**
     * Gera CSV das inscrições agrupadas por status
     *
     *
     */
    public function GET_exportRegistrationsByStatus()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $conn = $app->em->getConnection();

        $request = $this->data;

        $data = [];
        $params = ['opportunity' => $request['opportunity_id']];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value) {
            $status = $this->statusToString($value["status"]);
            if ($status) {
                $data[$status] = $value["count"];
            }
        }

        $csv_data = [];
        foreach ($data as $key => $value) {
            $csv_data[] = [$key, $value];
        }

        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity_id']);

    }

    public function GET_registrationsByEvaluationStatusBar()
    {
        $app = App::i();

        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $em = $opp->getEvaluationMethod();

        //Pega conexão
        $conn = $app->em->getConnection();
        

        $request = $this->data;

        //Seleciona e agrupa inscrições ao longo do tempo

        $params = ['opportunity_id' => $opp->id];

        $result = [];
        $a = 0;
        $b = 20;
        $label = "";
        for ($i = 0; $i < 100; $i += 20) {

            if ($i > 0) {
                $a = $b + 1;
                $b = $b + 20;
            }

            $query = "SELECT count(consolidated_result)
            FROM registration r
            WHERE opportunity_id = :opportunity_id
            AND consolidated_result <> '0' AND
            cast(consolidated_result as DECIMAL) BETWEEN {$i} AND {$b}";

            $label = i::__('de ') . $a . i::__(' a ') . $b;

            $result[$label] = $conn->fetchAll($query, $params);

        }
        $header = [
            i::__('AVALIACAO'),
            i::__('QUANTIDADE'),
        ];
        $data = [];
        foreach ($result as $key => $value) {
            
            $csv_data[] = [$key, $value[0]['count']];

        }

        $this->createCsv($header, $csv_data, $request['action'], $opp->id);
    }

    /**
     * Gera CSV das inscrições agrupadas por avaliação
     *
     *
     */
    public function GET_exportRegistrationsByEvaluation()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $conn = $app->em->getConnection();

        $request = $this->data;

        $data = [];
        $params = ['opportunity' => $request['opportunity_id']];

        $query = "SELECT count(*) AS evaluated FROM registration r WHERE opportunity_id = :opportunity  AND consolidated_result <> '0'";

        $evaluated = $conn->fetchAll($query, $params);

        $query = "SELECT COUNT(*) AS notEvaluated FROM registration r WHERE opportunity_id = :opportunity  AND consolidated_result = '0'";

        $notEvaluated = $conn->fetchAll($query, $params);

        $data = array_merge($evaluated, $notEvaluated);

        foreach ($data as $m) {
            foreach ($m as $v) {
                if (empty($v)) {
                    return false;
                }
            }
        }

        $result = [];
        foreach ($data as $m) {
            foreach ($m as $key => $v) {
                $result[] = [$key, $v];
            }
        }

        $csv_data = [];
        $csv_data = array_map(function ($index) {
            if ($index[0] == "evaluated") {
                return [
                    i::__('AVALIADA'),
                    $index[1],
                ];
            } else {
                return [
                    i::__('NAO AVALIADA'),
                    $index[1],
                ];
            }
        }, $result);

        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity_id']);

    }

    /**
     * Gera CSV das inscrições agrupadas por status da avaliação
     *
     *
     */
    public function GET_exportRegistrationsByEvaluationStatus()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $request = $this->data;

        $em = $opp->getEvaluationMethod();

        $conn = $app->em->getConnection();

        $data = [];
        $params = ['opportunity' => $opp->id];

        $query = "SELECT COUNT(*), consolidated_result FROM registration r WHERE opportunity_id = :opportunity  AND consolidated_result <> '0' GROUP BY consolidated_result";

        $evaluations = $conn->fetchAll($query, $params);

        $cont = 0;
        foreach ($evaluations as $evaluation) {
            if ($cont < 8) {
                $data[$em->valueToString($evaluation['consolidated_result'])] = $evaluation['count'];
                $cont++;
            }
        }

        $csv_data = [];
        foreach ($data as $key => $value) {
            $csv_data[] = [$key, $value];
        }

        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity_id']);
    }

    /**
     * Gera CSV das inscrições  agrupadas pela categoria
     *
     *
     */
    public function GET_exportRegistrationsByCategory()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $request = $this->data;

        $conn = $app->em->getConnection();

        $csv_data = [];
        $params = ['opportunity' => $opp->id];

        $query = "select  category, count(category) from registration r where r.status > 0 and r.opportunity_id = :opportunity group by category";

        $csv_data = $conn->fetchAll($query, $params);

        foreach ($csv_data as $value) {
            foreach ($value as $v) {
                if (empty($v)) {
                    return false;
                }
            }
        }

        $header = [
            i::__('CATEGORIA'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $opp->id);
    }

    /**
     * Gera CSV das inscrições agrupadas por status
     *
     *
     */
    public function GET_exportRegistrationsDraftVsSent()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $request = $this->data;

        $app = App::i();

        $conn = $app->em->getConnection();

        $data = [];
        $params = ['opportunity' => $opp->id];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value) {
            $status = $this->statusToString($value["status"]);
            if ($status) {
                $data[$status] = $value["count"];
            }
        }

        $csv_data = [];
        $total = 0;
        foreach ($data as $key => $value) {
            if ($key == i::__("Rascunho")) {
                $csv_data[0] = [i::__("Rascunho"), $value];
            } else {
                $total = ($total + $value);
                $csv_data[1] = [i::__("Enviadas"), $total];
            }

        }

        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity_id']);

    }

    /**
     * Gera CSV das Inscrições VS tempo
     *
     *
     */
    public function GET_registrationsByTime()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $conn = $app->em->getConnection();

        $initiated = [];
        $sent = [];
        $params = ['opportunity' => $opp->id];

        $query = "SELECT
        to_char(create_timestamp , 'YYYY-MM-DD') as date,
        count(*) as total
        FROM registration r
        WHERE opportunity_id = :opportunity
        GROUP BY to_char(create_timestamp , 'YYYY-MM-DD')
        ORDER BY date ASC";
        $initiated = $conn->fetchAll($query, $params);

        $query = "SELECT
        to_char(sent_timestamp , 'YYYY-MM-DD') as date,
        count(*) as total
        FROM registration r
        WHERE opportunity_id = :opportunity AND r.status > 0
        GROUP BY to_char(sent_timestamp , 'YYYY-MM-DD')
        ORDER BY date ASC";
        $sent = $conn->fetchAll($query, $params);

        if (!$sent || !$initiated) {
            return false;
        }

        $header = [
            i::__('STATUS'),
            i::__('DATA'),
            i::__('QUANTIDADE'),
        ];

        $result = [];
        $count = 0;
        foreach ($sent as  $value) {
            $result[$count]['status'] = i::__('Enviada');
            $result[$count] += $value;

            $count++;
        }

        foreach ($initiated as $value) {
            $result[$count]['status'] = i::__('Iniciada');
            $result[$count] += $value;

            $count++;

        }

        $return = array_map(function ($index) {
            $date = new DateTime($index['date']);
            return [
                'status' => $index['status'],
                'data' => $date->format('d/m/Y'),
                'total' => $index['total'],

            ];
        }, $result);

        $this->createCsv($header, $return, $this->data['action'], $opp->id);

    }

    public function GET_getGraphic()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();

        $return = [];

        $request = $this->data;

        if(!(isset($request['reportData']))){
            
            $params = ['objectId' => $opp->id, "group" => "reports"];
    
            $metalists = $app->repo("MetaList")->findBy($params);
          
            foreach ($metalists as $metalist){
                $value = json_decode($metalist->value, true);
                $value['reportData']['graphicId'] = $metalist->id;
                $value['data'] = $this->getData($value, $opp, $request['status']);
                $return[] = $value;
                
            }
        }else{
            $reportData = json_decode($request['reportData'], true);
            $return =  $this->getData($reportData, $opp, $request['status']);
        }

        $this->apiResponse($return);
    }

    public function POST_saveGraphic()
    {
        $this->requireAuthentication();

        $app = App::i();
        $module = $app->modules['Reports'];

        $request = $this->data;
        $opp = $app->repo("Opportunity")->find($request["opportunity_id"]);
        $opp->checkPermission('viewReport');
                
        $preload = $this->getData($this->data, $opp, $request['status']);

        /**
         * Verifica se existe dados suficientes para gerar o gráfico
         */ 
        if ($preload['typeGraphic'] == 'pie') {
            if (!$module->checkIfChartHasData($preload['data']) && $request['status'] == 'all') {
                $this->apiResponse(['error' => true]);
                return;
            }
        } else {
            if (!$module->checkIfChartHasData($preload['series']) && $request['status'] == 'all') {
                $this->apiResponse(['error' => true]);
                return;
            }
        }

        $value = "";
        $source = "";       
        foreach ($request['columns'] as $v){
            $value .= $v['value'];
            $source .= is_array($v['source']) ? implode(",",$v['source']) : $v['source'];
        }

        $identifier = md5($opp->id . "-" . $request['typeGraphic'] . "-" . $source . "-" . $value);
        
        $this->data['identifier'] = $identifier;

        $conn = $app->em->getConnection();

        $params = [
            "identifier" => "%identifier\":\"{$identifier}%"
        ];
       
        $query = "SELECT * FROM metalist WHERE value like :identifier";
        if (!($metalist = $conn->fetchAll($query, $params))) {
            $metaList = new metaList;
            $metaList->value = json_encode($this->data) ;
        } else {
            $metaList = $app->repo("MetaList")->find($metalist[0]['id']);
            $metaList->value = json_encode($this->data);
        }

        $metaList->owner = $opp;
        $metaList->group = 'reports';
        $metaList->title = 'Graphic' ;
        $metaList->save(true);

        $return = [
            'graphicId' => $metaList->id,
            'identifier' => $identifier,
            'error' => false
        ];

        $this->apiResponse($return);

    }

    public function GET_dataOpportunityReport()
    {
        $opp = $this->getOpportunity();
        $opp->checkPermission('viewReport');

        $app = App::i();        
        $opportunity = $app->repo("Opportunity")->find($opp->id);
        $this->apiResponse($this->getValidFields($opportunity));
    }

    public function ALL_csvDynamicGraphic()
    {
        $this->requireAuthentication();
    
        $opp = $this->getOpportunity();
        
        $app = App::i();

        $return = null;

        $request = $this->data;
        
        $params = ['objectId' => $opp->id, "group" => "reports", "id" => $request['graphicId']];

        $metalists = $app->repo("MetaList")->findBy($params);
        
        $action =  i::__('dynamicGraphic');
       
        foreach ($metalists as $metalist){
            $value = json_decode($metalist->value, true);
            $value['reportData']['graphicId'] = $metalist->id;
            $value['data'] = $this->getData($value, $opp, $_SESSION['reportStatusRegistration']);
            $return = $value;
        }

        $csv_data = [];
        if($return['typeGraphic'] != "pie"){
            
            $header = $return['data']['labels'];
            array_unshift($header, "");

            $csv_data = [];
            foreach ($return['data']['series'] as $key => $value){
                $csv_data[][$key] = $value['label'];
                foreach ($value['data'] as $k => $v){                    
                    $csv_data[$key][] = $v;
                }
            }
        
        }else{           
            $header = [i::__($return['title']), i::__('QUANTIDADE')];
            foreach ($return['data']['data'] as $key => $value){
                $csv_data[] = [$return['data']['labels'][$key], $value];
            }
        }
        
        $this->createCsv($header, $csv_data, $action, $opp->id);
    }

    public function getData($reportData, $opp, $status)
    {
        $em = $opp->getEvaluationMethod();
        $app = App::i();
        $module = $app->modules['Reports'];

        $dataA = $reportData["columns"][0];
        $dataB = $reportData["columns"][1];
        $conn = $app->em->getConnection();
        $query = $this->buildQuery($reportData["columns"], $opp,
                                   ($reportData["typeGraphic"] == "line"), $status);
        $result = $conn->fetchAll($query, ["opportunity" => $opp->id]);

        $return = [];
        $labels = [];
        $color = [];
        $data = [];
        $generate_colors = [];
        // post-processing may be necessary depending on type, so obtain it
        $typeA = $dataA["source"]["type"] ?? "";
        if ($reportData["typeGraphic"] != "pie") {
            $typeB = $dataB["source"]["type"] ?? "";
            $return = ($reportData["typeGraphic"] == "line") ?
                      $this->prepareTimeSeries($result, $typeA, $opp, $em) :
                      $this->prepareGroupedSeries($result, $typeA, $typeB,
                                                  $reportData["typeGraphic"],
                                                  $opp, $em);
        } else {
            foreach ($result as $item) {
                
                $color = $this->getChartColors();

                $color[] = $color[0];
                $labels[] = $this->generateLabel($item["value0"], $typeA, $em);
                $data[] = $item["quantity"];
            }
            $return = [
                "labels" => $labels,
                "backgroundColor" => $color,
                "borderWidth" => 0,
                "data" => $data,
                "typeGraphic" => $reportData["typeGraphic"],
            ];
        }
        return $return;
    }

    public function buildQuery($columns, $op, $timeSeries=false, $statusValue = "all")
    {

        switch ($statusValue) {
            case 'all':
                $status = '> 0';
                break;
            case 'draft':
                $status = '= 0';
                break;
            case 'approved':
                $status = '= 10';
                break;
            default:
                $status = '> 0';
                break;
        }


        // FIXME: remove empty definitions at the source, not here
        $columns = array_filter($columns, function ($item) {
            return (strlen($item["value"]) > 0);
        });
        $out = "";
        $tables = $this->queryTables($columns);
        $fields = $this->queryFields($columns, $op);
        $tbCodes = array_map(function ($column) {
            return $column["source"]["table"];
        }, $columns);
        $types = array_map(function ($column) {
            return ($column["source"]["type"] ?? "");
        }, $columns);
        $ctes = $this->queryCTEs($fields, $tbCodes, $tables, $types);
        if (!empty($ctes)) {
            $out .= ("WITH " . implode(", ", array_map(function ($i, $cte) {
                return "table$i AS $cte";
            }, array_keys($ctes), $ctes)) . " ");
        }
        $targets = $this->queryTargets($columns, $tables, $types,
                                       sizeof($ctes), $timeSeries);
        $out .= ("SELECT " . $this->querySelect($targets, $timeSeries) .
                 " FROM registration r " .
                 $this->queryJoins($tables, $types, $ctes) .
                 "WHERE r.opportunity_id = :opportunity AND r.status {$status} " .
                 "GROUP BY " . $this->queryGroup($targets) .
                 $this->queryOrder($targets, $timeSeries));
        return $out;
    }

    private function queryCTEs($fields, $tbCodes, $tables, $types)
    {
        $ctes = [];
        if (($cte = $this->queryTemplateCTE($fields[0], $tbCodes[0],
                                            $tables[0], $types[0]))) {
            $ctes[] = $cte;
        }
        if ((sizeof($tables) > 1) &&
            (($tables[1] == "term") || str_ends_with($tables[1], "_meta") ||
             ($tables[0] != $tables[1]) || ($types[0] != $types[1]))) {
            if (($cte = $this->queryTemplateCTE($fields[1], $tbCodes[1],
                                                $tables[1], $types[1]))) {
                // agent and space CTEs come with a %s that needs to go
                if (($tables[1] == "agent") || ($tables[1] == "space")) {
                    $s = (($tables[0] == "registration") || !empty($ctes)) ?
                         "" : ", {$tbCodes[1]}.{$fields[1]}";
                    $cte = sprintf($cte, $s);
                }
                $ctes[] = $cte;
            }
        }
        if (($tables[0] == "agent") || ($tables[0] == "space")) {
            if ((sizeof($ctes) == 2) || (sizeof($tables) == 1) ||
                ($tables[1] == "registration")) {
                $ctes[0] = sprintf($ctes[0], "");
            } else {
                $ctes[0] = sprintf($ctes[0], ", {$tbCodes[1]}.{$fields[1]}");
            }
        }
        return $ctes;
    }

    private function queryFields($columns, $op)
    {
        // get all field names for this opportunity to validate received ones
        $fieldList = array_map(function ($item) {
            return $item["value"];
        }, $this->getValidFields($op));
        return array_map(function ($item) use ($fieldList) {
            if (!in_array($item["value"], $fieldList)) {
                $this->errorJson("Invalid parameter.");
            }
            return $item["value"];
        }, $columns);
    }

    private function queryGroup($targets)
    {
        return implode(", ", $targets);
    }

    private function queryJoins($tables, $types, $ctes)
    {
        if (empty($ctes)) {
            return "";
        }
        $joins = "LEFT OUTER JOIN table0 ON r.id = table0.object_id ";
        $i = ($tables[0] == "registration") ? 1 : 0;
        if ($this->queryMatchAgent($tables[$i], $types[$i])) {
            $joins = ($tables[$i] == "agent") ? "" : "LEFT OUTER ";
            $joins .= "JOIN table0 ON r.agent_id = table0.object_id ";
        }
        if (sizeof($ctes) > 1) {
            if ($this->queryMatchAgent($tables[1], $types[1])) {
                $joins .= (($tables[$i] == "agent_meta") ?
                            "" : "LEFT OUTER ");
                $joins .= "JOIN table1 ON r.agent_id = table1.object_id ";
            } else {
                $joins .= ("LEFT OUTER JOIN table1 ON r.id = " .
                            "table1.object_id ");
            }
        }
        return $joins;
    }

    private function queryMatchAgent($table, $type)
    {
        return ((str_starts_with($table, "agent") && ($type != "coletivo") &&
                 ($type != "instituicao")) ||
                (($table == "term") && ($type == "owner")));
    }

    private function queryOrder($targets, $timeSeries)
    {
        if (!$timeSeries) {
            return "";
        }
        return (" ORDER BY " . implode(", ", $targets). " ASC");
    }

    private function querySelect($targets, $timeSeries)
    {
        $out = array_map(function ($i, $target) use ($timeSeries) {
            if ($timeSeries && ($i == 0)) {
                return "$target AS date";
            }
            $n = $timeSeries ? ($i - 1) : $i;
            return "$target AS value$n";
        }, array_keys($targets), $targets);
        $out[] = "count(*) AS quantity";
        return implode(", ", $out);
    }

    private function queryTables($columns)
    {
        // map front-end names back to real table names
        $tableDic = [
            "r" => "registration",
            "rm" => "registration_meta",
            "a" => "agent",
            "am" => "agent_meta",
            "s" => "space",
            "sm" => "space_meta",
            "t" => "term"
        ];
        return array_map(function ($column) use ($tableDic) {
            if (!isset($tableDic[$column["source"]["table"]])) {
                $this->errorJson("Invalid parameter.");
            }
            return $tableDic[$column["source"]["table"]];
        }, $columns);
    }

    private function queryTargets($columns, $tables, $types, $nctes,
                                  $timeSeries)
    {
        $out = array_map(function ($i, $column) use ($tables, $types, $nctes) {
            $src = $column["source"]["table"];
            if ($tables[$i] != "registration") {
                $src = "table" . (min($i, ($nctes - 1)));
            }
            $field = str_ends_with($tables[$i], "_meta") ? "$src.value" :
                     "$src.{$column["value"]}";
            if ($types[$i] == "dateToAge") {
                $field = "div(date_part('year', age(to_timestamp($field, " .
                         "'YYYY-MM-DD')))::integer, 5)";
            }
            return $field;
        }, array_keys($columns), $columns);
        if ($timeSeries) {
            array_unshift($out, "to_char(r.create_timestamp, 'YYYY-MM-DD')");
        }
        return $out;
    }

    private function queryTemplateCTE($field, $tbCode, $table, $type)
    {
        // object type expressions used in relation tables
        $agentType = "object_type = 'MapasCulturais\\Entities\\Agent'";
        $regType = "object_type = 'MapasCulturais\\Entities\\Registration'";
        $spaceType = "object_type = 'MapasCulturais\\Entities\\Space'";
        // CTEs by source table (considering owner agent)
        $ctes = [
            "registration_meta" => "(SELECT $tbCode.object_id, $tbCode.value FROM $table $tbCode WHERE $tbCode.key = '$field')",
            "agent" => "(SELECT $tbCode.id AS object_id, $tbCode.$field%s FROM $table $tbCode)",
            "agent_meta" => "(SELECT $tbCode.object_id, $tbCode.value FROM $table $tbCode WHERE $tbCode.key = '$field')",
            "space" => "(SELECT sr.object_id, $tbCode.$field%s FROM space_relation sr JOIN $table $tbCode ON sr.space_id = $tbCode.id WHERE sr.$regType)",
            "space_meta" => "(SELECT sr.object_id, $tbCode.value FROM space_relation sr JOIN $table $tbCode ON sr.space_id = $tbCode.object_id WHERE sr.$regType AND $tbCode.key = '$field')",
            "term" => "(SELECT tr.object_id, $tbCode.$field FROM term_relation tr JOIN $table $tbCode ON tr.term_id = $tbCode.id WHERE tr.$agentType AND $tbCode.taxonomy = 'area')"
        ];
        // if not owner agent, fix the CTEs that change from the above
        if (($type == "coletivo") || ($type == "instituicao")) {
            $ctes["agent"] = "(SELECT ar.object_id, $tbCode.$field%s FROM agent_relation ar JOIN $table $tbCode ON ar.agent_id = $tbCode.id WHERE ar.type = '$type' AND ar.$regType)";
            $ctes["agent_meta"] = "(SELECT ar.object_id, $tbCode.value FROM agent_relation ar JOIN $table $tbCode ON ar.agent_id = $tbCode.object_id WHERE ar.type = '$type' AND ar.$regType AND $tbCode.key = '$field')";
            $ctes["term"] = "(SELECT ar.object_id, $tbCode.$field FROM agent_relation ar JOIN term_relation tr ON ar.agent_id = tr.object_id JOIN $table $tbCode ON tr.term_id = $tbCode.id WHERE ar.type = '$type' AND ar.$regType AND tr.$agentType AND $tbCode.taxonomy = 'area')";
        } else if ($type == "space") {
            $ctes["term"] = "(SELECT sr.object_id, $tbCode.$field FROM space_relation sr JOIN term_relation tr ON sr.space_id = tr.object_id JOIN $table $tbCode ON tr.term_id = $tbCode.id WHERE sr.$regType AND tr.$spaceType AND $tbCode.taxonomy = 'area')";
        }
        return ($ctes[$table] ?? null);
    }

    private function queryTest($op)
    {
        $fields = $this->getValidFields($op);
        $field = null;
        foreach ($fields as $item) {
            if ($item["source"]["table"] == "rm") {
                $field = $item["value"];
                break;
            }
        }
        // single field queries
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "r"]]], $op) ==
               "SELECT r.status AS value0, count(*) AS quantity FROM registration r WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY r.status");
        if ($field) {
            assert($this->buildQuery([["value" => $field, "source" => ["table" => "rm"]]], $op) ==
                   "WITH table0 AS (SELECT rm.object_id, rm.value FROM registration_meta rm WHERE rm.key = '$field') SELECT table0.value AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value");
        }
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "a", "type" => "owner"]]], $op) ==
               "WITH table0 AS (SELECT a.id AS object_id, a.status FROM agent a) SELECT table0.status AS value0, count(*) AS quantity FROM registration r JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "a", "type" => "coletivo"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, a.status FROM agent_relation ar JOIN agent a ON ar.agent_id = a.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration') SELECT table0.status AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "s"]]], $op) ==
               "WITH table0 AS (SELECT sr.object_id, s.status FROM space_relation sr JOIN space s ON sr.space_id = s.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration') SELECT table0.status AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status");
        assert($this->buildQuery([["value" => "En_Estado", "source" => ["table" => "am", "type" => "owner"]]], $op) ==
               "WITH table0 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'En_Estado') SELECT table0.value AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value");
        assert($this->buildQuery([["value" => "En_Estado", "source" => ["table" => "am", "type" => "coletivo"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, am.value FROM agent_relation ar JOIN agent_meta am ON ar.agent_id = am.object_id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration' AND am.key = 'En_Estado') SELECT table0.value AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value");
        assert($this->buildQuery([["value" => "En_Estado", "source" => ["table" => "sm"]]], $op) ==
               "WITH table0 AS (SELECT sr.object_id, sm.value FROM space_relation sr JOIN space_meta sm ON sr.space_id = sm.object_id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration' AND sm.key = 'En_Estado') SELECT table0.value AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value");
        assert($this->buildQuery([["value" => "term", "source" => ["table" => "t", "type" => "owner"]]], $op) ==
               "WITH table0 AS (SELECT tr.object_id, t.term FROM term_relation tr JOIN term t ON tr.term_id = t.id WHERE tr.object_type = 'MapasCulturais\\Entities\\Agent' AND t.taxonomy = 'area') SELECT table0.term AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.term");
        assert($this->buildQuery([["value" => "term", "source" => ["table" => "t", "type" => "coletivo"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, t.term FROM agent_relation ar JOIN term_relation tr ON ar.agent_id = tr.object_id JOIN term t ON tr.term_id = t.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration' AND tr.object_type = 'MapasCulturais\\Entities\\Agent' AND t.taxonomy = 'area') SELECT table0.term AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.term");
        assert($this->buildQuery([["value" => "term", "source" => ["table" => "t", "type" => "space"]]], $op) ==
               "WITH table0 AS (SELECT sr.object_id, t.term FROM space_relation sr JOIN term_relation tr ON sr.space_id = tr.object_id JOIN term t ON tr.term_id = t.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration' AND tr.object_type = 'MapasCulturais\\Entities\\Space' AND t.taxonomy = 'area') SELECT table0.term AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.term");
        // double field queries
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "r"]], ["value" => "consolidated_result", "source" => ["table" => "r"]]], $op) ==
               "SELECT r.status AS value0, r.consolidated_result AS value1, count(*) AS quantity FROM registration r WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY r.status, r.consolidated_result");
        if ($field) {
            assert($this->buildQuery([["value" => $field, "source" => ["table" => "rm"]], ["value" => "consolidated_result", "source" => ["table" => "r"]]], $op) ==
                   "WITH table0 AS (SELECT rm.object_id, rm.value FROM registration_meta rm WHERE rm.key = '$field') SELECT table0.value AS value0, r.consolidated_result AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value, r.consolidated_result");
            assert($this->buildQuery([["value" => "consolidated_result", "source" => ["table" => "r"]], ["value" => $field, "source" => ["table" => "rm"]]], $op) ==
                   "WITH table0 AS (SELECT rm.object_id, rm.value FROM registration_meta rm WHERE rm.key = '$field') SELECT r.consolidated_result AS value0, table0.value AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY r.consolidated_result, table0.value");
        }
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "r"]], ["value" => "status", "source" => ["table" => "a", "type" => "owner"]]], $op) ==
               "WITH table0 AS (SELECT a.id AS object_id, a.status FROM agent a) SELECT r.status AS value0, table0.status AS value1, count(*) AS quantity FROM registration r JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY r.status, table0.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "a", "type" => "owner"]], ["value" => "status", "source" => ["table" => "r"]]], $op) ==
               "WITH table0 AS (SELECT a.id AS object_id, a.status FROM agent a) SELECT table0.status AS value0, r.status AS value1, count(*) AS quantity FROM registration r JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status, r.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "r"]], ["value" => "status", "source" => ["table" => "a", "type" => "coletivo"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, a.status FROM agent_relation ar JOIN agent a ON ar.agent_id = a.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration') SELECT r.status AS value0, table0.status AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY r.status, table0.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "a", "type" => "coletivo"]], ["value" => "status", "source" => ["table" => "r"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, a.status FROM agent_relation ar JOIN agent a ON ar.agent_id = a.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration') SELECT table0.status AS value0, r.status AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status, r.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "a", "type" => "coletivo"]], ["value" => "status", "source" => ["table" => "s"]]], $op) ==
               "WITH table0 AS (SELECT ar.object_id, a.status FROM agent_relation ar JOIN agent a ON ar.agent_id = a.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration'), table1 AS (SELECT sr.object_id, s.status FROM space_relation sr JOIN space s ON sr.space_id = s.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration') SELECT table0.status AS value0, table1.status AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id LEFT OUTER JOIN table1 ON r.id = table1.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status, table1.status");
        assert($this->buildQuery([["value" => "status", "source" => ["table" => "s"]], ["value" => "status", "source" => ["table" => "a", "type" => "coletivo"]]], $op) ==
               "WITH table0 AS (SELECT sr.object_id, s.status FROM space_relation sr JOIN space s ON sr.space_id = s.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration'), table1 AS (SELECT ar.object_id, a.status FROM agent_relation ar JOIN agent a ON ar.agent_id = a.id WHERE ar.type = 'coletivo' AND ar.object_type = 'MapasCulturais\\Entities\\Registration') SELECT table0.status AS value0, table1.status AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id LEFT OUTER JOIN table1 ON r.id = table1.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.status, table1.status");
        assert($this->buildQuery([["value" => "En_Estado", "source" => ["table" => "am", "type" => "owner"]], ["value" => "term", "source" => ["table" => "t", "type" => "space"]]], $op) ==
               "WITH table0 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'En_Estado'), table1 AS (SELECT sr.object_id, t.term FROM space_relation sr JOIN term_relation tr ON sr.space_id = tr.object_id JOIN term t ON tr.term_id = t.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration' AND tr.object_type = 'MapasCulturais\\Entities\\Space' AND t.taxonomy = 'area') SELECT table0.value AS value0, table1.term AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id LEFT OUTER JOIN table1 ON r.id = table1.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.value, table1.term");
        assert($this->buildQuery([["value" => "term", "source" => ["table" => "t", "type" => "space"]], ["value" => "En_Estado", "source" => ["table" => "am", "type" => "owner"]]], $op) ==
               "WITH table0 AS (SELECT sr.object_id, t.term FROM space_relation sr JOIN term_relation tr ON sr.space_id = tr.object_id JOIN term t ON tr.term_id = t.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration' AND tr.object_type = 'MapasCulturais\\Entities\\Space' AND t.taxonomy = 'area'), table1 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'En_Estado') SELECT table0.term AS value0, table1.value AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.id = table0.object_id LEFT OUTER JOIN table1 ON r.agent_id = table1.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY table0.term, table1.value");
        // single field timeseries query
        assert($this->buildQuery([["value" => "En_Estado", "source" => ["table" => "am", "type" => "owner"]]], $op, true) ==
               "WITH table0 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'En_Estado') SELECT to_char(r.create_timestamp, 'YYYY-MM-DD') AS date, table0.value AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY to_char(r.create_timestamp, 'YYYY-MM-DD'), table0.value ORDER BY to_char(r.create_timestamp, 'YYYY-MM-DD'), table0.value ASC");
        // single field age range query
        assert($this->buildQuery([["value" => "dataDeNascimento", "source" => ["table" => "am", "type" => "dateToAge"]]], $op) ==
               "WITH table0 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'dataDeNascimento') SELECT div(date_part('year', age(to_timestamp(table0.value, 'YYYY-MM-DD')))::integer, 5) AS value0, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY div(date_part('year', age(to_timestamp(table0.value, 'YYYY-MM-DD')))::integer, 5)");
        // double field with age range query
        assert($this->buildQuery([["value" => "dataDeNascimento", "source" => ["table" => "am", "type" => "dateToAge"]], ["value" => "term", "source" => ["table" => "t", "type" => "space"]]], $op) ==
               "WITH table0 AS (SELECT am.object_id, am.value FROM agent_meta am WHERE am.key = 'dataDeNascimento'), table1 AS (SELECT sr.object_id, t.term FROM space_relation sr JOIN term_relation tr ON sr.space_id = tr.object_id JOIN term t ON tr.term_id = t.id WHERE sr.object_type = 'MapasCulturais\\Entities\\Registration' AND tr.object_type = 'MapasCulturais\\Entities\\Space' AND t.taxonomy = 'area') SELECT div(date_part('year', age(to_timestamp(table0.value, 'YYYY-MM-DD')))::integer, 5) AS value0, table1.term AS value1, count(*) AS quantity FROM registration r LEFT OUTER JOIN table0 ON r.agent_id = table0.object_id LEFT OUTER JOIN table1 ON r.id = table1.object_id WHERE r.opportunity_id = :opportunity AND r.status > 0 GROUP BY div(date_part('year', age(to_timestamp(table0.value, 'YYYY-MM-DD')))::integer, 5), table1.term");
        return;
    }

    private function fieldDefinition($label, $value, $table, $type=null)
    {
        return [
            "label" => $label,
            "value" => $value,
            "source" => isset($type) ? ["table" => $table, "type" => $type] :
                                       ["table" => $table]
        ];
    }

    private function generateLabel($value, $type, $evalMethod)
    {
        if(empty($value)) {
            return "Não informado";
        }
        if (!isset($value)) {
            return i::__("(dado não informado)");
        }
        if ($type == "dateToAge") {
            return ("" . ($value * 5) . "-" . (($value * 5) + 4));
        }
        if ($type == "valueToString") {
            return $evalMethod->valueToString($value);
        }
        if ($type == "status") {
            return $this->statusToString($value);
        }
        return $value;
    }

    private function getValidFields($opportunity)
    {
        $app = App::i();
        
        $moduleConfig = $app->modules['Reports']->config;
        $fieldsUse = [
            "collective" => $moduleConfig['collective'],
            "agent" => $moduleConfig['agent'],
        ];

        $app->applyHookBoundTo($this,"module(Reports).agentFields",[&$fieldsUse]);

        $fields = [];
        if (!empty($opportunity->registrationCategories)) {
            $fields[] = $this->fieldDefinition(i::__("Categoria"), "category", "r");
        }
        $fields[] = $this->fieldDefinition(i::__("Status"), "status", "r", 'status');
        $fields[] = $this->fieldDefinition(i::__("Avaliação"), "consolidated_result", "r", "valueToString");
        foreach ($opportunity->registrationFieldConfigurations as $value) {
            if ($value->fieldType == "select") {
                $fields[] = $this->fieldDefinition($value->title, $value->fieldName, "rm");
            }
        }
        $agentClass = Agent::getClassName();
        $fields[] = $this->fieldDefinition(i::__("Faixa etária"), "dataDeNascimento", "am", "dateToAge");
        $this->getEntitySelectFields($fields, "owner", $agentClass, "a", $fieldsUse["agent"]);
        $fields[] = $this->fieldDefinition(i::__("Área de atuação do agente responsável"), "term", "t", "owner");
        if (($opportunity->useAgentRelationColetivo ?? "dontUse") != "dontUse") {
            $this->getEntitySelectFields($fields, "coletivo", $agentClass, "a", $fieldsUse["collective"]);
            $fields[] = $this->fieldDefinition(i::__("Área de atuação do agente coletivo"), "term", "t", "coletivo");
        }
        if (($opportunity->useAgentRelationInstituicao ?? "dontUse") != "dontUse") {
            $this->getEntitySelectFields($fields, "instituicao", $agentClass, "a", $fieldsUse["collective"]);
            $fields[] = $this->fieldDefinition(i::__("Área de atuação da instituição"), "term", "t", "instituicao");
        }
        if(($opportunity->useSpaceRelationIntituicao ?? "dontUse") != "dontUse") {
            $this->getEntitySelectFields($fields, "space", Space::getClassName(), "s", $fieldsUse["collective"]);
        }
        return $fields;
    }

    private function getEntitySelectFields(&$selectFields, $type, $entityClass, $baseName,
                                           $includeFields=null)
    {
        $fields = $entityClass::getPropertiesMetadata();
        foreach ($fields as $key => $value) {
            if ((($value["type"] ?? "") == "select") &&
                (!$includeFields || in_array($key, $includeFields))) {
                $selectFields[] = [
                    "label" => $value["label"],
                    "value" => $key,
                    'source' => [
                        "table" => ($baseName . ($value["isMetadata"] ? "m" : "")),
                        "type" => $type
                    ],
                ];
            }
        }
    }

    private function getRegistrationIds()
    {
        $opp = $this->getOpportunity();

        $app = App::i();

        $conn = $app->em->getConnection();

        $params = ['opportunity' => $opp->id];

        $query = "SELECT r.id FROM registration r
        JOIN agent a ON r.agent_id  = a.id
        JOIN opportunity o ON r.opportunity_id = o.id
        WHERE r.opportunity_id = :opportunity";
        return $conn->fetchAll($query, $params);
    }

    /**
     *Retorna a opportunidade
     *
     * @return object
     */
    private function getOpportunity(): Opportunity
    {
        $this->requireAuthentication();
        $request = $this->data;
        return App::i()->repo("Opportunity")->find($request["opportunity_id"]);
    }

    /**
     * Retorna o status em forma de string
     */

     private function statusToString($value)
     {
        switch ($value) {
            case 0:
                return i::__("Rascunho");
            case 1:
                return i::__("Pendente");
            case 2:
                return i::__("Inválida");
            case 3:
                return i::__("Não Selecionada");
            case 8:
                return i::__("Suplente");
            case 10:
                return i::__("Selecionada");
        }
        return null;
     }

    /**
     * Gera o CSV
     *
     * @param array $header
     * @param array $csv_daa
     */
    private function createCsv($header, $csv_data, $action, $opp)
    {
        $date = new DateTime();
        $fileName = $date->format('dmY') . "-" . $action . "-opp-" . $opp . "-" . md5(json_encode($csv_data)) . ".csv";
        $csv = Writer::createFromString();

        $csv->setDelimiter(';');

        $csv->insertOne($header);

        foreach ($csv_data as $csv_line) {
            $csv->insertOne($csv_line);
        }

        $csv->output($fileName);
    }

    private function prepareGroupedSeries($data, $typeA, $typeB, $chartType,
                                          $opportunity, $evalMethod)
    {
        return $this->prepareSeries($data, $typeA, "value1", $chartType,
                                    $opportunity, $evalMethod,
                                    function ($value) use ($typeB,
                                                           $evalMethod) {
            return $this->generateLabel($value, $typeB, $evalMethod);
        });
    }

    private function prepareSeries($data, $type, $key, $chartType,
                                   $opportunity, $evalMethod, $labelCallback)
    {
        $app = App::i();
        $module = $app->modules['Reports'];

        $series = [];
        $points = [];
        $outColours = [];
        $outLines = [];
        $outSeries = [];
        $outPoints = [];

        foreach ($data as $item) {
            $label = $this->generateLabel($item["value0"], $type, $evalMethod);
            if (!isset($series[$label])) {
                $series[$label] = [];
            }
            $series[$label][$item[$key]] = $item["quantity"];
            if ((sizeof($points) < 1) || !in_array($item[$key], $points)) {
                $points[] = $item[$key];
                $outPoints[] = $labelCallback($item[$key]);
            }
        }

        $generate_colors = [];
        foreach (array_keys($series) as $label) {

            $color = $this->getChartColors();

            $current = [
                "label" => $label,
                "colors" => $color[0],
                "type" => $chartType,
                "fill" => false,
                "data" => []
            ];
            foreach ($points as $point) {
                $current["data"][] = $series[$label][$point] ?? 0;
            }
            $outLines[] = $label;
            $outSeries[] = $current;
        }
        return [
            "labels" => $outPoints,
            "series" => $outSeries,
            "legends" => $outLines,
            "colors" => $outColours,
            "typeGraphic" => $chartType,
            "opportunity" => $opportunity->id,
            "borderWidth" => 0
        ];
    }

    private function prepareTimeSeries($data, $type, $opportunity, $evalMethod)
    {
        return $this->prepareSeries($data, $type, "date", "line", $opportunity,
                                    $evalMethod, function ($value) {
            return (new DateTime($value))->format("d/m/Y");
        });
    }

    public function getEntityDailyData(string $table, string $entity_class, array $entity_fields = [], array $metadata = [])
    {
        $conn = App::i()->em->getConnection();

        $data = [];

        $metadata = $entity_class::getPropertiesMetadata(true);

        $filters = $this->getEntityFilters($entity_class);

        $where = implode(' AND ', $filters);

        $data['total'] = (object) [
            'label' => 'Total',
            'sql' => "SELECT count(e.*) as num, 'total' as data_group, create_timestamp::DATE as day FROM {$table} e WHERE 1=1 AND ($where) group by data_group, day order by day, data_group ASC",
        ];

        foreach ($entity_fields as $field) {
            $column = $metadata[$field]['columnName'];
            $data[$field] = (object) [
                'label' => $metadata[$field]['label'],
                'sql' => "SELECT count(e.*) as num, e.{$column} as data_group, create_timestamp::DATE as day FROM {$table} e WHERE 1=1 AND ($where) group by data_group, day order by day, data_group ASC",
            ];
        }

        $result = [];
        foreach ($data as $prop => &$q) {
            $rs = $conn->fetchAll($q->sql);

            $mapped = array_map(function ($row) use ($q, $entity_class, $prop) {
                $row = (object) $row;
                $row->data_group = $this->getFieldValueString($entity_class, $prop, $row->data_group);
                $row->field = $prop;
                $row->field_label = $prop;
                return $row;
            }, $rs);

            $result = array_merge($result, $mapped);
        }
        return $result;
    }

    public function getEntityTotalData(string $table, string $entity_class, array $entity_fields = [], array $metadata = [])
    {
        $conn = App::i()->em->getConnection();

        $metadata = $entity_class::getPropertiesMetadata(true);

        $data = [];

        $filters = $this->getEntityFilters($entity_class);

        $where = implode(' AND ', $filters);

        $data['total'] = (object) [
            'label' => 'Total',
            'sql' => "SELECT count(e.*) as num, 'total' as data_group FROM {$table} e WHERE 1=1 AND ($where)",
        ];

        foreach ($entity_fields as $field) {
            $column = $metadata[$field]['columnName'];
            $data[$field] = (object) [
                'field' => $field,
                'label' => $metadata[$field]['label'],
                'sql' => "SELECT count(e.*) as num, e.{$column} as data_group FROM {$table} e WHERE 1=1 AND ($where) group by data_group",
            ];
        }

        foreach ($data as &$q) {
            $q->data = $conn->fetchAll($q->sql);
        }

        return $data;
    }

    public function getFieldValueString($class_name, $field, $value)
    {
        $app = App::i();

        $result = '';

        switch ($field) {
            case '_type':
                if ($type = $app->getRegisteredEntityTypeById($class_name, $value)) {
                    $result = $type->name;
                }
                break;

            case 'publicLocation':
                if ($value) {
                    $result = 'Pública';
                } else if ($value === false) {
                    $result = 'Privada';
                } else {
                    $result = 'Não informada';
                }
                break;
        }

        return $result;
    }

    public function sortDataByNum(&$data)
    {
        usort($data, function ($a, $b) {
            return $b['num'] <=> $a['num'];
        });
    }

    public function getDayOfFirstEntity($entity)
    {
        $app = App::i();

        $cache_id = __METHOD__ . '::' . $entity;

        if ($app->cache->contains($cache_id)) {
            return $app->cache->fetch($cache_id);
        }

        $conn = $app->em->getConnection();

        $result = $conn->fetchColumn("SELECT MIN(create_timestamp::DATE) FROM {$entity} WHERE id > 1 AND status > 0");

        $app->cache->save($cache_id, $result);

        return $result;
    }

    public function getDays($entity)
    {
        $first_day = $this->data['from'] ?? $this->getDayOfFirstEntity($entity);
        $last_day = $this->data['to'] ?? date('Y-m-d');
        $period = new \DatePeriod(
            new \DateTime($first_day),
            new \DateInterval('P1D'),
            new \DateTime($last_day)
        );

        $result = [];
        foreach ($period as $date) {
            $result[] = $date->format('Y-m-d');
        }

        return $result;
    }

    public function extractDailyData($entity, $daily_data, $field = null, $group = null)
    {
        $days = $this->getDays($entity);

        $result = [];

        foreach ($days as $day) {
            $result[$day] = 0;
        }

        foreach ($daily_data as $row) {
            if ($field) {
                if ($row->field == $field) {
                    if ($group) {
                        if ($row->data_group == $group) {
                            $result[$row->day] = $row->num;
                        }
                    } else {
                        $result[$row->day] = $row->num;
                    }
                }
            } else {
                $result[$row->day] = $row->num;
            }
        }

        return array_values($result);
    }

    public function extractData($data, $field = false)
    {
        $result = [];
        foreach ($data as $row) {
            if (!$field || ($row->field == $field)) {
                $result[] = $row->num;
            }
        }

        return $result;
    }

    public function extractDistinctGroups($data, $field = null)
    {
        $result = [];
        foreach ($data as $row) {
            if (!in_array($row->data_group, $result)) {
                if ($field) {
                    if ($field == $row->field) {
                        $result[] = $row->data_group;
                    }
                } else {
                    $result[] = $row->data_group;
                }
            }
        }

        return $result;
    }
}

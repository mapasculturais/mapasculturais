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
     * Gera CSV das inscrições agrupadas por status
     *
     *
     */
    public function GET_exportRegistrationsByStatus()
    {
        $this->requireAuthentication();

        $app = App::i();

        $conn = $app->em->getConnection();

        $request = $this->data;

        $data = [];
        $params = ['opportunity' => $request['opportunity_id']];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value) {
            switch ($value['status']) {
                case 0:
                    $status = i::__('Rascunho');
                    break;
                case 1:
                    $status = i::__('Pendente');
                    break;
                case 2:
                    $status = i::__('Inválida');
                    break;
                case 3:
                    $status = i::__('Não Selecionada');
                    break;
                case 8:
                    $status = i::__('Suplente');
                    break;
                case 10:
                    $status = i::__('Selecionada');
                    break;
            }

            $data[$status] = $value['count'];
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
     * Gera CSV das inscrições agrupadas por avaliação
     *
     *
     */
    public function GET_exportRegistrationsByEvaluation()
    {
        $this->requireAuthentication();

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

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity']);
    }

    /**
     * Gera CSV das inscrições agrupadas por status
     *
     *
     */
    public function GET_exportRegistrationsDraftVsSent()
    {
        $opp = $this->getOpportunity();

        $request = $this->data;

        $app = App::i();

        $conn = $app->em->getConnection();

        $data = [];
        $params = ['opportunity' => $opp->id];

        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity GROUP BY status";

        $result = $conn->fetchAll($query, $params);

        foreach ($result as $value) {
            switch ($value['status']) {
                case 0:
                    $status = i::__('Rascunho');
                    break;
                case 1:
                    $status = i::__('Pendente');
                    break;
                case 2:
                    $status = i::__('Inválida');
                    break;
                case 3:
                    $status = i::__('Não Selecionada');
                    break;
                case 8:
                    $status = i::__('Suplente');
                    break;
                case 10:
                    $status = i::__('Selecionada');
                    break;
            }

            $data[$status] = $value['count'];
        }

        $csv_data = [];
        $total = 0;
        foreach ($data as $key => $value) {
            if ($key == "Rascunho") {
                $csv_data[0] = ['Rascunho', $value];
            } else {
                $total = ($total + $value);
                $csv_data[1] = ['Enviadas', $total];
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
        foreach ($sent as $key => $value) {
            $result[$count]['status'] = i::__('Enviada');
            $result[$count] += $value;

            $count++;
        }

        foreach ($initiated as $key => $value) {
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

        $this->createCsv($header, $result, $this->data['action'], $opp->id);

    }

    public function POST_loadingGrafic()
    {
        $this->requireAuthentication();

        $opp = $this->getOpportunity();

        $app = App::i();

        $params = ['objectId' => $opp->id, "group" => "reports"];

        $metalist = $app->repo("MetaList")->findBy($params);

        $return = [];
        foreach ($metalist as $data){
            $return[] = json_decode($data->value);
        }

        $this->apiResponse($return);
    }

    public function POST_saveGrafic()
    {
        $this->requireAuthentication();

        $app = App::i();

        $reportData = $this->data['reportData'];

        $opp = $app->repo("Opportunity")->find($reportData['opportunity_id']);

        $value = "";
        $source = "";       
        foreach ($reportData['columns'] as $v){
            $value .= $v['value'];
            $source .= is_array($v['source']) ? implode(",",$v['source']) : $v['source'];
        

        }

        $identifier = md5($reportData['opportunity_id'] . "-" . $reportData['typeGrafic'] . "-" . $source . "-" . $value);
        
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
        $metaList->title = 'Grafico' ;
        $metaList->save(true);
    }

    public function ALL_dataOpportunityReport()
    {
        $this->requireAuthentication();
        $app = App::i();
        $request = $this->data;
        $opportunity = $app->repo("Opportunity")->find($request["opportunity_id"]);
        $this->apiResponse($this->getValidFields($opportunity));
    }

    public function POST_createGrafic()
    {
        $this->requireAuthentication();
        $opp = $this->getOpportunity();
        $em = $opp->getEvaluationMethod();
        $app = App::i();
        $request = $this->data;
        $reportData = $request["reportData"];
        $dataA = $reportData["dataA"];
        $dataB = $reportData["dataB"];
        $conn = $app->em->getConnection();
        $query = $this->buildQuery($dataA, $opp, ($reportData["graficType"] == "line"));
        $result = $conn->fetchAll($query, ["opportunity" => $opp->id]);
        $return = [];
        $labels = [];
        $color = [];
        $data = [];
        // post-processing may be necessary depending on type, so obtain it
        $type = $dataA["source"]["type"] ?? "";
        if ($reportData["graficType"] == "line") {
            $return = $this->prepareTimeSeries($result, $type, $em);
            $return["opportunity"] = $opp->id;
            $return["typeGrafic"] = "line";
            $return["borderWidth"] = 0;
        } else {
            foreach ($result as $item) {
                $color[] = $this->color();
                $labels[] = $this->generateLabel($item["value"], $type, $em);
                $data[] = $item["quantity"];
            }
            $return = [
                "labels" => $labels,
                "backgroundColor" => $color,
                "borderWidth" => 0,
                "data" => $data,
                "typeGrafic" => $reportData["graficType"],
                "period" => $this->getPeriod($opp->createTimestamp, "P1D")
            ];
        }
        $this->apiResponse($return);
    }

    private function buildQuery($data, $op, $timeline=false)
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
        if (!isset($tableDic[$data["source"]["table"]])) {
            $this->jsonError("Invalid parameter.");
        }
        // get all field names for this opportunity to validate received ones
        $fieldList = array_map(function ($item) {
            return $item["value"];
        }, $this->getValidFields($op));
        if (!in_array($data["value"], $fieldList)) {
            $this->jsonError("Invalid parameter.");
        }
        // basic information from the request
        $type = $data["source"]["type"] ?? "";
        $field = $data["value"];
        $tbCode = $data["source"]["table"];
        $table = $tableDic[$tbCode];
        // query parts - head and tail
        $select = $timeline ? "SELECT to_char(r.create_timestamp , 'YYYY-MM-DD') AS date," : "SELECT";
        $tail = $timeline ? ", to_char(r.create_timestamp , 'YYYY-MM-DD') ORDER BY date, value ASC" : "";
        // query parts - object type conditions
        $agentType = "object_type = 'MapasCulturais\\Entities\\Agent'";
        $regType = "object_type = 'MapasCulturais\\Entities\\Registration'";
        $spaceType = "object_type = 'MapasCulturais\\Entities\\Space'";
        // query parts - common registration conditions
        $regWhere = "r.opportunity_id = :opportunity AND r.status > 0";
        // query parts - registration-to-X expressions
        $regToAgent = "JOIN agent a ON r.agent_id = a.id";
        $regToSpace = "(SELECT s.$field, sr.object_id FROM space_relation sr JOIN space s ON s.id = sr.space_id WHERE $regType)";
        // query parts - top-level select
        $selMain = "$select $tbCode.$field AS value, count(*) AS quantity FROM registration r";
        $selMeta = "$select $tbCode.value, count(*) AS quantity FROM registration r";
        // query parts - group expressions
        $groupMeta = "GROUP BY $tbCode.value$tail";
        $groupMain = "GROUP BY $tbCode.$field" . $tail;
        // query parts - subqueries
        $metaSubQuery = "(SELECT object_id, value FROM $table WHERE key = '$field')";
        $termSubQuery = "(SELECT $tbCode.$field, tr.object_id FROM $table $tbCode JOIN term_relation tr ON tr.term_id = $tbCode.id WHERE $tbCode.taxonomy = 'area' AND tr.$agentType) AS $tbCode ON $tbCode.object_id = r.agent_id";
        // the dateToAge type requires a special conversion; handling main case is probably unnecessary
        if ($type == "dateToAge") {
            $selMain = "$select div(date_part('year', age(to_timestamp($tbCode.$field, 'YYYY-MM-DD')))::integer, 5) AS value, count(*) AS quantity FROM registration r";
            $groupMain = "GROUP BY div(date_part('year', age(to_timestamp($tbCode.$field, 'YYYY-MM-DD')))::integer, 5)$tail";
            $selMeta = "$select div(date_part('year', age(to_timestamp($tbCode.value, 'YYYY-MM-DD')))::integer, 5) AS value, count(*) AS quantity FROM registration r";
            $groupMeta = "GROUP BY div(date_part('year', age(to_timestamp($tbCode.value, 'YYYY-MM-DD')))::integer, 5)$tail";
        // these types of agents are obtained from agent_relation
        } else if (($type == "coletivo") || ($type == "instituicao")) {
            $idField = ($table == "agent") ? "id" : "object_id";
            $selTarget = str_ends_with($table, "_meta") ? "$tbCode.value" : "$tbCode.$field";
            $regToAgent = "LEFT OUTER JOIN (SELECT $selTarget, ar.agent_id AS id, ar.object_id FROM agent_relation ar JOIN $table $tbCode ON ar.agent_id = $tbCode.$idField WHERE ar.$regType AND ar.type = '$type') AS a ON a.object_id = r.id";
            $termSubQuery = "(SELECT $tbCode.$field, ar.object_id FROM agent_relation ar JOIN term_relation tr ON tr.object_id = ar.agent_id JOIN $table $tbCode ON $tbCode.id = tr.term_id WHERE ar.$regType AND ar.type = '$type' AND $tbCode.taxonomy = 'area' AND tr.$agentType AND tr.object_id = ar.agent_id) AS $tbCode ON $tbCode.object_id = r.id";
        // handle term searches for spaces
        } else if ($type == "space") {
            $termSubQuery = "(SELECT $tbCode.$field, sr.object_id FROM space_relation sr JOIN term_relation tr ON tr.object_id = sr.space_id JOIN $table $tbCode ON $tbCode.id = tr.term_id WHERE sr.$regType AND $tbCode.taxonomy = 'area' AND tr.$spaceType AND tr.object_id = sr.space_id) AS $tbCode ON $tbCode.object_id = r.id";
        }
        // possible queries using the query parts above, by source table
        $sqls = [
            "registration" => "$selMain WHERE $regWhere $groupMain",
            "registration_meta" => "$selMeta LEFT OUTER JOIN $metaSubQuery AS $tbCode ON r.id = $tbCode.object_id WHERE $regWhere $groupMeta",
            "agent" => "$selMain $regToAgent WHERE $regWhere $groupMain",
            "agent_meta" => "$selMeta $regToAgent LEFT OUTER JOIN $metaSubQuery AS $tbCode ON a.id = $tbCode.object_id WHERE $regWhere $groupMeta",
            "space" => "$selMain LEFT OUTER JOIN $regToSpace AS $tbCode ON $tbCode.object_id = r.id WHERE $regWhere $groupMain",
            "space_meta" => "$selMeta LEFT OUTER JOIN (SELECT sr.object_id, $tbCode.value FROM space_relation sr JOIN $table $tbCode ON sr.space_id = $tbCode.object_id WHERE sr.$regType AND $tbCode.key = '$field') AS $tbCode ON $tbCode.object_id = r.id WHERE $regWhere $groupMeta",
            "term" => "$selMain LEFT OUTER JOIN $termSubQuery WHERE $regWhere $groupMain",
        ];
        return $sqls[$table];
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
        if (!isset($value)) {
            return i::__("(dado não informado)");
        }
        if ($type == "dateToAge") {
            return ("" . ($value * 5) . "-" . (($value * 5) + 4));
        }
        if ($type == "valueToString") {
            return $evalMethod->valueToString($value);
        }
        return $value;
    }

    private function getValidFields($opportunity)
    {
        $fieldsUse = [
            "collective" => [
                "En_Estado",
                "En_Municipio",
            ],
            "agent" => [
                "raca",
                "genero",
                "orientacaoSexual",
                "En_Estado",
                "En_Municipio",
                "En_Bairro",
                "dataDeNascimento",
            ],
        ];
        //$dataOpportunity = $opportunity->getEvaluationCommittee();
        $fields = [];
        if (!empty($opportunity->registrationCategories)) {
            $fields[] = $this->fieldDefinition(i::__("Categoria"), "category", "r");
        }
        $fields[] = $this->fieldDefinition(i::__("Status"), "status", "r");
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

    private function getPeriod($dateStart, $period)
    {
        $period = new \DatePeriod(
            $dateStart,
            new \DateInterval($period),
            new \DateTime()
        );

        $return = [];

        foreach ($period as $recurrence) {
            $return[] =  $recurrence->format('Y-m-d');
        }

        return $return;

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

    private function prepareTimeSeries($data, $type, $evalMethod)
    {
        $series = [];
        $times = [];
        $outColours = [];
        $outLines = [];
        $outSeries = [];
        $outTimes = [];
        foreach ($data as $item) {
            $label = $this->generateLabel($item["value"], $type, $evalMethod);
            if (!isset($series[$label])) {
                $series[$label] = [];
            }
            $series[$label][$item["date"]] = $item["quantity"];
            if ((sizeof($times) < 1) ||
                ($times[sizeof($times) - 1] != $item["date"])) {
                $times[] = $item["date"];
                $outTimes[] = (new DateTime($item["date"]))->format("d/m/Y");
            }
        }
        foreach (array_keys($series) as $label) {
            $current = [
                "label" => $label,
                "colors" => $this->color(),
                "type" => "line",
                "fill" => false,
                "data" => []
            ];
            foreach ($times as $time) {
                $current[] = $series[$label][$time] ?? 0;
            }
            $outLines[] = $label;
            $outSeries[] = $current;
        }
        return [
            "labels" => $outTimes,
            "series" => $outSeries,
            "legends" => $outLines,
            "colors" => $outColours
        ];
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

    private function color()
    {
        mt_srand((double) microtime() * 1000000);
        $c = '';
        while (strlen($c) < 6) {
            $c .= sprintf("%02X", mt_rand(0, 255));
        }
        return "#" . $c;
    }
}

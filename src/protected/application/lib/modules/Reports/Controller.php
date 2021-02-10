<?php

namespace Reports;

use DateTime;
use MapasCulturais\i;
use League\Csv\Writer;
use MapasCulturais\App;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

class Controller extends \MapasCulturais\Controller
{
    protected function fetch($sql, $params = [])
    {
        $app = App::i();

        $conn = $app->em->getConnection();

        $conn->fetchAll($sql, $params);
    }

    function GET_agents()
    {
        $props = ['_type'];
        $meta = ['En_Estado', 'En_Cidade', 'acessibilidade'];

        $daily_data = $this->getEntityDailyData('agent', Agent::class, $props, $meta);
        $total_data = $this->getEntityDailyData('agent', Agent::class, $props, $meta);

        $this->render('agents', ['total_data' => $total_data, 'daily_data' => $daily_data]);
    }
    function GET_spaces()
    {
        $props = ['_type'];
        $meta = ['En_Estado', 'En_Cidade', 'acessibilidade'];

        $daily_data = $this->getEntityDailyData('space', Space::class, $props, $meta);
        $total_data = $this->getEntityDailyData('space', Space::class, $props, $meta);

        $this->render('spaces', ['total_data' => $total_data, 'daily_data' => $daily_data]);
    }
    function GET_events()
    {
        $this->entityReport('event', Event::class);
    }
    function GET_projects()
    {
        $this->entityReport('project', Project::class);
    }
    function GET_opportunities()
    {
        $this->entityReport('opportunity', Opportunity::class);
    }
    function GET_files()
    {
        $this->entityReport('file', File::class);
    }
    function GET_registrations()
    {
        $this->entityReport('registration', Registration::class);
    }

    function getEntityFilters($entity_class)
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
        $app = App::i();
        
        $conn = $app->em->getConnection();

        $request = $this->data;      
        
        $data = [];
        $params = ['opportunity_id' => $request['opportunity_id']];
        
        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity_id GROUP BY status";

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
        foreach ($data as $key => $value){
            $csv_data[] = [$key, $value];
        }
 
        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE')
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
        $app = App::i();
    
        $conn = $app->em->getConnection();

        $request = $this->data;
        
        $data = [];
        $params = ['opportunity_id' => $request['opportunity_id']];

        $query = "SELECT count(*) AS evaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result <> '0'";

        $evaluated = $conn->fetchAll($query, $params);

        $query = "SELECT COUNT(*) AS notEvaluated FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result = '0'";

        $notEvaluated = $conn->fetchAll($query, $params);

        $data = array_merge($evaluated, $notEvaluated);
        
        foreach($data as $m){
            foreach ($m as $v){
              if(empty($v)){
                  return false;
              }
            }
        }
      
        $result = [];
        foreach($data as $m){
            foreach ($m as $key => $v){
                $result[] = [$key, $v];
            }
        }

        $csv_data = [];
        $csv_data = array_map(function($index){
            if($index[0] == "evaluated"){
                return [
                    i::__('AVALIADA'),
                    $index[1]
                ];
            }else{
                return [
                    i::__('NAO AVALIADA'),
                    $index[1]
                ];
            }
        },$result);        
      
        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE')
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
        $app = App::i();       

        $request = $this->data;
        
        $opp = $app->repo("Opportunity")->find($request['opportunity_id']);

        $em = $opp->getEvaluationMethod();
        
        $conn = $app->em->getConnection();
        
        $data = [];
        $params = ['opportunity_id' => $request['opportunity_id']];

        $query = "SELECT COUNT(*), consolidated_result FROM registration r WHERE opportunity_id = :opportunity_id  AND consolidated_result <> '0' GROUP BY consolidated_result";

        $evaluations = $conn->fetchAll($query, $params);

        $cont = 0;
        foreach ($evaluations as $evaluation) {
            if ($cont < 8) {
                $data[$em->valueToString($evaluation['consolidated_result'])] = $evaluation['count'];
                $cont++;
            }
        }

        $csv_data = [];
        foreach ($data as $key => $value){
            $csv_data[] = [$key, $value];
        }
        
        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE')
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
        $app = App::i();

        $request = $this->data;
        
        $opp = $app->repo("Opportunity")->find($request['opportunity_id']);

        $em = $opp->getEvaluationMethod();
          
        $conn = $app->em->getConnection();
    
        $csv_data = [];
        $params = ['opportunity_id' => $opp->id];

        $query = "select  category, count(category) from registration r where r.status > 0 and r.opportunity_id = :opportunity_id group by category";

        $csv_data = $conn->fetchAll($query, $params);

        foreach ($csv_data as $value){
            foreach ($value as $v)
            {
                if(empty($v)){
                    return false;
                }
            }
        }
       
        $header = [
            i::__('CATEGORIA'),
            i::__('QUANTIDADE'),
        ];

        $this->createCsv($header, $csv_data, $request['action'], $request['opportunity_id']);
    }

     /**
     * Gera CSV das inscrições agrupadas por status
     *
     *
     */
    public function GET_exportRegistrationsDraftVsSent()
    {
        $app = App::i();
        
        $conn = $app->em->getConnection();

        $request = $this->data;      
        
        $data = [];
        $params = ['opportunity_id' => $request['opportunity_id']];
        
        $query = "SELECT status, count(*) FROM registration r WHERE opportunity_id = :opportunity_id GROUP BY status";

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
        foreach ($data as $key => $value){
            if($key == "Rascunho"){
                $csv_data[0] = ['Rascunho', $value];
            }else{                
                $total = ($total + $value);
                $csv_data[1] = ['Enviadas', $total];
            }
            
        }
        
        $header = [
            i::__('STATUS'),
            i::__('QUANTIDADE')
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
        $app = App::i();

        $request = $this->data;      

        //Pega conexão
        $conn = $app->em->getConnection();

        //Seleciona e agrupa inscrições ao longo do tempo
        $initiated = [];
        $sent = [];
        $params = ['opportunity_id' => $request['opportunity_id']];

        $query = "SELECT
        to_char(create_timestamp , 'YYYY-MM-DD') as date,
        count(*) as total
        FROM registration r
        WHERE opportunity_id = :opportunity_id
        GROUP BY to_char(create_timestamp , 'YYYY-MM-DD')
        ORDER BY date ASC";
        $initiated = $conn->fetchAll($query, $params);
        

        $query = "SELECT
        to_char(sent_timestamp , 'YYYY-MM-DD') as date,
        count(*) as total
        FROM registration r
        WHERE opportunity_id = :opportunity_id AND r.status > 0
        GROUP BY to_char(sent_timestamp , 'YYYY-MM-DD')
        ORDER BY date ASC";
        $sent = $conn->fetchAll($query, $params);       
        
        if(!$sent || !$initiated){
            return false;
        }
        
        $header = [
            i::__('STATUS'),
            i::__('DATA'),
            i::__('QUANTIDADE')
        ];

        $result = [];
        $count = 0;
        foreach($sent as $key => $value){
            $result[$count]['status'] =  i::__('Enviada');
            $result[$count] += $value;

            $count ++;
        }

        foreach($initiated as $key => $value){
            $result[$count]['status'] =  i::__('Iniciada');
            $result[$count] += $value;

            $count ++;

        }
        
        $return = array_map(function($index){
            $date = new DateTime($index['date']);
            return [
                'status' => $index['status'],
                'data' => $date->format('d/m/Y'),
                'total' => $index['total'],

            ];
        }, $result);

        $this->createCsv($header, $result, $request['action'], $request['opportunity_id']);

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
        $fileName =$date->format('dmY')."-".$action."-opp-".$opp."-".md5(json_encode($csv_data)).".csv";
        $csv = Writer::createFromString();

        $csv->setDelimiter(';');

        $csv->insertOne($header);

        foreach ($csv_data as $csv_line) {
            $csv->insertOne($csv_line);
        }

        $csv->output($fileName);
    }


    function getEntityDailyData(string $table, string $entity_class, array $entity_fields = [], array $metadata = [])
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

    function getEntityTotalData(string $table, string $entity_class, array $entity_fields = [], array $metadata = [])
    {
        $conn = App::i()->em->getConnection();

        $metadata = $entity_class::getPropertiesMetadata(true);

        $data = [];

        $filters = $this->getEntityFilters($entity_class);

        $where = implode(' AND ', $filters);

        $data['total'] = (object) [
            'label' => 'Total',
            'sql' => "SELECT count(e.*) as num, 'total' as data_group FROM {$table} e WHERE 1=1 AND ($where)"
        ];

        foreach ($entity_fields as $field) {
            $column = $metadata[$field]['columnName'];
            $data[$field] = (object) [
                'field' => $field,
                'label' => $metadata[$field]['label'],
                'sql' => "SELECT count(e.*) as num, e.{$column} as data_group FROM {$table} e WHERE 1=1 AND ($where) group by data_group"
            ];
        }

        foreach ($data as &$q) {
            $q->data = $conn->fetchAll($q->sql);
        }

        return $data;
    }

    function getFieldValueString($class_name, $field, $value)
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

    function sortDataByNum(&$data)
    {
        usort($data, function ($a, $b) {
            return $b['num'] <=> $a['num'];
        });
    }

    function getDayOfFirstEntity($entity)
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


    function getDays($entity)
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

    function extractDailyData($entity, $daily_data, $field = null, $group = null) {
        $days = $this->getDays($entity);

        $result = [];

        foreach($days as $day) {
            $result[$day] = 0;
        }

        foreach($daily_data as $row) {
            if($field) {
                if($row->field == $field){
                    if($group) {
                        if($row->data_group == $group){
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

    function extractData($data, $field = false)
    {
        $result = [];
        foreach($data as $row) {
            if(!$field || ($row->field == $field)){
                $result[] = $row->num;
            }
        }

        return $result;
    }

    function extractDistinctGroups($data, $field = null)
    {
        $result = [];
        foreach($data as $row) {
            if(!in_array($row->data_group, $result)) {
                if($field){
                    if($field == $row->field) {
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

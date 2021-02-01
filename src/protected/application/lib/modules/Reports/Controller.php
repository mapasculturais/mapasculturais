<?php

namespace Reports;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Space;

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

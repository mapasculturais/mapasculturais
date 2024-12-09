<?php

namespace Spreadsheets;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\i;

/**
 * @property-read string $fileGroup
 * @package Spreadsheets
 */
abstract class EvaluationsSpreadsheetJob extends SpreadsheetJob
{
    function _getHeader(Job $job): array
    {
        // Parte comum a todos os métodos de avaliação
        $entity_class_name = $job->entityClassName;     
        $registration_class_name = Registration::class;

        $query = $job->query;
        $properties = explode(',', $query['@select']);
        
        $header = [
            'A1:H1' => i::__('Informações sobre as inscrições e proponentes'), 
            'I1' => i::__('Informações sobre o avaliador'),
        ];

        $sub_header = [];
        $total_properties = 0;
        $job->owner->registerRegistrationMetadata(true);
        foreach($properties as $property) {
            if (!in_array($property, ['result', 'status', 'evaluationData'])) {
                $total_properties++;

                if($property === 'projectName') {
                    $sub_header[$property] = i::__('Nome do projeto');
                    continue;
                }

                if(str_starts_with($property, 'owner.{')) {
                    $values = $this->extractValues($property);

                    foreach($values as $val) {
                        if($val === 'name') {
                            $sub_header[$val] = i::__('Agente responsável');
                        }
                    }
                    continue;
                }
                
                if($property === 'user') {
                    $sub_header[$property] = i::__('Nome');
                    continue;
                }

                $sub_header[$property] = $registration_class_name::getPropertyLabel($property) ?: $property;
            }
        }

        // Parte dos dados da avaliação
        $data_header = $this->getEvaluationDataHeader($job, $total_properties);
        $header = isset($data_header['header']) ? array_merge($header, $data_header['header']) : $header;
        $sub_header = isset($data_header['subHeader']) ? array_merge($sub_header, $data_header['subHeader']) : $sub_header;
        $column_prefixes = $data_header['columnPrefixes'] ?? $this->generateSpreadsheetStructure(1, 300);

        // Parte do parecer/resultado da avaliação
        $result_header = $this->getEvaluationResultHeader($job, $properties, $column_prefixes);

        $header = isset($result_header['header']) ? array_merge($header, $result_header['header']) : $header;
        $sub_header = isset($result_header['subHeader']) ? array_merge($sub_header, $result_header['subHeader']) : $sub_header;
        
        $result = [$header, $sub_header];

        return $result;
    }

    protected function _getBatch(Job $job) : array {
        $app = App::i();
        
        $opportunity = $job->owner;

        $query = [];
        $query['@limit'] = $this->limit;
        $query['@page'] = $this->page;
        $query['@order'] = $job->query['@order'] ?? 'id ASC';
        $opportunity_controller = $app->controller('opportunity');
        $opportunity_controller->data = $opportunity_controller->postData;
        $evaluations = $opportunity_controller->apiFindEvaluations($opportunity->id, $query);
        $evaluations = json_decode(json_encode($evaluations), true);

        $result = $this->getEvaluationDataBatch($job, $evaluations);
        return $result;
    }

    function getSpreadsheetColumnName($index) {
        $column_name = '';
        
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $column_name = chr($mod + 65) . $column_name;
            $index = (int)(($index - $mod) / 26);
        }

        return $column_name;
    }
    
    function generateSpreadsheetStructure($num_rows, $num_cols) {
        $sheet = [];
        for ($row = 1; $row <= $num_rows; $row++) {
            for ($col = 1; $col <= $num_cols; $col++) {
                $column_name = $this->getSpreadsheetColumnName($col);
                $cell_reference = $column_name;
                $sheet[] = $cell_reference;
            }
        }
        return $sheet;
    }

    function statusName($status) {
        if($status === 10) {
            return i::__('Selecionada');
        } elseif($status === 8) {
            return i::__('Suplente');
        } elseif($status === 3) {
            return i::__('Não selecionada');
        } elseif($status === 2) {
            return i::__('Inválida');
        } elseif($status === 1) {
            return i::__('Pendente');
        } else {
            return i::__('Rascunho');
        }
    }

    function getEvaluationDataHeader(Job $job, int $total_properties)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataHeader:before", [$job, $total_properties]);

        $result = $this->_getEvaluationDataHeader($job, $total_properties);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataHeader:after", [$job, $total_properties, &$result]);

        return $result;
    }

    function getEvaluationResultHeader(Job $job, array $properties, array $column_prefixes)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationResultHeader:before", [$job, $properties, $column_prefixes]);

        $result = $this->_getEvaluationResultHeader($job, $properties, $column_prefixes);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationResultHeader:after", [$job, $properties, $column_prefixes, &$result]);

        return $result;
    }

    function getEvaluationDataBatch(Job $job, array $evaluations)
    {
        $app = App::i();
        
        $slug = $job->owner->evaluationMethod->slug;

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataBatch:before", [$job, $evaluations]);

        $result = $this->_getEvaluationDataBatch($job, $evaluations);

        $app->applyHookBoundTo($this, "EvaluationsSpreadsheetJob($slug).getEvaluationDataBatch:after", [$job, $evaluations, &$result]);

        return $result;
    }

    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationDataHeader(Job $job, int $total_properties): array;
    
    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationResultHeader(Job $job, array $properties, array $column_prefixes): array;

    /**
     * 
     * @return array[]
     */
    abstract protected function _getEvaluationDataBatch(Job $job, array $evaluations): array;
}

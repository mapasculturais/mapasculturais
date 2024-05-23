<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use Spreadsheets\SpreadsheetJob;

class EvaluationMethodQualification extends SpreadsheetJob
{
    protected function _getFileGroup() : string {
        return $this->slug;
    }

    protected function _getTargetEntities() : array {
        return [RegistrationEvaluation::class];
    }

    protected function _getHeader(Job $job) : array {
        $sub_header = [];

        $entity_class_name = $job->entityClassName;
        $opportunity = $job->owner;
        $sections = json_decode(json_encode($opportunity->evaluationMethodConfiguration->sections), true);
        $criteria = json_decode(json_encode($opportunity->evaluationMethodConfiguration->criteria), true);

        $query = $job->query;
        $properties = explode(',', $query['@select']);
        array_unshift($properties, "projectName", "category");
        
        $header = [
            'A1:D1' => i::__('Informações sobre as inscrições e proponentes'), 
            'E1' => i::__('Informações sobre o avaliador'),
        ];
        
        $column_prefixes = $this->generateSpreadsheetStructure(1, 300);
        array_splice($column_prefixes, 0, 5);

        foreach($properties as $property) {
            if (!in_array($property, ['result', 'status', 'evaluationData'])) {
                $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
            } else {
                if($property === 'evaluationData') {
                    $evaluations_fields['reasonDisqualification'] = i::__('Motivo(s) da inabilitação');
                    $evaluations_fields['obs'] = i::__('Observações');
                } else {
                    $evaluations_fields[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
                }
            }
        }

        foreach($sections as &$section) {
            $section['criteria'] = array_filter($criteria, function($cri) use ($section) {
                return $cri['sid'] === $section['id'];
            });
            
            foreach($section['criteria'] as $cri) {
                $sub_header[$cri['id']] = $cri['name'];
            }

            $total_criteria = count($section['criteria']);
            $columns = array_splice($column_prefixes, 0, $total_criteria);

            $first_column = reset($columns);
            $last_column = end($columns);
            
            $header["{$first_column}1:{$last_column}1"] = $section['name'];
        }

        $columns_evaluations = array_splice($column_prefixes, 0, 4);
        $first_column_evaluation = reset($columns_evaluations);
        $last_column_evaluation = end($columns_evaluations);

        $header["{$first_column_evaluation}1:{$last_column_evaluation}1"] = i::__('Avaliações');

        $sub_header += $evaluations_fields;
        $result = [$header, $sub_header];
        
        return $result;
    }

    protected function _getBatch(Job $job) : array {
        $app = App::i();
        
        $opportunity = $job->owner;

        $query = [];
        $query['@limit'] = $this->limit;
        $query['@page'] = $this->page;

        $opportunity_controller = $app->controller('opportunity');
        $opportunity_controller->data = $opportunity_controller->postData;
        $evaluations = $opportunity_controller->apiFindEvaluations($opportunity->id, $query);

        $result = [];
        foreach ($evaluations->evaluations as $evaluation) {
            $evaluation_data = $evaluation['evaluation']['evaluationData'] ?? [];
            
            $result[] = [
                'projectName' => $evaluation['registration']['projectName'],
                'category' => $evaluation['registration']['category'],
                'owner.{name}' => $evaluation['registration']['owner']['name'],
                'registration' => $evaluation['registration']['number'],
                'user' => $evaluation['valuer']['name'],
                'result' => $evaluation['evaluation']['resultString'] ?? null,
                'status' => $evaluation['registration']['status'],
                'reasonDisqualification' => $evaluation['registration']['evaluationResultString']
            ] + $evaluation_data;
        }
        return $result;
    }

    protected function _getFilename(Job $job) : string {
        $entity_class_name = $job->entityClassName;
        $label = $entity_class_name::getEntityTypeLabel(true);
        $extension = $job->extension;
        $date = date('Y-m-d H:i:s');

        $result = "{$label}-{$date}.{$extension}";

        return $result;
    }

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $md5 = md5(json_encode([
            $data,
            $start_string,
            $interval_string,
            $iterations
        ]));

        return "evaluationsSpreadsheet:{$md5}";
    }

    function getExcelColumnName($index) {
        $columnName = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $columnName = chr($mod + 65) . $columnName;
            $index = (int)(($index - $mod) / 26);
        }
        return $columnName;
    }
    
    function generateExcelSheetStructure($numRows, $numCols) {
        $sheet = [];
        for ($row = 1; $row <= $numRows; $row++) {
            for ($col = 1; $col <= $numCols; $col++) {
                $columnName = $this->getExcelColumnName($col);
                $cellReference = $columnName;
                $sheet[] = $cellReference;
            }
        }
        return $sheet;
    }
}
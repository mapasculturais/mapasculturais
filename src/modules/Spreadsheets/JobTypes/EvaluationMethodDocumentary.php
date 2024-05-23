<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use Spreadsheets\SpreadsheetJob;

class EvaluationMethodDocumentary extends SpreadsheetJob
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
        $evaluations_fields_configurations = $opportunity->getRegistrationFieldConfigurations();
        $evaluations_files_configurations = $opportunity->getRegistrationFileConfigurations();

        $evaluations_fields = array_merge($evaluations_fields_configurations, $evaluations_files_configurations);

        $query = $job->query;
        $properties = explode(',', $query['@select']);
        
        $header = [
            'A1:B1' => i::__('Informações sobre as inscrições e proponentes'), 
            'C1' => i::__('Informações sobre o avaliador'),
        ];
        
        $column_prefixes = $this->generateExcelSheetStructure(1, 300);
        array_splice($column_prefixes, 0, 3);

        foreach($properties as $property) {
            if (!in_array($property, ['result', 'status', 'evaluationData'])) {
                $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
            } else {
                if($property != 'evaluationData') {
                    $sub_header_fields[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
                }
            }
        }
        
        foreach($evaluations_fields as $evaluation_field) {
            $columns = array_splice($column_prefixes, 0, 3);
            $first_column = reset($columns);
            $last_column = end($columns);
            
            $header["{$first_column}1:{$last_column}1"] = $evaluation_field->title;

            $sub_header['evaluation-'.$evaluation_field->id] = i::__('Avaliação');
            $sub_header['obs-'.$evaluation_field->id] = i::__('Observações');
            $sub_header['obs-item-'.$evaluation_field->id] = i::__('Descumprimento do(s) item(s) do edital');
        }

        $columns_evaluations = array_splice($column_prefixes, 0, 2);
        $first_column_evaluation = reset($columns_evaluations);
        $last_column_evaluation = end($columns_evaluations);

        $header["{$first_column_evaluation}1:{$last_column_evaluation}1"] = i::__('Avaliações');
        
        $sub_header += $sub_header_fields;

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

            $result_evaluation_data = [];
            foreach($evaluation_data as $key => $value) {
                $result_evaluation_data['evaluation-' . $key] = $value['evaluation'];
                $result_evaluation_data['obs-' . $key] = $value['obs'];
                $result_evaluation_data['obs-item-' . $key] = $value['obs_items'];
            }

            $result[] = [
                'owner.{name}' => $evaluation['registration']['owner']['name'],
                'registration' => $evaluation['registration']['number'],
                'user' => $evaluation['valuer']['name'],
                'result' => $evaluation['evaluation']['resultString'] ?? null,
                'status' => $evaluation['registration']['status'],
            ] + $result_evaluation_data;
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

    function getFieldLabel($field_name, $opportunity)
    {
        $app = App::i();
        
        /** @var Opportunity $opportunity */
        $opportunity->registerRegistrationMetadata();

        $field = $app->getRegisteredMetadataByMetakey($field_name, Registration::class);

        return $field->label;
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
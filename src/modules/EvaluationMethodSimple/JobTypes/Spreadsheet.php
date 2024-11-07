<?php
namespace EvaluationMethodSimple\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use Spreadsheets\EvaluationsSpreadsheetJob;

class Spreadsheet extends EvaluationsSpreadsheetJob
{
    protected function _getFileGroup() : string {
        return $this->slug;
    }

    protected function _getTargetEntities() : array {
        return [RegistrationEvaluation::class];
    }

    protected function _getEvaluationDataHeader(Job $job, $total_properties) : array {
        $column_prefixes = $this->generateSpreadsheetStructure(1, 300);
        array_splice($column_prefixes, 0, $total_properties);

        return ['header' => null, 'subHeader' => null, 'columnPrefixes' => $column_prefixes];
    }

    protected function _getEvaluationResultHeader(Job $job, $properties, $column_prefixes) : array {
        $entity_class_name = $job->entityClassName;

        $sub_header = [];
        foreach($properties as $property) {
            if (in_array($property, ['result', 'status', 'evaluationData'])) {
                if($property === 'evaluationData') {
                    $sub_header['obs'] = i::__('Observações');
                    continue;
                }
                
                if($property === 'result') {
                    $sub_header[$property] = i::__('Resultado');
                    continue;
                }

                $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
            }
        }

        $columns_evaluations = array_splice($column_prefixes, 0, count($sub_header));
        $first_column_evaluation = reset($columns_evaluations);
        $last_column_evaluation = end($columns_evaluations);
        
        $header["{$first_column_evaluation}1:{$last_column_evaluation}1"] = i::__('Avaliações');

        return ['header' => $header, 'subHeader' => $sub_header];
    }

    protected function _getEvaluationDataBatch(Job $job, $evaluations) : array {
        $result = [];
        foreach ($evaluations['evaluations'] as $evaluation) {
            $registration_data = $evaluation['registration'];

            $result[] = [
                'projectName' => $registration_data['projectName'],
                'category' => $registration_data['category'],
                'name' => $registration_data['owner']['name'],
                'number' => $registration_data['number'],
                'range' => $registration_data['range'],
                'score' => $registration_data['score'],
                'proponentType' => $registration_data['proponentType'],
                'eligible' => $registration_data['eligible'],
                'user' => $evaluation['valuer']['name'],
                'result' => $evaluation['evaluation']['resultString'],
                'status' => $this->statusName($registration_data['status']),
                'obs' => $evaluation['evaluation']['evaluationData']['obs']
            ];
        }
        
        return $result;
    }

    protected function _getFilename(Job $job) : string {
        $opportunity = i::__('oportunidade');
        $opportunity_id = $job->owner->id;
        $extension = $job->extension;
        $date = date('Y-m-d H:i:s');
        
        $result = "{$opportunity}-{$opportunity_id}--avaliacoes-{$date}.{$extension}";

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
}
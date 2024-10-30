<?php
namespace EvaluationMethodDocumentary\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Registration;
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
        $opportunity = $job->owner;

        $evaluations_fields_configurations = $opportunity->getRegistrationFieldConfigurations();
        $evaluations_files_configurations = $opportunity->getRegistrationFileConfigurations();
        $evaluations_fields = array_merge($evaluations_fields_configurations, $evaluations_files_configurations);

        $column_prefixes = $this->generateSpreadsheetStructure(1, 900);
        array_splice($column_prefixes, 0, $total_properties);

        $header = [];
        $sub_header = [];
        foreach($evaluations_fields as $evaluation_field) {
            $columns = array_splice($column_prefixes, 0, 3);
            $first_column = reset($columns);
            $last_column = end($columns);
            
            $header["{$first_column}1:{$last_column}1"] = $evaluation_field->title;

            $sub_header['evaluation-'.$evaluation_field->id] = i::__('Avaliação');
            $sub_header['obs-'.$evaluation_field->id] = i::__('Observações');
            $sub_header['obs-item-'.$evaluation_field->id] = i::__('Descumprimento do(s) item(s) do edital');
        }

        return ['header' => $header, 'subHeader' => $sub_header, 'columnPrefixes' => $column_prefixes];
    }

    protected function _getEvaluationResultHeader(Job $job, $properties, $column_prefixes) : array {
        $entity_class_name = $job->entityClassName;

        $sub_header = [];
        foreach($properties as $property) {
            if (in_array($property, ['result', 'status', 'evaluationData'])) {
                if($property != 'evaluationData') {
                    if($property === 'result') {
                        $sub_header[$property] = i::__('Resultado');
                        continue;
                    }

                    $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
                }
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
            $evaluation_data = $evaluation['evaluation']['evaluationData'] ?? [];

            $result_evaluation_data = [];
            foreach($evaluation_data as $key => $value) {
                $result_evaluation_data['evaluation-' . $key] = $this->translateResult($value['evaluation']);
                $result_evaluation_data['obs-' . $key] = $value['obs'];
                $result_evaluation_data['obs-item-' . $key] = $value['obsItems'];
            }

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
                'result' => $evaluation['evaluation']['resultString'] ?? null,
                'status' => $this->statusName($registration_data['status']),
            ] + $result_evaluation_data;
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

    function getFieldLabel($field_name, $opportunity)
    {
        $app = App::i();
        
        /** @var Opportunity $opportunity */
        $opportunity->registerRegistrationMetadata();

        $field = $app->getRegisteredMetadataByMetakey($field_name, Registration::class);

        return $field->label;
    }

    /**
     * Traduz o resultado da avaliação para uma string localizada.
     *
     * @param string|null $result O resultado da avaliação, que pode ser 'valid', 'invalid' ou null.
     * @return string A string traduzida ou uma string vazia se o resultado for null.
    */
    function translateResult($result): string {
        if(is_null($result)) {
            return '';
        }
        
        $values = [
            'valid' => i::__('Válido'),
            'invalid' => i::__('Inválido')
        ];
    
        return $values[$result] ?? $result;
    }
}
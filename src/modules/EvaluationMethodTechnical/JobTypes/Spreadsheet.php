<?php
namespace EvaluationMethodTechnical\JobTypes;

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
        $opportunity = $job->owner;

        $sections = json_decode(json_encode($opportunity->evaluationMethodConfiguration->sections), true);
        $criteria = json_decode(json_encode($opportunity->evaluationMethodConfiguration->criteria), true);

        $column_prefixes = $this->generateSpreadsheetStructure(1, 300);
        array_splice($column_prefixes, 0, $total_properties);

        $header = [];
        $sub_header = [];
        foreach($sections as &$section) {
            $section['criteria'] = array_filter($criteria, function($cri) use ($section) {
                return $cri['sid'] === $section['id'];
            });
            
            $subtotal = 0;
            foreach($section['criteria'] as $cri) {
                $subtotal += $cri['max'];
                $sub_header[$cri['id']] = "{$cri['title']} (" . i::__('peso') . ": {$cri['weight']})";
            }

            $section['criteria']['subtotal'] = $subtotal;

            $total_criteria = count($section['criteria']);
            $columns = array_splice($column_prefixes, 0, $total_criteria);

            $first_column = reset($columns);
            $last_column = end($columns);
            
            $header["{$first_column}1:{$last_column}1"] = $section['name'];

            $sub_header['subtotal-'.$section['id']] = i::__('Subtotal') . " (". i::__('max') . ": {$subtotal})";
        }

        return ['header' => $header, 'subHeader' => $sub_header, 'columnPrefixes' => $column_prefixes];
    }

    protected function _getEvaluationResultHeader(Job $job, $properties, $column_prefixes) : array {
        $entity_class_name = $job->entityClassName;

        $sub_header = [];
        foreach($properties as $property) {
            if (in_array($property, ['result', 'status', 'evaluationData'])) {
                if($property === 'evaluationData') {
                    $sub_header['obs'] = i::__('Parecer Técnico');
                    $sub_header['viability'] = i::__('Esta proposta apresenta exequibilidade?');
                } else {
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
        $opportunity = $job->owner;
        $sections = json_decode(json_encode($opportunity->evaluationMethodConfiguration->sections), true);
        $criteria = json_decode(json_encode($opportunity->evaluationMethodConfiguration->criteria), true);

        $result = [];
        foreach ($evaluations['evaluations'] as $evaluation) {
            $evaluation_data = $evaluation['evaluation']['evaluationData'] ?? [];
            unset($evaluation_data['obs']);

            $section_data = [];
            foreach($sections as $section) {
                $section['criteria'] = array_filter($criteria, function($cri) use ($section) {
                    return $cri['sid'] === $section['id'];
                });

                $section_data['subtotal-'.$section['id']] = null;
                foreach($section['criteria'] as $cri) {
                    if(isset($evaluation['evaluation']['evaluationData'])) {
                        foreach($evaluation['evaluation']['evaluationData'] as $key => $value) {
                            if($cri['id'] === $key) {
                                $section_data['subtotal-'.$section['id']] += $value;
                            }
                        }
                    }
                }
            }

            $evaluation_data = array_merge($evaluation_data, $section_data);
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
                'obs' => $evaluation['evaluation']['evaluationData']['obs'] ?? null,
                'viability' => $evaluation['evaluation']['evaluationData']['viability'] ?? null
            ] + $evaluation_data;
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
<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use Spreadsheets\SpreadsheetJob;

class EvaluationMethodTechnical extends SpreadsheetJob
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
        
        $header = [
            'A1:B1' => i::__('Informações sobre as inscrições e proponentes'), 
            'C1' => i::__('Informações sobre o avaliador'), 
        ];
        
        $column_prefixes = [
            'D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ','BA','BB','BC','BD','BE','BF','BG','BH','BI','BJ','BK','BL','BM','BN','BO','BP','BQ','BR','BS','BT','BU','BV','BW','BX','BY','BZ',
        ];

        foreach($properties as $property) {
            if (!in_array($property, ['result', 'status', 'evaluationData'])) {
                $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
            } else {
                if($property === 'evaluationData') {
                    $evaluations_fields['obs'] = i::__('Parecer Técnico');
                    $evaluations_fields['viability'] = i::__('Esta proposta apresenta exequibilidade?');
                } else {
                    $evaluations_fields[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
                }
            }
        }

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

            $sub_header['subtotal-'.$section['id']] = "Subtotal (max: {$subtotal})";
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
        $sections = json_decode(json_encode($opportunity->evaluationMethodConfiguration->sections), true);
        $criteria = json_decode(json_encode($opportunity->evaluationMethodConfiguration->criteria), true);

        $query = [];
        $query['@limit'] = $this->limit;
        $query['@page'] = $this->page;
        $opportunity_controller = $app->controller('opportunity');
        $opportunity_controller->data = $opportunity_controller->postData;
        $evaluations = $opportunity_controller->apiFindEvaluations($opportunity->id, $query);

        $result = [];
        foreach ($evaluations->evaluations as $evaluation) {
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
            
            $result[] = [
                'owner.{name}' => $evaluation['registration']['owner']['name'],
                'registration' => $evaluation['registration']['number'],
                'user' => $evaluation['valuer']['name'],
                'result' => $evaluation['evaluation']['resultString'] ?? null,
                'status' => $evaluation['registration']['status'],
                'obs' => $evaluation['evaluation']['evaluationData']['obs'] ?? null,
                'viability' => $evaluation['evaluation']['evaluationData']['viability'] ?? null
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
}
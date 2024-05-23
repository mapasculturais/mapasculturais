<?php
namespace Spreadsheets\JobTypes;

use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\i;
use Spreadsheets\SpreadsheetJob;

class EvaluationMethodSimple extends SpreadsheetJob
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
        
        $query = $job->query;
        $properties = explode(',', $query['@select']);

        foreach($properties as $property) {
            $sub_header[$property] = $entity_class_name::getPropertyLabel($property) ?: $property;
        }

        $header = [
            'A1:B1' => i::__('Informações sobre as inscrições e proponentes'), 
            'C1' => i::__('Informações sobre o avaliador'), 
            'D1:F1' => i::__('Avaliação')
        ];

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
            $result[] = [
                'owner.{name}' => $evaluation['registration']['owner']['name'],
                'registration' => $evaluation['registration']['number'],
                'user' => $evaluation['valuer']['name'],
                'result' => $evaluation['evaluation']['resultString'],
                'status' => $evaluation['registration']['status'],
                'evaluationData' => $evaluation['evaluation']['evaluationData']['obs']
            ];
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
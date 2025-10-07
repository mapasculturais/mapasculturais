<?php
namespace OpportunityExporter;

use MapasCulturais\Module as MapasCulturaisModule;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\i;

class Module extends MapasCulturaisModule {
    public function _init() { 
        $app = App::i();

        $app->hook('POST(opportunity.export)', function () use($app) {
            /** @var OpportunityController $this */
            $this->requireAuthentication();

            /** @var Opportunity */
            $opportunity = $this->requestedEntity;

            if(!$opportunity) {
                $app->pass();
            }

            $opportunity->checkPermission('@control');

            $data = $this->data;

            $infos = $data['infos'] ?? false;
            $files = $data['files'] ?? false;
            $images = $data['images'] ?? false;
            $dates = $data['dates'] ?? false;
            $vacancy_limits = $data['vacancyLimits'] ?? false;
            $categories = $data['categories'] ?? false;
            $ranges = $data['ranges'] ?? false;
            $proponent_types = $data['proponentTypes'] ?? false;
            $workplan = $data['workplan'] ?? false;
            $status_labels = $data['statusLabels'] ?? false;
            $phase_seals = $data['phaseSeals'] ?? false;
            $appeal_phases = $data['appealPhases'] ?? false;
            $monitoring_phases = $data['monitoringPhases'] ?? false;

            $exporter = new Exporter(
                $opportunity,
                infos: $infos,
                files: $files,
                images: $images,
                dates: $dates,
                vacancyLimits: $vacancy_limits,
                categories: $categories,
                ranges: $ranges,
                proponentTypes: $proponent_types,
                workplan: $workplan,
                statusLabels: $status_labels,
                phaseSeals: $phase_seals,
                appealPhases: $appeal_phases,
                monitoringPhases: $monitoring_phases,
            );

            $file_content = $exporter->export();
            $filename = str_replace('{id}', $opportunity->id, i::__('oportunidade-exportada-{id}.export'));

            $headers = [
                'Content-Type' => 'application/json',
                'Content-disposition' => "attachment; filename={$filename}",
                'Content-Length' => strlen($file_content), 
            ];

            foreach($headers as $header_name => $value) {
                $app->response = $app->response->withHeader($header_name, $value);
            }
            
            $app->halt(200, $file_content);
        });
    }

    public function register() { }

}
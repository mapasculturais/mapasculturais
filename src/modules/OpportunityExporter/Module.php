<?php
namespace OpportunityExporter;

use MapasCulturais\Module as MapasCulturaisModule;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities;
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
            $workplan = $data['workplan'] ?? false;
            $status_labels = $data['statusLabels'] ?? false;
            $appeal_phases = $data['appealPhases'] ?? false;
            $monitoring_phases = $data['monitoringPhases'] ?? false;

            $exporter = new Exporter(
                $opportunity,
                infos: $infos,
                files: $files,
                images: $images,
                dates: $dates,
                vacancyLimits: $vacancy_limits,
                workplan: $workplan,
                statusLabels: $status_labels,
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

        $app->hook('POST(opportunity.import)', function () use ($app) {
            /** @var OpportunityController $this */
            $this->requireAuthentication();

            $data = $this->data['opportunity'] ?? null;

            if(!$data) {
                $this->errorJson(['opportunity' => [i::__('Os dados para importação não foram enviados')]], 400);
            }

            $filters = $this->data['filters'] ?? null;

            if(!$filters) {
                $this->errorJson(['opportunity' => [i::__('As definição do que deve ser importado não foi enviada')]], 400);
            }
            
            $owner_entity = $this->data['opportunity']['ownerEntity'] ?? null;

            if (!$owner_entity) {
                $this->errorJson(['ownerEntity' => [i::__('A entidade vinculada é obrigatória')]], 400);
            }

            $opportunity_classes = [
                'agent' => Entities\AgentOpportunity::class,
                'space' => Entities\SpaceOpportunity::class,
                'event' => Entities\EventOpportunity::class,
                'project' => Entities\ProjectOpportunity::class,
            ];

            $opportunity_class = $opportunity_classes[$owner_entity['__objectType']] ?? null;

            if (!$opportunity_class) {
                $this->errorJson(['ownerEntity' => [i::__('Tipo inválido para a entidade vinculada')]], 400);
            }

            $owner_entity = $app->repo($owner_entity['__objectType'])->find($owner_entity['_id']);

            if (!$owner_entity) {
                $this->errorJson(['ownerEntity' => [i::__('Entidade vinculada não encontrada')]], 400);
            }

            $importer = new Importer(
                onwerEntity: $owner_entity, 
                data: $data,
                files: $filters['files'] ?? false,
                images: $filters['images'] ?? false,

                dates: $filters['dates'] ?? false,

                vacancyLimits: $filters['vacancyLimits'] ?? false,

                statusLabels: $filters['statusLabels'] ?? false,
                appealPhases: $filters['appealPhases'] ?? false,
                monitoringPhases: $filters['monitoringPhases'] ?? false,
            );

            $app->conn->beginTransaction();
            try {
                $opportunity = $importer->import();                
            } catch(\Throwable $e) {
                $app->conn->rollBack();
                throw $e;
            }
            
            $app->conn->commit();
            
            $this->json($opportunity);
        });
    }

    public function register() { }

    public static function getInfoProperties(): array
    {
        $app = App::i();

        $properties = [
            'name',
            'type',
            'shortDescription',
            'longDescription',
            'terms',

            'site',

            'facebook',
            'twitter',
            'instagram',
            'linkedin',
            'vimeo',
            'spotify',
            'youtube',
            'pinterest',
            'tiktok',

        ];

        $app->applyHook('opportunityExport.infoProperties', [&$properties]);

        return $properties;
    }

    public static function getWorkplanProperties(): array
    {
        $app = App::i();

        $properties = [
            'workplanLabelDefault',
            'goalLabelDefault',
            'deliveryLabelDefault',
            'workplan_dataProjectlimitMaximumDurationOfProjects',
            'workplan_dataProjectmaximumDurationInMonths',
            'workplan_metaInformTheStageOfCulturalMaking',
            'workplan_metaLimitNumberOfGoals',
            'workplan_metaMaximumNumberOfGoals',
            'workplan_deliveryReportTheDeliveriesLinkedToTheGoals',
            'workplan_deliveryLimitNumberOfDeliveries',
            'workplan_deliveryMaximumNumberOfDeliveries',
            'workplan_registrationReportTheNumberOfParticipants',
            'workplan_registrationInformCulturalArtisticSegment',
            'workplan_registrationReportExpectedRenevue',
            'workplan_monitoringInformTheFormOfAvailability',
            'workplan_monitoringInformAccessibilityMeasures',
            'workplan_monitoringInformThePriorityAudience',
            'workplan_monitoringProvideTheProfileOfParticipants',
            'workplan_monitoringReportExecutedRevenue',
        ];

        $app->applyHook('opportunityExport.workplanProperties', [&$properties]);

        return $properties;
    }
}
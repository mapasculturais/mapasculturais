<?php

namespace OpportunityExporter;

use Exception;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\i;
use MapasCulturais\Traits\Singleton;
use MapasCulturais\App;

class Exporter
{
    /**
     * @param Opportunity $opportunity Oportunidade a ser exportada
     * @param bool $infos Exportar informações básicas
     * @param bool $files Exportar anexos
     * @param bool $images Exportar imagens
     * @param bool $dates Exportar datas das fases do edital
     * @param bool $vacancyLimits Exportar limites de vagas
     * @param bool $categories Exportar categorias
     * @param bool $ranges Exportar faixas/linhas
     * @param bool $proponentTypes Exportar tipos de proponente
     * @param bool $workplan Exportar plano de metas
     * @param bool $statusLabels Exportar configurações de status
     * @param bool $phaseSeals Exportar configuração dos selos certificadores das fases
     * @param bool $appealPhases Exportar fases de recurso
     * @param bool $monitoringPhases Exportar fases de monitoramento
     */
    function __construct(
        protected Opportunity $opportunity,
        protected bool $infos = true,
        protected bool $files = true,
        protected bool $images = true,

        protected bool $dates = true,

        protected bool $vacancyLimits = true,

        protected bool $categories = true,
        protected bool $ranges = true,
        protected bool $proponentTypes = true,

        protected bool $workplan = true,

        protected bool $statusLabels = true,
        protected bool $phaseSeals = true,
        protected bool $appealPhases = true,
        protected bool $monitoringPhases = true,
    ) {
        if(!$opportunity->isFirstPhase) {
            throw new Exception('O parâmetro opportunity deve ser a primeira fase de uma oportunidade');
        }
    }

    public function export(): string
    {
        $app = App::i();

        $result = [];

        if ($this->infos) {
            $result['infos'] = $this->exportInfo();
        }

        if ($this->vacancyLimits) {
            $result['vacancyLimits'] = $this->exportVacancyLimits();
        }

        if ($this->categories) {
            $result['categories'] = $this->exportCategories();
        }

        if ($this->ranges) {
            $result['ranges'] = $this->exportRanges();
        }

        if ($this->proponentTypes) {
            $result['proponentTypes'] = $this->exportProponentTypes();
        }

        if ($this->workplan) {
            $result['workplan'] = $this->exportWorkplan();
        }

        if ($this->images) {
            $result['images'] = [
                'header' => $this->exportFileGroup('header'),
                'avatar' => $this->exportFileGroup('avatar'),
                'gallery' => $this->exportFileGroup('gallery'),
            ];
        }

        if ($this->files) {
            $registered_file_groups = $app->getRegisteredFileGroupsByEntity(Opportunity::class);
            $result['files'] = [];
            foreach($registered_file_groups as $file_group) {
                if(in_array($file_group->name, ['header', 'avatar', 'gallery'])) {
                    continue;
                }

                $result['files'][$file_group->name] = $this->exportFileGroup($file_group->name);
            }
        }

        $result['phases'] = [];

        foreach($this->opportunity->allPhases as $phase) {
            $result['phases'][] = $this->exportPhase($phase);
        }

        return json_encode($result);
    }

    public function exportInfo(): array
    {
        $result = [];

        $result['properties'] = $this->exportInfoProperties();
        $result['metalists'] = $this->exportInfoMetalists();

        return $result;
    }

    public function exportInfoProperties(): array
    {
        $result = [];

        $properties = [
            'name',
            'shortDescription',
            'longDescription',
            'terms'
        ];

        foreach ($properties as $prop) {
            $result[$prop] = $this->opportunity->$prop;
        }

        return $result;
    }

    public function exportInfoMetalists(): array
    {
        $result = [];
        foreach ($this->opportunity->metaLists as $group => $metalists) {
            $result[$group] = [];

            foreach ($metalists as $metalist) {
                $result[$group][] = $metalist->simplify('title,value,description');
            }
        }
        return $result;
    }

    public function exportVacancyLimits(): array
    {
        $result = [
            'registrationLimit' => $this->opportunity->registrationLimit,
            'registrationLimitPerOwner' => $this->opportunity->registrationLimitPerOwner,
            'totalResource' => $this->opportunity->totalResource,
            'vacancies' => $this->opportunity->vacancies,
        ];

        return $result;
    }

    public function exportCategories(): array
    {
        return $this->opportunity->registrationCategories ?: [];
    }

    public function exportRanges(): array
    {
        return $this->opportunity->registrationRanges ?: [];
    }

    public function exportProponentTypes(): array
    {
        $result = [
            'enabledProponentTypes' => $this->opportunity->registrationProponentTypes ?: [],
            'useAgentRelationColetivo' => $this->opportunity->useAgentRelationColetivo,
            'proponentAgentRelation' => $this->opportunity->proponentAgentRelation,
        ];

        return $result;
    }

    public function exportWorkplan(): array
    {
        $result = [
            'enableWorkplan' => $this->opportunity->enableWorkplan,
            'workplanLabelDefault' => $this->opportunity->workplanLabelDefault,
            'goalLabelDefault' => $this->opportunity->goalLabelDefault,
            'deliveryLabelDefault' => $this->opportunity->deliveryLabelDefault,
            'workplan_dataProjectlimitMaximumDurationOfProjects' => $this->opportunity->workplan_dataProjectlimitMaximumDurationOfProjects,
            'workplan_dataProjectmaximumDurationInMonths' => $this->opportunity->workplan_dataProjectmaximumDurationInMonths,
            'workplan_metaInformTheStageOfCulturalMaking' => $this->opportunity->workplan_metaInformTheStageOfCulturalMaking,
            'workplan_metaLimitNumberOfGoals' => $this->opportunity->workplan_metaLimitNumberOfGoals,
            'workplan_metaMaximumNumberOfGoals' => $this->opportunity->workplan_metaMaximumNumberOfGoals,
            'workplan_deliveryReportTheDeliveriesLinkedToTheGoals' => $this->opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals,
            'workplan_deliveryLimitNumberOfDeliveries' => $this->opportunity->workplan_deliveryLimitNumberOfDeliveries,
            'workplan_deliveryMaximumNumberOfDeliveries' => $this->opportunity->workplan_deliveryMaximumNumberOfDeliveries,
            'workplan_registrationReportTheNumberOfParticipants' => $this->opportunity->workplan_registrationReportTheNumberOfParticipants,
            'workplan_registrationInformCulturalArtisticSegment' => $this->opportunity->workplan_registrationInformCulturalArtisticSegment,
            'workplan_registrationReportExpectedRenevue' => $this->opportunity->workplan_registrationReportExpectedRenevue,
            'workplan_monitoringInformTheFormOfAvailability' => $this->opportunity->workplan_monitoringInformTheFormOfAvailability,
            'workplan_monitoringInformAccessibilityMeasures' => $this->opportunity->workplan_monitoringInformAccessibilityMeasures,
            'workplan_monitoringInformThePriorityAudience' => $this->opportunity->workplan_monitoringInformThePriorityAudience,
            'workplan_monitoringProvideTheProfileOfParticipants' => $this->opportunity->workplan_monitoringProvideTheProfileOfParticipants,
            'workplan_monitoringReportExecutedRevenue' => $this->opportunity->workplan_monitoringReportExecutedRevenue,
        ];

        return $result;
    }

    public function exportFile(File $file): array {
        $result = [
            'name' => $file->name,
            'description' => $file->description,
            'mimeType' => $file->mimeType,
            'md5' => $file->md5,
            'content' => base64_encode(file_get_contents($file->path))
        ];

        return $result;
    }

    public function exportFileGroup(string $group_name): array
    {
        $result = [];

        if($group_files = $this->opportunity->files[$group_name] ?? false) {
            $group_files = is_array($group_files) ? $group_files : [$group_files];

            foreach($group_files as $file) {
                $result[] = $this->exportFile($file);
            }
        }
        
        return $result;
    }


    // por fase

    public function exportPhase(Opportunity $phase): array
    {
        
    }

    public function exportStatusLabels(Opportunity $phase): array
    {
        $result = [];

        return $result;
    }
}

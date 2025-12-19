<?php

namespace OpportunityExporter;

use Exception;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\App;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entity;

class Exporter
{
    /**
     * Leia o README.md do módulo
     */
    const VERSION = '1.0.0';

    /**
     * @param Opportunity $opportunity Oportunidade a ser exportada
     * @param bool $infos Exportar informações básicas
     * @param bool $files Exportar anexos
     * @param bool $images Exportar imagens
     * @param bool $dates Exportar datas das fases do edital
     * @param bool $vacancyLimits Exportar limites de vagas
     * @param bool $workplan Exportar plano de metas
     * @param bool $statusLabels Exportar configurações de status
     * @param bool $appealPhases Exportar fases de recurso
     * @param bool $monitoringPhases Exportar fases de monitoramento
     */
    function __construct(
        protected Opportunity $opportunity,
        protected bool $infos = false,
        protected bool $files = false,
        protected bool $images = false,

        protected bool $dates = false, // exportado a cada fase

        protected bool $vacancyLimits = false,

        protected bool $workplan = false,

        protected bool $statusLabels = false, // exportado a cada fase
        protected bool $appealPhases = false, // exportado a cada fase
        protected bool $monitoringPhases = false,
    ) {
        if (!$opportunity->isFirstPhase) {
            throw new Exception('O parâmetro opportunity deve ser a primeira fase de uma oportunidade');
        }
    }

    public function export(): string
    {
        $app = App::i();

        $result = [
            'exporterVersion' => self::VERSION,
            'entityType' => 'opportunity',
            'exported' => [
                'infos' => $this->infos,
                'files' => $this->files,
                'images' => $this->images,
                'dates' => $this->dates,
                'vacancyLimits' => $this->vacancyLimits,
                'workplan' => $this->workplan,
                'statusLabels' => $this->statusLabels,
                'appealPhases' => $this->appealPhases,
                'monitoringPhases' => $this->monitoringPhases,
            ]
        ];

        if ($this->infos) {
            $result['infos'] = $this->exportInfo();
        }

        if ($this->files) {
            $registered_file_groups = $app->getRegisteredFileGroupsByEntity(Opportunity::class);
            $result['files'] = [];
            foreach ($registered_file_groups as $file_group) {
                if (in_array($file_group->name, ['header', 'avatar', 'gallery'])) {
                    continue;
                }

                $result['files'][$file_group->name] = $this->exportFileGroup($file_group->name);
            }
        }

        if ($this->images) {
            $result['images'] = [
                'header' => $this->exportFileGroup('header'),
                'avatar' => $this->exportFileGroup('avatar'),
                'gallery' => $this->exportFileGroup('gallery'),
            ];
        }

        if ($this->vacancyLimits) {
            $result['vacancyLimits'] = $this->exportVacancyLimits();
        }

        $result['categories'] = $this->exportCategories();
        $result['ranges'] = $this->exportRanges();
        $result['proponentTypes'] = $this->exportProponentTypes();

        if ($this->workplan) {
            $result['workplan'] = $this->exportWorkplan();
        }

        $result['phases'] = [];

        foreach ($this->opportunity->allPhases as $phase) {
            if ($phase->isReportingPhase) {
                if (!$this->monitoringPhases) {
                    continue;
                }
                $result['phases'][] = $this->exportMonitoringPhase($phase);
            } else {
                $result['phases'][] = $this->exportPhase($phase);
            }
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

        $properties = Module::getInfoProperties();

        foreach ($properties as $prop) {
            $result[$prop] = $this->opportunity->$prop;
        }

        $result['type'] = $result['type']->id;

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
            'registrationProponentTypes' => $this->opportunity->registrationProponentTypes ?: [],
            'useAgentRelationColetivo' => $this->opportunity->useAgentRelationColetivo,
            'proponentAgentRelation' => $this->opportunity->proponentAgentRelation,
        ];

        return $result;
    }

    public function exportWorkplan(): array
    {
        if(!$this->opportunity->enableWorkplan) {
            return ['enableWorkplan' => false];
        }

        $result = ['enableWorkplan' => true];

        foreach(Module::getInfoProperties() as $prop) {
            $result[$prop] = $this->opportunity->$prop;
        }

        return $result;
    }

    public function exportFile(File $file): array
    {
        if (file_exists($file->path)) {
            $file_content = file_get_contents($file->path);
        } else {
            $file_content = '';
        }

        $result = [
            'name' => $file->name,
            'description' => $file->description,
            'mimeType' => $file->mimeType,
            'md5' => $file->md5,
            'content' => base64_encode($file_content)
        ];

        return $result;
    }

    public function exportFileGroup(string $group_name, ?Entity $owner = null): array
    {
        $result = [];

        if(is_null($owner)) {
            $owner = $this->opportunity;
        }

        if ($group_files = $owner->files[$group_name] ?? false) {
            $group_files = is_array($group_files) ? $group_files : [$group_files];

            foreach ($group_files as $file) {
                $result[] = $this->exportFile($file);
            }
        }

        return $result;
    }

    // por fase

    public function exportPhase(Opportunity $phase, array $include_metadata = []): array
    {
        $export_phase_props = [
            'isDataCollection',
            ...$include_metadata,
        ];

        if (!$phase->isFirstPhase) {
            // o nome da primeira fase é exportado nas infos
            $export_phase_props = [
                ...$export_phase_props,
                'name'
            ];
        }

        if ($this->statusLabels) {
            $export_phase_props = [
                ...$export_phase_props,
                'statusLabels'
            ];
        }

        if ($this->dates) {
            $export_phase_props = [
                ...$export_phase_props,
                'registrationFrom',
                'registrationTo',
                'publishTimestamp',
                'autoPublish',
            ];
        }

        $result = [];

        if ($phase->isFirstPhase) {
            $result['isFirstPhase'] = true;
        }

        if ($phase->isLastPhase) {
            $result['isLastPhase'] = true;
        }

        foreach ($export_phase_props as $prop) {
            $value = $phase->$prop;
            if($value instanceof \DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $result[$prop] = $value;
        }

        if ($phase->isDataCollection) {
            $result['form'] = $this->exportForm($phase);
        }

        if ($evaluation_phase = $phase->evaluationMethodConfiguration) {
            $result['evaluationPhase'] = $this->exportEvaluationPhase($evaluation_phase);
        }

        if ($this->appealPhases) {
            $result['appealPhase'] = $this->exportAppealPhase($phase);
        }

        return $result;
    }

    public function exportForm(Opportunity $phase): array
    {
        $result = [
            'steps' => $this->exportFormSteps($phase),
            'fields' => $this->exportFormFields($phase),
            'attachments' => $this->exportFormAttachments($phase)
        ];

        return $result;
    }

    public function exportFormSteps(Opportunity $phase): array
    {
        $result = [];

        foreach ($phase->registrationSteps as $step) {
            $step_id = "STEP(" . base_convert($step->id, 10, 36) . ")";

            $result[$step_id] = [
                'name' => $step->name,
                'displayOrder' => $step->displayOrder,
                'metadata' => $step->metadata
            ];
        }

        return $result;
    }

    public function exportFormFields(Opportunity $phase): array
    {
        $result = [];

        foreach ($phase->registrationFieldConfigurations as $field) {
            $field_id = "FIELD(" . base_convert($field->id, 10, 36) . ")";

            $config = is_array($field->config) ? array_filter($field->config) : $field->config;

            $field_result = [
                'step' => "STEP(" . base_convert($field->step->id, 10, 36) . ")",

                'title' => $field->title,
                'description' => $field->description,
                'maxSize' => $field->maxSize,
                'required' => $field->required,
                'fieldType' => $field->fieldType,
                'displayOrder' => $field->displayOrder,
                'fieldOptions' => $field->fieldOptions,
                'config' => $config,
                'categories' => $field->categories,
                'registrationRanges' => $field->registrationRanges,
                'proponentTypes' => $field->proponentTypes,

                'conditional' => false,
            ];

            if ($field->conditional && $field->conditionalField && preg_match('#field_(\d+)#', $field->conditionalField, $matches)) {
                $conditional_field = "FIELD(" . base_convert($matches[1], 10, 36) . ")";
                $field_result = [
                    ...$field_result,
                    'conditional' => true,
                    'conditionalField' => "$conditional_field",
                    'conditionalValue' => $field->conditionalValue,
                ];
            }

            $result[$field_id] = $field_result;
        }

        return $result;
    }

    public function exportFormAttachments(Opportunity $phase): array
    {
        $result = [];

        foreach ($phase->registrationFileConfigurations as $rfc) {
            $rfc_id = "FILE(" . base_convert($rfc->id, 10, 36) . ")";

            $rfc_result = [
                'step' => "STEP(" . base_convert($rfc->step->id, 10, 36) . ")",

                'title' => $rfc->title,
                'description' => $rfc->description,
                'required' => $rfc->required,
                'displayOrder' => $rfc->displayOrder,
                'categories' => $rfc->categories,
                'registrationRanges' => $rfc->registrationRanges,
                'proponentTypes' => $rfc->proponentTypes,

                'conditional' => false,
            ];

            if ($rfc->conditional && $rfc->conditionalField && preg_match('#field_(\d+)#', $rfc->conditionalField, $matches)) {
                $conditional_field = "FIELD(" . base_convert($matches[1], 10, 36) . ")";
                $rfc_result = [
                    ...$rfc_result,
                    'conditional' => true,
                    'conditionalField' => "$conditional_field",
                    'conditionalValue' => $rfc->conditionalValue,
                ];
            }

            if ($template = $rfc->files['registrationFileTemplate'] ?? false) {
                $rfc_result['template'] = $this->exportFile($template);
            }

            $result[$rfc_id] = $rfc_result;
        }

        return $result;
    }

    public function exportEvaluationPhase(EvaluationMethodConfiguration $phase): array
    {
        $result = $phase->evaluationMethod->export($phase);

        $result = [
            'name' => $phase->name,
            'type' => $phase->type->id,
            'evaluationFrom' => $phase->evaluationFrom ? $phase->evaluationFrom->format('Y-m-d H:i:s') : null,
            'evaluationTo' => $phase->evaluationTo ? $phase->evaluationTo->format('Y-m-d H:i:s') : null,

            ...$result,

            'infos' => $phase->infos,
            'publishEvaluationDetails' => $phase->publishEvaluationDetails,
            'publishValuerNames' => $phase->publishValuerNames,
            'autoApplicationAllowed' => $phase->autoApplicationAllowed,

            'avaliableEvaluationFields' => $phase->opportunity->avaliableEvaluationFields,
        ];

        $result_json = json_encode($result);

        if (preg_match_all('#field_(\d+)#', $result_json, $matches)) {
            foreach ($matches[0] as $i => $field_name) {
                $fid = "FIELD(" . base_convert($matches[1][$i], 10, 36) . ")";
                $result_json = str_replace($field_name, ":$fid", $result_json);
            }
        }

        if (preg_match_all('#"field":"?(\d+)"?#', $result_json, $matches)) {
            foreach ($matches[0] as $i => $field_name) {
                $fid = "FIELD(" . base_convert($matches[1][$i], 10, 36) . ")";
                $result_json = str_replace($field_name, "\"field\":\"@$fid\"", $result_json);
            }
        }

        if (preg_match_all('#rfc_(\d+)#', $result_json, $matches)) {
            foreach ($matches[0] as $i => $file_group) {
                $fid = "FILE(" . base_convert($matches[1][$i], 10, 36) . ")";
                $result_json = str_replace($file_group, "%$fid", $result_json);
            }
        }

        return (array) json_decode($result_json);
    }

    public function exportAppealPhase(Opportunity $phase): ?array
    {
        $appeal_phase = $phase->appealPhase;

        if (!$appeal_phase) {
            return null;
        }

        $result = $this->exportPhase($appeal_phase, ['isAppealPhase']);

        return $result;
    }

    public function exportMonitoringPhase(Opportunity $phase): ?array
    {
        $result = [];

        if (!$phase->isReportingPhase) {
            return null;
        }

        $result = $this->exportPhase($phase, ['isReportingPhase', 'isFinalReportingPhase', 'includesWorkPlan']);

        return $result;
    }
}

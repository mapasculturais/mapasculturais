<?php

namespace OpportunityExporter;

use Exception;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Event;
use MapasCulturais\Entities\MetaList;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\RegistrationFieldConfiguration;
use MapasCulturais\Entities\RegistrationFileConfiguration;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entity;

class Importer
{
    /**
     * Leia o README.md do módulo
     */
    const VERSION = '1.0.0';

    protected Opportunity $opportunity;

    /**
     * Mapeamento dos steps criados
     * @var RegistrationStep[]
     */
    protected array $stepsMap = [];

    /**
     * Mapeamento dos fields criados
     * @var RegistrationFieldConfiguration[]
     */
    protected array $fieldsMap = [];

    /**
     * Mapeamento dos anexos criados
     * @var RegistrationFileConfiguration[]
     */
    protected array $attachmentsMap = [];

    public function __construct(
        protected Agent|Space|Event|Project $onwerEntity,
        protected array $data,

        protected bool $files = false,
        protected bool $images = false,

        protected bool $dates = false, // exportado a cada fase

        protected bool $vacancyLimits = false,

        protected bool $workplan = false,

        protected bool $statusLabels = false, // exportado a cada fase
        protected bool $appealPhases = false, // exportado a cada fase
        protected bool $monitoringPhases = false,
    ) {
        $opportunity_class = $this->onwerEntity->opportunityClassName;

        $opportunity = new $opportunity_class;
        $opportunity->ownerEntity = $this->onwerEntity;

        $this->opportunity = $opportunity;
    }

    public function import(): Opportunity
    {
        $data = $this->data;
        $this->importInfos($data['infos']);

        if($this->files) {
            foreach(($data['files'] ?? []) as $group => $files_data) {
                $this->importFileGroup($group, $files_data);
            }
        }
        
        if($this->images) {
            foreach(($data['images'] ?? []) as $group => $files_data) {
                $this->importFileGroup($group, $files_data);
            }
        }

        if ($this->vacancyLimits && isset($data['vacancyLimits'])) {
            $this->importVacancyLimits($data['vacancyLimits']);
        }

        $this->importCategories($data['categories']);
        $this->importRanges($data['ranges']);
        $this->importProponentTypes($data['proponentTypes']);

        if ($this->workplan && isset($data['workplan'])) {
            $this->importWorkplan($data['workplan']);
        }

        foreach ($data['phases'] as $phase_data) {
            if ($phase_data['isReportingPhase'] ?? false) {
                $this->importMonitoringPhase($phase_data);
            } else {
                $this->importPhase($phase_data);
            }
        }

        return $this->opportunity;
    }

    public function importInfos(array $data)
    {
        $this->importInfoProperties($data['properties']);

        $this->importInfoProperties($data['metalists']);
    }

    public function importInfoProperties(array $data)
    {
        $properties = Module::getInfoProperties();

        foreach ($properties as $prop) {
            if (array_key_exists($prop, $data)) {
                $this->opportunity->$prop = $data[$prop];
            }
        }

        $this->opportunity->save(true);
    }

    public function exportInfoMetalists(array $data)
    {
        foreach ($data as $group => $metalists) {
            foreach ($metalists as $val) {
                $metalist = new MetaList;
                $metalist->owner = $this->opportunity;
                $metalist->group = $group;
                $metalist->title = $val['title'];
                $metalist->value = $val['value'];
                $metalist->description = $val['description'];
                $metalist->save(true);
            }
        }
    }

    public function importVacancyLimits(array $data)
    {
        $this->opportunity->registrationLimit = $data['registrationLimit'];
        $this->opportunity->registrationLimitPerOwner = $data['registrationLimitPerOwner'];
        $this->opportunity->totalResource = $data['totalResource'];
        $this->opportunity->vacancies = $data['vacancies'];
        $this->opportunity->save(true);
    }

    public function importCategories(array $data)
    {
        $this->opportunity->registrationCategories = $data ?? [];
        $this->opportunity->save(true);
    }

    public function importRanges(array $data)
    {
        $this->opportunity->registrationRanges = $data ?? [];
        $this->opportunity->save(true);
    }

    public function importProponentTypes(array $data)
    {
        $this->opportunity->registrationProponentTypes = $data['registrationProponentTypes'] ?? [];

        if (array_key_exists('useAgentRelationColetivo', $data)) {
            $this->opportunity->useAgentRelationColetivo = $data['useAgentRelationColetivo'];
        }

        if (array_key_exists('proponentAgentRelation', $data)) {
            $this->opportunity->proponentAgentRelation = $data['proponentAgentRelation'];
        }

        $this->opportunity->save(true);
    }

    public function importWorkplan(array $data)
    {
        if ($data['enableWorkplan'] ?? false) {
            $this->opportunity->enableWorkplan = true;

            foreach (Module::getInfoProperties() as $prop) {
                if (array_key_exists($prop, $data)) {
                    $this->opportunity->$prop = $data[$prop];
                }
            }

            $this->opportunity->save(true);
        }
    }

    public function importPhase(array $phase_data, array $include_metadata = [], int $status = Opportunity::STATUS_PHASE, ?Opportunity $parent = null): Opportunity
    {
        if ($phase_data['isFirstPhase'] ?? false) {
            $phase = $this->opportunity;
        } else if ($phase_data['isLastPhase'] ?? false) {
            $phase = $this->opportunity->lastPhase;
        } else {
            $opportunity_class = $this->onwerEntity->opportunityClassName;
            $parent = $parent ?: $this->opportunity;

            $phase = new $opportunity_class;
            $phase->parent = $parent;
            $phase->ownerEntity = $this->onwerEntity;
            $phase->status = $status;
            $phase->name = $phase_data['name'] ?? '';
            $phase->registrationCategories = $parent->registrationCategories;
            $phase->registrationRanges = $parent->registrationRanges;
            $phase->registrationProponentTypes = $parent->registrationProponentTypes;
            $phase->save(true);
        }

        /** @var Opportunity $phase */

        $import_phase_props = [
            'isDataCollection',
            ...$include_metadata,
        ];

        if ($this->statusLabels) {
            $import_phase_props = [
                ...$import_phase_props,
                'statusLabels'
            ];
        }

        if ($this->dates) {
            $import_phase_props = [
                ...$import_phase_props,
                'registrationFrom',
                'registrationTo',
                'publishTimestamp',
                'autoPublish',
            ];
        }

        foreach ($import_phase_props as $prop) {
            if (array_key_exists($prop, $phase_data)) {
                $phase->$prop = $phase_data[$prop];
            }
        }

        $phase->save(true);

        if ($phase->isDataCollection && array_key_exists('form', $phase_data)) {
            $this->importForm($phase, $phase_data['form']);
        }

        if (array_key_exists('evaluationPhase', $phase_data)) {
            $this->importEvaluationPhase($phase, $phase_data['evaluationPhase']);
        }

        if ($appeal_phase = $phase_data['appealPhase'] ?? null) {
            $this->importAppealPhase($phase, $appeal_phase);
        }

        return $phase;
    }

    public function importMonitoringPhase(array $phase_data)
    {
        $this->importPhase($phase_data, ['isReportingPhase', 'isFinalReportingPhase', 'includesWorkPlan']);
    }

    public function importEvaluationPhase(Opportunity $phase, array $data): EvaluationMethodConfiguration
    {

        $data_json = json_encode($data);

        foreach($this->fieldsMap as $fid => $field) {
            $data_json = str_replace(":$fid", $field->fieldName, $data_json);
            $data_json = str_replace('"field":"@' . $fid . '"', '"field":"' . $field->id . '"', $data_json);
        }

        foreach($this->attachmentsMap as $fid => $field) {
            $data_json = str_replace("%$fid", $field->fileGroupName, $data_json);
        }
        
        $replaced_data = json_decode($data_json, JSON_OBJECT_AS_ARRAY);

        $evaluation_phase = new EvaluationMethodConfiguration;

        $evaluation_phase->opportunity = $phase;
        $evaluation_phase->name = $replaced_data['name'];
        $evaluation_phase->type = $replaced_data['type'];
        $evaluation_phase->evaluationFrom = $replaced_data['evaluationFrom'];
        $evaluation_phase->evaluationTo = $replaced_data['evaluationTo'];
        $evaluation_phase->save(true);

        $evaluation_phase->evaluationMethod->import($evaluation_phase, $replaced_data);
        $evaluation_phase->save(true);

        return $evaluation_phase;
    }

    public function importAppealPhase(Opportunity $phase, array $data): Opportunity
    {
        $appeal_phase = $this->importPhase(
            phase_data: $data, 
            include_metadata: ['isAppealPhase'],
            status: Opportunity::STATUS_APPEAL_PHASE,
            parent: $phase
        );

        $phase->appealPhase = $appeal_phase;
        $phase->save(true);

        return $appeal_phase;
    }

    public function importForm(Opportunity $phase, array $data)
    {
        $this->importFormSteps($phase, $data['steps'] ?? []);
        $this->importFormFields($phase, $data['fields'] ?? []);
        $this->importFormAttachments($phase, $data['attachments'] ?? []);
    }

    public function importFormSteps(Opportunity $phase, array $data)
    {
        $app = App::i();
        $app->conn->delete('registration_step', ['opportunity_id' => $phase->id]);
        foreach ($data as $id => $step_data) {
            $step = new RegistrationStep;
            $step->opportunity = $phase;
            $step->name = $step_data['name'] ?? '';
            $step->displayOrder = $step_data['displayOrder'] ?? 0;
            $step->metadata = $step_data['metadata'] ?? (object) [];
            $step->save(true);

            $this->stepsMap[$id] = $step;
        }
    }

    public function importFormFields(Opportunity $phase, array $data)
    {
        $next_round = [];
        foreach ($data as $id => $field_data) {
            $conditional = $field_data['conditional'] ?? false;
            $conditional_field = $field_data['conditionalField'] ?? null;

            if ($conditional && $conditional_field && !isset($this->fieldsMap[$conditional_field])) {
                $next_round[$id] = $field_data;
                continue;
            }

            $field = new RegistrationFieldConfiguration;
            $field->owner = $phase;
            $field->step = $this->stepsMap[$field_data['step']];

            $field->title = $field_data['title'];
            $field->description = $field_data['description'];
            $field->maxSize = $field_data['maxSize'];
            $field->required = $field_data['required'];
            $field->fieldType = $field_data['fieldType'];
            $field->displayOrder = $field_data['displayOrder'];
            $field->fieldOptions = $field_data['fieldOptions'];
            $field->config = $field_data['config'];
            $field->categories = $field_data['categories'];
            $field->registrationRanges = $field_data['registrationRanges'];
            $field->proponentTypes = $field_data['proponentTypes'];
            $field->conditional = $conditional;

            if ($field->conditional && $conditional_field) {
                $field->conditionalField = $this->fieldsMap[$conditional_field]->fieldName;
                $field->conditionalValue = $field_data['conditionalValue'];
            }

            $field->save(true);

            $this->fieldsMap[$id] = $field;
        }

        if ($next_round) {
            $this->importFormFields($phase, $next_round);
        }
    }

    public function importFormAttachments(Opportunity $phase, array $data)
    {
        foreach ($data as $id => $rfc_data) {
            $conditional = $rfc_data['conditional'] ?? false;
            $conditional_field = $rfc_data['conditionalField'] ?? null;

            $rfc = new RegistrationFileConfiguration;
            $rfc->owner = $phase;
            $rfc->step = $this->stepsMap[$rfc_data['step']];

            $rfc->title = $rfc_data['title'];
            $rfc->description = $rfc_data['description'];
            $rfc->required = $rfc_data['required'];
            $rfc->displayOrder = $rfc_data['displayOrder'];
            $rfc->categories = $rfc_data['categories'];
            $rfc->registrationRanges = $rfc_data['registrationRanges'];
            $rfc->proponentTypes = $rfc_data['proponentTypes'];
            $rfc->conditional = $conditional;

            if ($rfc->conditional && $conditional_field) {
                $rfc->conditionalField = $this->fieldsMap[$conditional_field]->fieldName;
                $rfc->conditionalValue = $rfc_data['conditionalValue'];
            }

            $rfc->save(true);

            $this->attachmentsMap[$id] = $rfc;


            if ($template_file_data = $rfc_data['template'] ?? null) {
                $this->importFile($rfc, 'registrationFileTemplate', $template_file_data);
            }
        }
    }

    public function importFileGroup(string $group, array $files_data, ?Entity $owner = null)
    {
        if(is_null($owner)) {
            $owner = $this->opportunity;
        }

        foreach($files_data as $file_data) {
            $this->importFile($owner, $group, $file_data);
        }
    }

    public function importFile(Opportunity|EvaluationMethodConfiguration|RegistrationFileConfiguration $owner, string $group, array $file_data)
    {
        $tmp_filename = sys_get_temp_dir() . '/' . uniqid('importer-');
        file_put_contents($tmp_filename, base64_decode($file_data['content']));

        $file_class = $owner->fileClassName;

        /** @var File */
        $file = new $file_class([
            'name' => $file_data['name'],
            'tmp_name' => $tmp_filename,
            'error' => UPLOAD_ERR_OK
        ]);

        $file->owner = $owner;
        $file->name = $file_data['name'];
        $file->description = $file_data['description'];
        $file->group = $group;

        $file->save(true);
    }
}

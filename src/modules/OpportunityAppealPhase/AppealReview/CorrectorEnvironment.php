<?php

namespace OpportunityAppealPhase\AppealReview;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;
use Opportunities\Module as OpportunitiesModule;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use OpportunityAppealPhase\Module;
use OpportunityPhases\Module as OpportunityPhases;

/**
 * Monta o payload read-only do ambiente do corretor (PR4.5 / issue #14).
 */
class CorrectorEnvironment
{
    private const STATUS_LABELS = [
        RegistrationAppealReview::STATUS_DESIGNATED => 'designated',
        RegistrationAppealReview::STATUS_DRAFT => 'draft',
        RegistrationAppealReview::STATUS_SENT => 'sent',
        RegistrationAppealReview::STATUS_REOPENED => 'reopened',
    ];

    /**
     * @return array<string, mixed>
     */
    public function build(RegistrationAppealReview $review): array
    {
        $app = App::i();
        $this->assertFeatureEnabled();
        $this->assertCanAccess($review, $app->user);

        $slot = $review->originalEvaluation;
        $scope_ids = $this->getReleasedCriteriaIds($review);

        // Após envio o slot já contém a nota corrigida; a original fica no snapshot da designação.
        $original_data = $review->originalValue ?? $slot->getEvaluationData();
        $original = $this->filterEvaluationData((array) $original_data, $scope_ids);
        $draft = $review->correctedValue
            ? $this->filterEvaluationData((array) $review->correctedValue, $scope_ids)
            : null;

        $main_emc = $slot->registration->opportunity->evaluationMethodConfiguration;
        $method_id = $main_emc ? (string) $main_emc->type->id : '';

        $registration = $review->registration;

        $payload = [
            'assignmentId' => (int) $review->id,
            'evaluationId' => (int) $slot->id,
            'registrationId' => (int) $registration->id,
            'registrationNumber' => $registration->number,
            'opportunityName' => (string) $registration->opportunity->name,
            // Formulário da inscrição filtrado pelos campos liberados na designação
            // (mesmo espírito do "Abrir lista de campos" do avaliador).
            'visibleForm' => $this->buildVisibleForm($review, $registration),
            // F7 (#51): método da fase principal — define como o frontend
            // renderiza o formulário (critérios técnicos, campos documentais
            // ou resultado global do simples).
            'method' => $method_id,
            'criteria' => $this->buildCriteriaMetadata($slot, $scope_ids),
            'deadline' => $review->endsAt ? $review->endsAt->format('c') : null,
            'status' => self::STATUS_LABELS[(int) $review->status] ?? (string) $review->status,
            'correctionType' => $review->correctionType,
            'originalEvaluation' => (object) $original,
            'draft' => $draft !== null ? (object) $draft : null,
        ];

        if ($this->shouldShowOriginalValuerName($review)) {
            $payload['targetEvaluatorName'] = $this->resolveEvaluatorName($review);
        }

        if ($method_id === 'documentary') {
            $payload['fields'] = $this->buildDocumentaryFields($review, $scope_ids);
        } elseif ($method_id === 'simple') {
            $payload['statusOptions'] = $this->buildSimpleStatusOptions();
        }

        if ($this->shouldShowAppealOpinion($review)) {
            $payload['committeeOpinion'] = $this->buildCommitteeOpinion($review);
        }

        return $payload;
    }

    /**
     * Designações do corretor (aba "Correções designadas" de Minhas
     * avaliações): ativas primeiro, depois as enviadas como histórico.
     * `available` = ativa e dentro da janela de prazo — a mesma regra que o
     * ambiente aplica em assertCanAccess.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listForCorrector($user): array
    {
        $app = App::i();
        $this->assertFeatureEnabled();

        if ($user->is('guest')) {
            return [];
        }

        /** @var RegistrationAppealReview[] $reviews */
        $reviews = $app->repo(RegistrationAppealReview::class)->findBy(
            ['correctorUser' => $user->id],
            ['endsAt' => 'ASC', 'id' => 'ASC']
        );

        usort($reviews, static function (RegistrationAppealReview $a, RegistrationAppealReview $b): int {
            if ($a->isActive() !== $b->isActive()) {
                return $a->isActive() ? -1 : 1;
            }

            if (!$a->isActive()) {
                return ($b->sentTimestamp?->getTimestamp() ?? 0) <=> ($a->sentTimestamp?->getTimestamp() ?? 0);
            }

            return 0;
        });

        $now = new DateTime();
        $items = [];

        foreach ($reviews as $review) {
            $registration = $review->registration;
            $opportunity = $registration->opportunity;
            $first_phase = $opportunity->firstPhase ?? $opportunity;
            $not_started = $review->startsAt && $now < $review->startsAt;
            $expired = $review->endsAt && $now > $review->endsAt;

            $items[] = [
                'assignmentId' => (int) $review->id,
                'registrationNumber' => $registration->number,
                'opportunityId' => (int) $opportunity->id,
                'firstPhaseId' => (int) $first_phase->id,
                'opportunityName' => (string) ($opportunity->parent?->name ?? $opportunity->name),
                'phaseName' => (string) $opportunity->name,
                'targetEvaluatorName' => $this->shouldShowOriginalValuerName($review)
                    ? $this->resolveEvaluatorName($review)
                    : null,
                'status' => self::STATUS_LABELS[(int) $review->status] ?? (string) $review->status,
                'startsAt' => $review->startsAt ? $review->startsAt->format('c') : null,
                'deadline' => $review->endsAt ? $review->endsAt->format('c') : null,
                'sentAt' => $review->sentTimestamp ? $review->sentTimestamp->format('c') : null,
                'available' => $review->isActive() && !$not_started && !$expired,
                'correctionUrl' => self::correctionUrl($review),
            ];
        }

        return $items;
    }

    public static function correctionUrl(RegistrationAppealReview $review): string
    {
        return App::i()->createUrl('appealCorrector', 'correction', [$review->id]);
    }

    private function assertCanAccess(RegistrationAppealReview $review, $user): void
    {
        if ($user->is('guest')) {
            throw new PermissionDenied($user, $review, 'correctorEnvironment');
        }

        if (!$review->isActive()) {
            throw new PermissionDenied($user, $review, 'correctorEnvironment');
        }

        if (!$review->correctorUser || !$review->correctorUser->equals($user)) {
            throw new PermissionDenied($user, $review, 'correctorEnvironment');
        }

        $now = new DateTime();
        if ($review->startsAt && $now < $review->startsAt) {
            throw new PermissionDenied($user, $review, 'correctorEnvironment');
        }
        if ($review->endsAt && $now > $review->endsAt) {
            throw new PermissionDenied($user, $review, 'correctorEnvironment');
        }
    }

    private function assertFeatureEnabled(): void
    {
        $module = App::i()->modules['OpportunityAppealPhase'] ?? null;
        $default = false;
        if ($module instanceof Module) {
            $default = (bool) ($module->config['featureFlag.appealScoreCorrection'] ?? false);
        }

        if (!(bool) env('APPEAL_SCORE_CORRECTION', $default)) {
            throw new PermissionDenied(App::i()->user, null, 'correctorEnvironment');
        }
    }

    private function resolveEvaluatorName(RegistrationAppealReview $review): string
    {
        $owner = $review->slotOwnerUser;
        if ($owner && $owner->profile) {
            return (string) $owner->profile->name;
        }

        return $owner ? (string) $owner->email : '';
    }

    /**
     * Metadata of the criteria released for correction, from the MAIN phase
     * evaluation method configuration (not the appeal phase). Criteria ids in
     * the scope that do not exist in the configuration are ignored.
     *
     * @param string[]|null $scope_ids null = all criteria
     * @return array<int, array<string, mixed>>
     */
    private function buildCriteriaMetadata(RegistrationEvaluation $slot, ?array $scope_ids): array
    {
        $emc = $slot->registration->opportunity->evaluationMethodConfiguration;
        if (!$emc) {
            return [];
        }

        $sections = $emc->sections ?? [];
        $criteria = $emc->criteria ?? [];

        // metadata may still be raw JSON strings before unserialize (same normalization
        // as EvaluationMethodConfiguration::validateCriteriaSectionsIntegrity)
        if (is_string($sections)) {
            $sections = json_decode($sections);
        }
        if (is_string($criteria)) {
            $criteria = json_decode($criteria);
        }

        $sections = (array) $sections;
        $criteria = (array) $criteria;

        $section_names = [];
        foreach ($sections as $section) {
            $section = (object) $section;
            if (isset($section->id)) {
                $section_names[(string) $section->id] = (string) ($section->name ?? '');
            }
        }

        $out = [];
        foreach ($criteria as $criterion) {
            $criterion = (object) $criterion;
            if (!isset($criterion->id)) {
                continue;
            }

            $id = (string) $criterion->id;
            if ($scope_ids !== null && !in_array($id, $scope_ids, true)) {
                continue;
            }

            $sid = isset($criterion->sid) ? (string) $criterion->sid : null;

            $out[] = [
                'id' => $id,
                'title' => (string) ($criterion->title ?? $id),
                'min' => (float) ($criterion->min ?? 0),
                'max' => (float) ($criterion->max ?? 0),
                'weight' => (float) ($criterion->weight ?? 1),
                'sectionId' => $sid,
                'sectionName' => $sid !== null ? ($section_names[$sid] ?? '') : null,
            ];
        }

        return $out;
    }

    /**
     * F7 (#51): campos exigidos pela qualificação documental (mesma fonte do
     * formulário documentary-evaluation-form): configurações de campo e de
     * anexo das fases da oportunidade, chaveadas por fieldName/fileGroupName
     * — as mesmas chaves do evaluationData e do releasedScope. Filtrado pelo
     * escopo liberado.
     *
     * @param string[]|null $scope_ids null = todos
     * @return array<int, array<string, mixed>>
     */
    private function buildDocumentaryFields(RegistrationAppealReview $review, ?array $scope_ids): array
    {
        $main_phase = $review->registration->opportunity;

        $items = [];

        foreach ($main_phase->allPhases as $phase) {
            foreach ($phase->registrationFieldConfigurations as $field) {
                $items[$field->getFieldName()] = (string) ($field->title ?? $field->getFieldName());
            }

            foreach ($phase->registrationFileConfigurations as $file) {
                $items[$file->fileGroupName] = (string) ($file->title ?? $file->fileGroupName);
            }
        }

        $out = [];
        foreach ($items as $id => $title) {
            if ($scope_ids !== null && !in_array((string) $id, $scope_ids, true)) {
                continue;
            }

            $out[] = [
                'id' => (string) $id,
                'title' => $title,
            ];
        }

        return $out;
    }

    /**
     * F7 (#51): opções de resultado global do método simples — mesmas
     * values/labels do formulário simple-evaluation-form.
     *
     * @return array<int, array<string, string>>
     */
    private function buildSimpleStatusOptions(): array
    {
        return [
            ['value' => '2', 'label' => i::__('Inválida')],
            ['value' => '3', 'label' => i::__('Não selecionada')],
            ['value' => '8', 'label' => i::__('Suplente')],
            ['value' => '10', 'label' => i::__('Selecionada')],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param string[]|null $scope_ids
     * @return array<string, mixed>
     */
    private function filterEvaluationData(array $data, ?array $scope_ids): array
    {
        if ($scope_ids === null) {
            return $data;
        }

        // obs/viability não são critérios: seguem sempre liberados (parecer
        // da correção e exequibilidade), alinhado a mergeWithinReleasedScope.
        $filtered = [];
        foreach ($data as $key => $value) {
            if ($key === 'obs' || $key === 'viability' || in_array((string) $key, $scope_ids, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function shouldShowAppealOpinion(RegistrationAppealReview $review): bool
    {
        $scope = $review->releasedScope;
        if ($scope === null) {
            return false;
        }

        $scope = (array) $scope;
        return !empty($scope['showAppealOpinion']);
    }

    /**
     * Nome do avaliador original (dono do slot). Ausente no escopo = true
     * (designações antigas sempre exibiam o nome); false explícito oculta.
     */
    private function shouldShowOriginalValuerName(RegistrationAppealReview $review): bool
    {
        $scope = $review->releasedScope;
        if ($scope === null) {
            return true;
        }

        $scope = (array) $scope;
        if (!array_key_exists('showOriginalValuerName', $scope)) {
            return true;
        }

        return !empty($scope['showOriginalValuerName']);
    }

    /**
     * Campos do formulário liberados na designação.
     * null = todos (designação antiga ou "selecionar todos").
     *
     * @return string[]|null
     */
    private function getReleasedFormFieldIds(RegistrationAppealReview $review): ?array
    {
        $scope = $review->releasedScope;
        if ($scope === null) {
            return null;
        }

        $scope = (array) $scope;
        if (!array_key_exists('formFields', $scope)) {
            return null;
        }

        if ($scope['formFields'] === null) {
            return null;
        }

        return array_map('strval', (array) $scope['formFields']);
    }

    private function isFormFieldReleased(?array $released, string $field_id): bool
    {
        return $released === null || in_array($field_id, $released, true);
    }

    /**
     * Formulário da inscrição filtrado pelos campos liberados ao corretor.
     *
     * @return array<string, mixed>
     */
    private function buildVisibleForm(RegistrationAppealReview $review, Registration $registration): array
    {
        $released = $this->getReleasedFormFieldIds($review);
        $owner = $registration->owner;
        $agents_data = $registration->agentsData;
        $owner_data = null;

        if (is_object($agents_data) && isset($agents_data->owner)) {
            $owner_data = $agents_data->owner;
        } elseif (is_array($agents_data) && isset($agents_data['owner'])) {
            $owner_data = (object) $agents_data['owner'];
        }

        $result = [
            'number' => $registration->number,
            'singleUrl' => $registration->getSingleUrl(),
            'category' => $registration->category,
            'range' => $registration->range,
            'proponentType' => $registration->proponentType,
            'agentsSummary' => null,
            'spaceSummary' => null,
            'fields' => [],
            'files' => [],
        ];

        if ($this->isFormFieldReleased($released, 'agentsSummary')) {
            $result['agentsSummary'] = [
                'owner' => $owner ? $owner->simplify('id,name,singleUrl') : null,
                'ownerSnapshot' => $owner_data ? [
                    'name' => $owner_data->name ?? null,
                    'shortDescription' => $owner_data->shortDescription ?? null,
                    'terms' => $owner_data->terms ?? null,
                ] : null,
            ];
        }

        if ($this->isFormFieldReleased($released, 'spaceSummary')) {
            $spaces = [];
            $space_relation = $registration->getSpaceRelation();
            $relations = is_array($space_relation) ? $space_relation : ($space_relation ? [$space_relation] : []);
            foreach ($relations as $relation) {
                if ($relation->space ?? null) {
                    $spaces[] = $relation->space->simplify('id,name,singleUrl');
                }
            }
            $result['spaceSummary'] = $spaces ?: null;
        }

        $phase_registrations = [];
        $current = $registration;
        while ($current) {
            array_unshift($phase_registrations, $current);
            $current = OpportunityPhases::getPreviousPhaseRegistration($current);
        }

        // Leitura privilegiada: o corretor já passou por assertCanAccess; o ??
        // do PHP NÃO dispara __get de metadados (isset sem __isset), então
        // `$reg->field_X ?? null` sempre virava null mesmo com valor no banco.
        $app = App::i();
        $app->disableAccessControl();
        try {
            foreach ($phase_registrations as $phase_registration) {
                $opportunity = $phase_registration->opportunity;
                $phase_name = (string) $opportunity->name;

                // Garante que field_* desta oportunidade estão registrados no App
                // (necessário para getMetadata/__get resolverem a definição).
                if (method_exists($opportunity, 'registerRegistrationMetadata')) {
                    $opportunity->registerRegistrationMetadata();
                }

                foreach ($opportunity->registrationFieldConfigurations as $field) {
                    $field_id = (string) $field->getFieldName();
                    if (!$this->isFormFieldReleased($released, $field_id)) {
                        continue;
                    }

                    $raw = $phase_registration->$field_id;
                    $formatted = $this->formatFormFieldValue($raw);
                    $result['fields'][] = [
                        'id' => $field_id,
                        'title' => (string) ($field->title ?: $field_id),
                        'type' => (string) ($field->fieldType ?? ''),
                        'phaseName' => $phase_name,
                        'value' => $formatted['text'],
                        'items' => $formatted['items'],
                    ];
                }

                foreach ($opportunity->registrationFileConfigurations as $file_cfg) {
                    $group = (string) $file_cfg->getFileGroupName();
                    if (!$this->isFormFieldReleased($released, $group)) {
                        continue;
                    }

                    $files = $phase_registration->getFiles($group);
                    if (!is_array($files)) {
                        $files = $files ? [$files] : [];
                    }

                    $file_items = [];
                    foreach ($files as $file) {
                        $file_items[] = [
                            'id' => (int) $file->id,
                            'name' => (string) $file->name,
                            'url' => method_exists($file, 'getUrl') ? $file->getUrl() : (string) ($file->url ?? ''),
                        ];
                    }

                    $result['files'][] = [
                        'id' => $group,
                        'title' => (string) ($file_cfg->title ?: $group),
                        'phaseName' => $phase_name,
                        'files' => $file_items,
                    ];
                }
            }
        } finally {
            $app->enableAccessControl();
        }

        return $result;
    }

    /**
     * Normaliza o valor do campo para a UI do corretor.
     * Arquivos/imagens (ex.: agent-owner-field de anexo) viram items
     * tipados; o restante vira texto legível — nunca JSON cru.
     *
     * @return array{text: string, items: array<int, array<string, mixed>>}
     */
    private function formatFormFieldValue(mixed $value): array
    {
        if ($value === null || $value === '') {
            return ['text' => '', 'items' => []];
        }

        if (is_bool($value)) {
            return ['text' => $value ? i::__('Sim') : i::__('Não'), 'items' => []];
        }

        if (is_scalar($value)) {
            return ['text' => (string) $value, 'items' => []];
        }

        $as_array = is_object($value) ? (array) $value : $value;
        if (!is_array($as_array)) {
            return ['text' => '', 'items' => []];
        }

        // Lista homogênea (galeria, downloads, links…)
        if ($as_array !== [] && array_is_list($as_array)) {
            $items = [];
            $texts = [];
            foreach ($as_array as $entry) {
                $formatted = $this->formatFormFieldValue($entry);
                foreach ($formatted['items'] as $item) {
                    $items[] = $item;
                }
                if ($formatted['text'] !== '') {
                    $texts[] = $formatted['text'];
                }
            }

            return [
                'text' => $items ? '' : implode(', ', $texts),
                'items' => $items,
            ];
        }

        $file_item = $this->normalizeFileLikeItem($as_array);
        if ($file_item !== null) {
            return ['text' => '', 'items' => [$file_item]];
        }

        // Link / vídeo MetaList-like: {title, value/url}
        $link_url = $as_array['value'] ?? $as_array['url'] ?? null;
        $link_title = $as_array['title'] ?? $as_array['name'] ?? null;
        if (is_string($link_url) && $link_url !== '' && str_starts_with($link_url, 'http')) {
            return [
                'text' => '',
                'items' => [[
                    'kind' => 'link',
                    'name' => (string) ($link_title ?: $link_url),
                    'url' => $link_url,
                ]],
            ];
        }

        // Endereço / objeto genérico: chave: valor legível
        $parts = [];
        foreach ($as_array as $key => $part) {
            if ($part === null || $part === '') {
                continue;
            }
            if (is_scalar($part)) {
                $parts[] = is_string($key) ? "{$key}: {$part}" : (string) $part;
            }
        }

        return ['text' => implode(' · ', $parts), 'items' => []];
    }

    /**
     * @param array<string, mixed> $data
     * @return array{kind: string, id?: int, name: string, url: string, mimeType?: string}|null
     */
    private function normalizeFileLikeItem(array $data): ?array
    {
        $url = $data['url'] ?? null;
        $name = $data['name'] ?? null;
        $mime = $data['mimeType'] ?? $data['mime'] ?? null;

        if (!is_string($url) || $url === '' || !is_string($name) || $name === '') {
            return null;
        }

        // Exige cara de arquivo (mime ou id) para não confundir com link genérico.
        if ($mime === null && !isset($data['id'])) {
            return null;
        }

        $kind = (is_string($mime) && str_starts_with($mime, 'image/')) ? 'image' : 'file';

        return [
            'kind' => $kind,
            'id' => isset($data['id']) ? (int) $data['id'] : null,
            'name' => $name,
            'url' => $url,
            'mimeType' => is_string($mime) ? $mime : null,
        ];
    }

    /**
     * Parecer da Comissão de Recursos — mesmo formato do "Exibir detalhamento"
     * (evaluationsDetails + consolidatedDetails por método), para N avaliadores.
     *
     * @return array<string, mixed>|null
     */
    private function buildCommitteeOpinion(RegistrationAppealReview $review): ?array
    {
        $app = App::i();
        $appeal_phase = $review->appealPhase;
        $registration = $review->registration;

        if (!$appeal_phase || !$registration) {
            return null;
        }

        $appeal_registration = $app->repo('Registration')->findOneBy([
            'opportunity' => $appeal_phase,
            'number' => $registration->number,
        ]);

        if (!$appeal_registration || !$appeal_registration->evaluationMethod) {
            return null;
        }

        $em = $appeal_registration->evaluationMethod;
        $emc = $appeal_registration->opportunity->evaluationMethodConfiguration;
        $sent_evaluations = $appeal_registration->sentEvaluations;

        if (!$sent_evaluations) {
            return null;
        }

        $evaluations_details = [];
        foreach ($sent_evaluations as $evaluation) {
            $detail = $em->getEvaluationDetails($evaluation);
            $detail['id'] = (int) $evaluation->id;

            if (isset($detail['entityEvaluation']) && $detail['entityEvaluation'] instanceof RegistrationEvaluation) {
                $detail['entityEvaluation'] = $detail['entityEvaluation']->jsonSerialize();
            }

            // Mesmo gate do "Exibir detalhamento": nome do parecerista só
            // com @control na oportunidade ou publishValuerNames no EMC;
            // senão só committeeSequentialNumber (número sequencial).
            if ($emc) {
                OpportunitiesModule::enrichEvaluationDetailWithValuerInfo(
                    $detail,
                    $appeal_registration,
                    $emc,
                    $evaluation,
                    $app
                );
            }

            $evaluations_details[] = $detail;
        }

        if (!$evaluations_details) {
            return null;
        }

        try {
            $consolidated = $em->getConsolidatedDetails($appeal_registration) ?: [];
        } catch (\Throwable $e) {
            $consolidated = [];
        }

        if (empty($consolidated['sentEvaluationCount'])) {
            $consolidated['sentEvaluationCount'] = count($evaluations_details);
        }

        $method_id = $emc ? (string) $emc->type->id : (string) $em->slug;

        return [
            'registrationId' => (int) $appeal_registration->id,
            'method' => $method_id,
            'score' => $appeal_registration->score,
            'consolidatedResult' => $appeal_registration->consolidatedResult,
            'consolidatedDetails' => $consolidated,
            'evaluationsDetails' => $evaluations_details,
        ];
    }

    /**
     * @return string[]|null
     */
    private function getReleasedCriteriaIds(RegistrationAppealReview $review): ?array
    {
        $scope = $review->releasedScope;
        if ($scope === null) {
            return null;
        }

        $scope = (array) $scope;
        $criteria = $scope['criteria'] ?? $scope['fields'] ?? null;

        if ($criteria === null) {
            return null;
        }

        return array_map('strval', (array) $criteria);
    }
}

<?php

namespace OpportunityAppealPhase\Services;

use Doctrine\DBAL\LockMode;
use DomainException;
use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use MapasCulturais\Exceptions\PermissionDenied;
use OpportunityAppealPhase\Entities\AppealTechnicalCorrection;
use OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem;

class AppealTechnicalCorrectionService
{
    public function buildCorrectedEvaluationData(array $criteria, array $before, array $changes): array
    {
        $criteriaById = [];
        foreach ($criteria as $criterion) {
            $criterion = (array) $criterion;
            if (!empty($criterion['id'])) {
                $criteriaById[(string) $criterion['id']] = $criterion;
            }
        }

        if ($changes === []) {
            throw new DomainException('Selecione ao menos um critério para correção.');
        }

        $after = $before;
        $changedCriteria = [];
        foreach ($changes as $criterionId => $value) {
            if (!isset($criteriaById[$criterionId])) {
                throw new DomainException("O critério {$criterionId} não existe mais na configuração técnica.");
            }
            if (!is_numeric($value)) {
                throw new DomainException("A nota do critério {$criterionId} deve ser numérica.");
            }

            $criterion = $criteriaById[$criterionId];
            $value = (float) $value;
            $min = (float) ($criterion['min'] ?? 0);
            $max = (float) ($criterion['max'] ?? 0);
            if ($value < $min || $value > $max) {
                throw new DomainException("A nota do critério {$criterionId} deve estar entre {$min} e {$max}.");
            }

            $oldValue = $before[$criterionId] ?? null;
            if (!is_numeric($oldValue)) {
                throw new DomainException("A avaliação original não possui nota válida para o critério {$criterionId}.");
            }

            if ((float) $oldValue !== $value) {
                $after[$criterionId] = $value;
                $changedCriteria[$criterionId] = [
                    'before' => (float) $oldValue,
                    'after' => $value,
                ];
            }
        }

        if ($changedCriteria === []) {
            throw new DomainException('A correção não altera nenhuma nota. Use a confirmação sem mudança.');
        }

        return [
            'before' => $before,
            'after' => $after,
            'beforeResult' => $this->calculateEvaluationResult($criteria, $before),
            'afterResult' => $this->calculateEvaluationResult($criteria, $after),
            'changedCriteria' => $changedCriteria,
        ];
    }

    public function calculateConsolidatedResult(array $criteria, array $evaluations): float
    {
        if ($evaluations === []) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($evaluations as $evaluationData) {
            $total += $this->calculateEvaluationResult($criteria, (array) $evaluationData);
        }

        return round($total / count($evaluations), 2);
    }

    public function calculateScorePreview(float $consolidatedResult, mixed $appliedPointReward, ?bool $eligible): array
    {
        $reward = $appliedPointReward ? (array) $appliedPointReward : [];
        $type = (string) ($reward['type'] ?? 'percentage');
        $percentage = (float) ($reward['percentage'] ?? 0);
        $fixed = (float) ($reward['fixed'] ?? 0);

        $score = $type === 'fixed'
            ? $consolidatedResult + $fixed
            : $consolidatedResult + (($consolidatedResult * $percentage) / 100);

        $reward['raw'] = $consolidatedResult;
        $reward['type'] = $type;
        $reward['percentage'] = $percentage;
        $reward['fixed'] = $fixed;
        $reward['roof'] = (float) ($reward['roof'] ?? 0);
        $reward['rules'] = array_map(
            fn($rule) => is_object($rule) ? (array) $rule : $rule,
            (array) ($reward['rules'] ?? [])
        );

        return [
            'score' => round($score, 2),
            'eligible' => $eligible,
            'appliedPointReward' => $reward,
        ];
    }

    public function findSourceRegistration(Registration $appeal): Registration
    {
        $this->assertAppealWithTechnicalSource($appeal);
        $source = App::i()->repo('Registration')->findOneBy([
            'opportunity' => $appeal->opportunity->parent,
            'number' => $appeal->number,
        ]);
        if (!$source) {
            throw new DomainException('A inscrição original do recurso não foi encontrada.');
        }
        return $source;
    }

    public function assignRelator(Registration $appeal, User $relator, User $actor): Registration
    {
        $this->assertAppealWithTechnicalSource($appeal);
        if (!$appeal->opportunity->canUser('@control', $actor) && !$actor->is('admin')) {
            throw new PermissionDenied($actor, $appeal, 'designar relator');
        }

        $valuers = (array) $appeal->valuers;
        if (!array_key_exists((string) $relator->id, $valuers)
            && !array_key_exists($relator->id, $valuers)) {
            throw new DomainException('O relator deve ser um avaliador distribuído para este recurso.');
        }

        $app = App::i();
        $draft = $app->repo(AppealTechnicalCorrection::class)->findOneBy([
            'appealRegistration' => $appeal,
            'status' => AppealTechnicalCorrection::STATUS_DRAFT,
        ]);
        $latest = $app->repo(AppealTechnicalCorrection::class)->findOneBy(
            ['appealRegistration' => $appeal],
            ['sequence' => 'DESC']
        );
        if ($latest && !$draft) {
            throw new DomainException('Uma correção finalizada só pode ter o relator alterado após a reabertura.');
        }

        $appeal->appealTechnicalCorrectionRelatorUserId = $relator->id;
        if ($draft) {
            $draft->replaceRelator($relator);
            $app->em->persist($draft);
        }
        $app->disableAccessControl();
        try {
            $appeal->save(true);
        } finally {
            $app->enableAccessControl();
        }
        return $appeal;
    }

    public function getContext(Registration $appeal, User $actor): array
    {
        $source = $this->findSourceRegistration($appeal);
        $this->assertCanView($appeal, $actor);
        $relatorId = (int) ($appeal->appealTechnicalCorrectionRelatorUserId ?? 0);
        $corrections = App::i()->repo(AppealTechnicalCorrection::class)->findBy(
            ['appealRegistration' => $appeal],
            ['sequence' => 'DESC']
        );
        $availableRelators = [];
        foreach (array_keys((array) $appeal->valuers) as $valuerId) {
            $valuer = App::i()->repo('User')->find((int) $valuerId);
            if ($valuer) {
                $availableRelators[] = [
                    'id' => $valuer->id,
                    'name' => $valuer->profile->name,
                ];
            }
        }
        usort($availableRelators, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        $current = $corrections ? $this->serializeCorrection($corrections[0], true) : null;
        if ($corrections && $corrections[0]->status === AppealTechnicalCorrection::STATUS_DRAFT) {
            $current['preview'] = $this->buildDraftPreview($source, $corrections[0]);
        }

        return [
            'appealRegistrationId' => $appeal->id,
            'sourceRegistrationId' => $source->id,
            'relatorUserId' => $relatorId ?: null,
            'canManageRelator' => $appeal->opportunity->canUser('@control', $actor) || $actor->is('admin'),
            'isRelator' => $relatorId === (int) $actor->id,
            'availableRelators' => $availableRelators,
            'criteria' => array_map(fn($criterion) => (array) $criterion, (array) $source->evaluationMethodConfiguration->criteria),
            'evaluations' => array_map(fn(RegistrationEvaluation $evaluation) => [
                'id' => $evaluation->id,
                'version' => $this->evaluationRevision($evaluation),
                'valuer' => $evaluation->user->profile->simplify('id,name'),
                'isTiebreaker' => (bool) $evaluation->isTiebreaker,
                'evaluationData' => (array) $evaluation->evaluationData,
                'result' => is_numeric($evaluation->result) ? (float) $evaluation->result : null,
            ], $source->sentEvaluations),
            'current' => $current,
            'history' => array_values(array_map(
                fn($correction) => $this->serializeCorrection($correction, true),
                array_filter(
                    $corrections,
                    fn($correction) => $correction->status !== AppealTechnicalCorrection::STATUS_DRAFT
                )
            )),
        ];
    }

    public function saveDraft(Registration $appeal, User $actor, array $payload): array
    {
        $this->assertRelator($appeal, $actor);
        $source = $this->findSourceRegistration($appeal);
        $criteria = array_map(fn($criterion) => (array) $criterion, (array) $source->evaluationMethodConfiguration->criteria);
        $reason = trim((string) ($payload['reason'] ?? ''));
        if ($reason === '') {
            throw new DomainException('O motivo da correção é obrigatório.');
        }

        $app = App::i();
        $draft = $app->repo(AppealTechnicalCorrection::class)->findOneBy([
            'appealRegistration' => $appeal,
            'status' => AppealTechnicalCorrection::STATUS_DRAFT,
        ]);
        $expectedVersion = (int) ($payload['expectedVersion'] ?? 0);
        if ($draft && $expectedVersion !== (int) $draft->version) {
            throw new AppealTechnicalCorrectionConflict('A correção foi alterada por outra pessoa.');
        }
        if (!$draft && $expectedVersion !== 0) {
            throw new AppealTechnicalCorrectionConflict('A correção foi criada por outra pessoa.');
        }

        if (!$draft) {
            $draft = new AppealTechnicalCorrection();
            $draft->appealRegistration = $appeal;
            $draft->sourceRegistration = $source;
            $draft->relator = $actor;
            $draft->sequence = $this->nextSequence($appeal);
            $app->em->persist($draft);
        }
        $draft->replaceDraft($reason, ['criteria' => $criteria]);
        $draft->clearItems();

        $changesByEvaluation = [];
        foreach ((array) ($payload['evaluations'] ?? []) as $change) {
            if (!is_array($change)) {
                throw new DomainException('Os dados da avaliação selecionada são inválidos.');
            }
            $evaluationId = (int) ($change['evaluationId'] ?? 0);
            if ($evaluationId <= 0 || isset($changesByEvaluation[$evaluationId])) {
                throw new DomainException('Cada avaliação selecionada deve aparecer uma única vez.');
            }
            $evaluation = $app->repo('RegistrationEvaluation')->find($evaluationId);
            if (!$evaluation
                || !$evaluation->registration->equals($source)
                || $evaluation->status !== RegistrationEvaluation::STATUS_SENT) {
                throw new DomainException('Apenas avaliações técnicas enviadas desta inscrição podem ser corrigidas.');
            }

            $built = $this->buildCorrectedEvaluationData(
                $criteria,
                (array) $evaluation->evaluationData,
                (array) ($change['criteria'] ?? [])
            );
            $item = new AppealTechnicalCorrectionItem();
            $item->targetEvaluation = $evaluation;
            $item->originalValuer = $evaluation->user;
            $item->isTiebreaker = (bool) $evaluation->isTiebreaker;
            $item->captureChange(
                $built['before'],
                $built['after'],
                $built['beforeResult'],
                $built['afterResult'],
                $built['changedCriteria']
            );
            $draft->addItem($item);
            $changesByEvaluation[$evaluationId] = $built;
        }

        if ($changesByEvaluation === []) {
            throw new DomainException('Selecione ao menos uma avaliação técnica para correção.');
        }

        $app->em->flush();
        $serialized = $this->serializeCorrection($draft, true);
        $serialized['preview'] = $this->buildDraftPreview($source, $draft);
        return $serialized;
    }

    public function resolve(Registration $appeal, User $actor, array $payload): AppealTechnicalCorrection
    {
        $this->assertRelator($appeal, $actor);
        $app = App::i();
        $conn = $app->em->getConnection();
        $ownsTransaction = !$conn->isTransactionActive();
        if ($ownsTransaction) {
            $conn->beginTransaction();
        }

        $affectedUsers = [];
        try {
            $appeal = $app->em->find(Registration::class, $appeal->id, LockMode::PESSIMISTIC_WRITE);
            $source = $this->findSourceRegistration($appeal);
            $source = $app->em->find(Registration::class, $source->id, LockMode::PESSIMISTIC_WRITE);
            $draft = $app->repo(AppealTechnicalCorrection::class)->findOneBy([
                'appealRegistration' => $appeal,
                'status' => AppealTechnicalCorrection::STATUS_DRAFT,
            ]);
            if ($draft) {
                $app->em->lock($draft, LockMode::PESSIMISTIC_WRITE);
            }
            $confirmNoChange = filter_var($payload['confirmNoScoreChange'] ?? false, FILTER_VALIDATE_BOOL);
            if (!$draft && !$confirmNoChange) {
                throw new DomainException('Salve a proposta de correção antes de finalizar.');
            }
            if ($draft && (int) ($payload['expectedVersion'] ?? 0) !== (int) $draft->version) {
                throw new AppealTechnicalCorrectionConflict('A correção foi alterada por outra pessoa.');
            }
            if ($draft && !$confirmNoChange) {
                $this->assertDraftUsesCurrentCriteria($source, $draft);
            }

            $reason = trim((string) ($payload['reason'] ?? ($draft?->reason ?? '')));
            $appealEvaluation = $this->saveAppealDecision($appeal, $actor, $reason);
            $this->assertAllAppealEvaluationsSent($appeal);
            $appeal->consolidateResult(true, $appealEvaluation);
            if ((int) $appeal->consolidatedResult !== Registration::STATUS_APPROVED) {
                throw new DomainException('A nota só pode ser corrigida quando o recurso consolidado estiver deferido.');
            }

            $before = $this->currentTotals($source);
            if ($confirmNoChange) {
                if (!$draft) {
                    $draft = $this->createEmptyDraft($appeal, $source, $actor);
                }
                $criteria = array_map(
                    fn($criterion) => (array) $criterion,
                    (array) $source->evaluationMethodConfiguration->criteria
                );
                $draft->replaceDraft($reason, ['criteria' => $criteria]);
                $draft->clearItems();
                $draft->appealEvaluation = $appealEvaluation;
                $draft->markConfirmedNoChange($reason, $before);
            } else {
                $draft->appealEvaluation = $appealEvaluation;
                $items = $draft->items->toArray();
                usort($items, fn($a, $b) => $a->targetEvaluation->id <=> $b->targetEvaluation->id);
                foreach ($items as $item) {
                    $evaluation = $app->em->find(
                        RegistrationEvaluation::class,
                        $item->targetEvaluation->id,
                        LockMode::PESSIMISTIC_WRITE
                    );
                    $evaluationVersions = (array) ($payload['evaluationVersions'] ?? []);
                    if (!array_key_exists($evaluation->id, $evaluationVersions)
                        && !array_key_exists((string) $evaluation->id, $evaluationVersions)) {
                        throw new AppealTechnicalCorrectionConflict('A versão da avaliação técnica não foi informada.');
                    }
                    $expectedRevision = (int) $evaluationVersions[$evaluation->id];
                    if ($expectedRevision !== $this->evaluationRevision($evaluation)) {
                        throw new AppealTechnicalCorrectionConflict('Uma avaliação técnica foi alterada durante a correção.');
                    }
                    $evaluation->setEvaluationData((object) $item->afterEvaluationData);
                    $app->disableAccessControl();
                    try {
                        $evaluation->save(false);
                    } finally {
                        $app->enableAccessControl();
                    }
                    $affectedUsers[$evaluation->user->id] = $evaluation->user;
                }
                $app->em->flush();
                $source->consolidateResult(true, $draft);
                $app->em->refresh($source);
                $draft->markApplied($before, $this->currentTotals($source));
            }

            $app->em->persist($draft);
            $app->em->flush();
            if ($ownsTransaction) {
                $conn->commit();
            }
        } catch (\Throwable $error) {
            if ($ownsTransaction && $conn->isTransactionActive()) {
                $conn->rollBack();
            }
            throw $error;
        }

        foreach ($affectedUsers as $user) {
            try {
                $this->notifyOriginalValuer($user, $source);
            } catch (\Throwable $error) {
                $app->log->error('Falha ao notificar avaliador sobre correção de nota técnica', [
                    'sourceRegistrationId' => $source->id,
                    'userId' => $user->id,
                    'exception' => $error,
                ]);
            }
        }
        return $draft;
    }

    public function reopen(Registration $appeal, User $actor): AppealTechnicalCorrection
    {
        if (!$appeal->opportunity->canUser('@control', $actor) && !$actor->is('admin')) {
            throw new PermissionDenied($actor, $appeal, 'reabrir correção');
        }
        $source = $this->findSourceRegistration($appeal);
        $relator = App::i()->repo('User')->find((int) ($appeal->appealTechnicalCorrectionRelatorUserId ?? 0));
        if (!$relator) {
            throw new DomainException('Defina um relator antes de reabrir a correção.');
        }
        $latest = App::i()->repo(AppealTechnicalCorrection::class)->findOneBy(
            ['appealRegistration' => $appeal],
            ['sequence' => 'DESC']
        );
        if (!$latest || $latest->status === AppealTechnicalCorrection::STATUS_DRAFT) {
            throw new DomainException('Não existe uma correção finalizada para reabrir.');
        }
        $draft = $this->createEmptyDraft($appeal, $source, $relator);
        App::i()->em->flush();
        return $draft;
    }

    public function assertCanView(Registration $appeal, User $actor): void
    {
        $relatorId = (int) ($appeal->appealTechnicalCorrectionRelatorUserId ?? 0);
        if ($relatorId !== (int) $actor->id
            && !$appeal->opportunity->canUser('@control', $actor)
            && !$actor->is('admin')) {
            throw new PermissionDenied($actor, $appeal, 'ver auditoria da correção');
        }
    }

    public function assertRelator(Registration $appeal, User $actor): void
    {
        $this->assertAppealWithTechnicalSource($appeal);
        if ((int) ($appeal->appealTechnicalCorrectionRelatorUserId ?? 0) !== (int) $actor->id) {
            throw new PermissionDenied($actor, $appeal, 'corrigir nota técnica');
        }
        $valuers = (array) $appeal->valuers;
        if (!array_key_exists((string) $actor->id, $valuers) && !array_key_exists($actor->id, $valuers)) {
            throw new DomainException('O relator não está mais distribuído para este recurso.');
        }
    }

    public function serializeCorrection(AppealTechnicalCorrection $correction, bool $includeAudit): array
    {
        $data = [
            'id' => $correction->id,
            'sequence' => $correction->sequence,
            'status' => $correction->status,
            'reason' => $correction->reason,
            'confirmNoScoreChange' => (bool) $correction->confirmNoScoreChange,
            'version' => $correction->version,
            'relator' => $correction->relator->profile->simplify('id,name'),
            'createTimestamp' => $correction->createTimestamp?->format(DATE_ATOM),
            'updateTimestamp' => $correction->updateTimestamp?->format(DATE_ATOM),
        ];
        if ($includeAudit) {
            $data['criteriaConfigurationSnapshot'] = $correction->criteriaConfigurationSnapshot;
            $data['before'] = [
                'consolidatedResult' => $correction->beforeConsolidatedResult,
                'score' => $correction->beforeScore,
                'eligible' => $correction->beforeEligible,
            ];
            $data['after'] = [
                'consolidatedResult' => $correction->afterConsolidatedResult,
                'score' => $correction->afterScore,
                'eligible' => $correction->afterEligible,
            ];
            $data['items'] = array_map(fn($item) => [
                'id' => $item->id,
                'evaluationId' => $item->targetEvaluation->id,
                'valuer' => $item->originalValuer->profile->simplify('id,name'),
                'isTiebreaker' => (bool) $item->isTiebreaker,
                'beforeEvaluationData' => $item->beforeEvaluationData,
                'afterEvaluationData' => $item->afterEvaluationData,
                'beforeResult' => $item->beforeResult,
                'afterResult' => $item->afterResult,
                'changedCriteria' => $item->changedCriteria,
            ], $correction->items->toArray());
        }
        return $data;
    }

    private function calculateEvaluationResult(array $criteria, array $evaluationData): float
    {
        $total = 0.0;
        foreach ($criteria as $criterion) {
            $criterion = (array) $criterion;
            $criterionId = (string) ($criterion['id'] ?? '');
            if ($criterionId === '' || !isset($evaluationData[$criterionId]) || !is_numeric($evaluationData[$criterionId])) {
                throw new DomainException("A avaliação está incompleta no critério {$criterionId}.");
            }
            $total += (float) $evaluationData[$criterionId] * (float) ($criterion['weight'] ?? 0);
        }
        return $total;
    }

    private function assertDraftUsesCurrentCriteria(
        Registration $source,
        AppealTechnicalCorrection $draft
    ): void {
        $currentCriteria = array_map(
            fn($criterion) => (array) $criterion,
            (array) $source->evaluationMethodConfiguration->criteria
        );
        $snapshot = (array) $draft->criteriaConfigurationSnapshot;
        $snapshotCriteria = (array) ($snapshot['criteria'] ?? []);
        if (json_encode($snapshotCriteria) !== json_encode($currentCriteria)) {
            throw new AppealTechnicalCorrectionConflict(
                'A configuração dos critérios técnicos mudou. Revise e salve novamente a proposta.'
            );
        }
    }

    private function buildDraftPreview(Registration $source, AppealTechnicalCorrection $draft): array
    {
        $criteria = array_map(
            fn($criterion) => (array) $criterion,
            (array) $source->evaluationMethodConfiguration->criteria
        );
        $changes = [];
        foreach ($draft->items as $item) {
            $changes[$item->targetEvaluation->id] = (array) $item->afterEvaluationData;
        }

        $evaluationData = [];
        foreach ($source->sentEvaluations as $evaluation) {
            $evaluationData[] = $changes[$evaluation->id] ?? (array) $evaluation->evaluationData;
        }
        $consolidatedResult = $this->calculateConsolidatedResult($criteria, $evaluationData);
        return [
            'consolidatedResult' => $consolidatedResult,
            ...$this->calculateScorePreview(
                $consolidatedResult,
                $source->appliedPointReward,
                $source->eligible === null ? null : (bool) $source->eligible
            ),
        ];
    }

    private function assertAppealWithTechnicalSource(Registration $appeal): void
    {
        if (!$appeal->opportunity?->isAppealPhase || !$appeal->opportunity->parent) {
            throw new DomainException('A inscrição informada não pertence a uma fase de recurso.');
        }
        if ($appeal->opportunity->parent->evaluationMethod?->slug !== 'technical') {
            throw new DomainException('A fase anterior não utiliza avaliação técnica.');
        }
    }

    private function nextSequence(Registration $appeal): int
    {
        $result = App::i()->em->createQuery(
            'SELECT MAX(c.sequence) FROM ' . AppealTechnicalCorrection::class . ' c WHERE c.appealRegistration = :appeal'
        )->setParameter('appeal', $appeal)->getSingleScalarResult();
        return ((int) $result) + 1;
    }

    private function createEmptyDraft(Registration $appeal, Registration $source, User $actor): AppealTechnicalCorrection
    {
        $existing = App::i()->repo(AppealTechnicalCorrection::class)->findOneBy([
            'appealRegistration' => $appeal,
            'status' => AppealTechnicalCorrection::STATUS_DRAFT,
        ]);
        if ($existing) {
            throw new DomainException('Já existe uma correção em aberto para este recurso.');
        }
        $draft = new AppealTechnicalCorrection();
        $draft->appealRegistration = $appeal;
        $draft->sourceRegistration = $source;
        $draft->relator = $actor;
        $draft->sequence = $this->nextSequence($appeal);
        App::i()->em->persist($draft);
        return $draft;
    }

    private function saveAppealDecision(Registration $appeal, User $actor, string $reason): RegistrationEvaluation
    {
        if ($reason === '') {
            throw new DomainException('O motivo do deferimento é obrigatório.');
        }
        $evaluation = $appeal->getUserEvaluation($actor) ?: new RegistrationEvaluation();
        $evaluation->registration = $appeal;
        $evaluation->user = $actor;
        $evaluation->setEvaluationData((object) [
            'status' => Registration::STATUS_APPROVED,
            'obs' => $reason,
        ]);
        $evaluation->status = RegistrationEvaluation::STATUS_SENT;
        $evaluation->sentTimestamp = new \DateTime();
        $app = App::i();
        $app->disableAccessControl();
        try {
            $evaluation->save(true);
        } finally {
            $app->enableAccessControl();
        }
        return $evaluation;
    }

    private function assertAllAppealEvaluationsSent(Registration $appeal): void
    {
        foreach (array_keys((array) $appeal->valuers) as $valuerId) {
            $evaluation = App::i()->repo('RegistrationEvaluation')->findOneBy([
                'registration' => $appeal,
                'user' => (int) $valuerId,
                'status' => RegistrationEvaluation::STATUS_SENT,
            ]);
            if (!$evaluation) {
                throw new DomainException('Todos os avaliadores do recurso devem enviar seus pareceres antes do relator.');
            }
        }
    }

    private function currentTotals(Registration $source): array
    {
        return [
            'consolidatedResult' => is_numeric($source->consolidatedResult) ? (float) $source->consolidatedResult : null,
            'score' => is_numeric($source->score) ? (float) $source->score : null,
            'eligible' => $source->eligible === null ? null : (bool) $source->eligible,
        ];
    }

    private function evaluationRevision(RegistrationEvaluation $evaluation): int
    {
        return (int) (App::i()->conn->fetchOne(
            'SELECT COALESCE(MAX(id), 0) FROM entity_revision WHERE object_type = :type AND object_id = :id',
            ['type' => RegistrationEvaluation::class, 'id' => $evaluation->id]
        ) ?: 0);
    }

    private function notifyOriginalValuer(User $user, Registration $source): void
    {
        $notification = new Notification();
        $notification->user = $user;
        $notification->message = sprintf(
            'Uma avaliação técnica da inscrição %s foi corrigida após deferimento de recurso.',
            $source->number
        );
        $notification->save(true);
    }
}

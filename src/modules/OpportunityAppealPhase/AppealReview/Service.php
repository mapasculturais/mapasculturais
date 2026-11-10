<?php

namespace OpportunityAppealPhase\AppealReview;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\EntityRevision;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use OpportunityAppealPhase\Module;

/**
 * Aplica correção de nota pós-recurso sobre o slot original (RegistrationEvaluation).
 *
 * Spec: AppealReview\Service::applyCorrection (PR4 / issue #13).
 */
class Service
{
    /**
     * Aplica a correção in-place no slot designado e reconsolida a inscrição.
     *
     * Os dados corrigidos vêm de `$review->correctedValue` (rascunho) ou do
     * parâmetro opcional `$corrected_data` (atalho para envio em um passo).
     *
     * @param RegistrationAppealReview $review Designação ativa
     * @param array|object|null $corrected_data Dados de avaliação (critérios)
     */
    public function applyCorrection(RegistrationAppealReview $review, array|object|null $corrected_data = null): RegistrationAppealReview
    {
        $app = App::i();
        $this->assertFeatureEnabled();

        if (!$review->isActive()) {
            throw new PermissionDenied($app->user, $review, 'applyCorrection');
        }

        $slot = $review->originalEvaluation;
        if (!$slot) {
            throw new PermissionDenied($app->user, $review, 'applyCorrection');
        }

        $user = $app->user;
        if ($user->is('guest') || !$review->correctorUser || !$review->correctorUser->equals($user)) {
            throw new PermissionDenied($user, $review, 'applyCorrection');
        }

        $this->assertWithinWindow($review);
        $this->assertEvaluationMethodResolvable($slot);

        if ($corrected_data !== null) {
            $review->correctedValue = (object) (array) $corrected_data;
        }

        $incoming = (array) ($review->correctedValue ?: []);
        if (!$incoming) {
            throw new \InvalidArgumentException(i::__('Dados da correção são obrigatórios.'));
        }

        $merged = $this->mergeWithinReleasedScope($slot, $review, $incoming);
        $corrected_score = $this->computeScore($slot, $merged);

        if ($review->originalValue === null) {
            $review->originalValue = $slot->getEvaluationData();
        }
        if ($review->originalScore === null) {
            $review->originalScore = is_numeric($slot->result) ? (float) $slot->result : null;
        }

        $is_official = $review->correctionType !== RegistrationAppealReview::CORRECTION_TYPE_RECORD;

        $revision_message = sprintf(
            i::__('Correção de recurso (tipo %s) pelo usuário %s no slot de avaliação #%s (avaliador original #%s).'),
            $review->correctionType ?: RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL,
            $user->id,
            $slot->id,
            $slot->user->id
        );

        $app->em->beginTransaction();

        try {
            $app->disableAccessControl();

            if ($is_official) {
                $this->applyOfficialCorrection($slot, $merged, $revision_message);
            } else {
                $this->applyRecordCorrection($slot, $merged, $revision_message);
            }

            $review->correctedValue = (object) $merged;
            $review->correctedScore = $corrected_score;
            $review->status = RegistrationAppealReview::STATUS_SENT;
            $review->sentTimestamp = new DateTime();
            $review->save(true);

            $app->enableAccessControl();
            $app->em->commit();
        } catch (\Throwable $e) {
            $app->em->rollback();
            if (!$app->isAccessControlEnabled()) {
                $app->enableAccessControl();
            }
            throw $e;
        }

        // PR5 (issue #15): notifica correção enviada — somente após o commit
        Notifier::notifyCorrectionSent($review);

        return $review;
    }

    /**
     * Persiste rascunho da correção sem alterar a nota oficial.
     */
    public function saveDraft(RegistrationAppealReview $review, array|object $corrected_data): RegistrationAppealReview
    {
        $app = App::i();
        $this->assertFeatureEnabled();

        if (!$review->isActive()) {
            throw new PermissionDenied($app->user, $review, 'saveDraft');
        }

        $user = $app->user;
        if ($user->is('guest') || !$review->correctorUser || !$review->correctorUser->equals($user)) {
            throw new PermissionDenied($user, $review, 'saveDraft');
        }

        $this->assertWithinWindow($review);

        $slot = $review->originalEvaluation;
        $incoming = (array) $corrected_data;
        $merged = $this->mergeWithinReleasedScope($slot, $review, $incoming);

        if ($review->originalValue === null) {
            $review->originalValue = $slot->getEvaluationData();
        }
        if ($review->originalScore === null) {
            $review->originalScore = is_numeric($slot->result) ? (float) $slot->result : null;
        }

        $review->correctedValue = (object) $merged;
        $review->correctedScore = $this->computeScore($slot, $merged);
        if ($review->status === RegistrationAppealReview::STATUS_DESIGNATED) {
            $review->status = RegistrationAppealReview::STATUS_DRAFT;
        }
        $review->save(true);

        return $review;
    }

    private function applyOfficialCorrection(RegistrationEvaluation $slot, array $merged, string $revision_message): void
    {
        $app = App::i();

        $slot->setEvaluationData((object) $merged);
        $slot->status = RegistrationEvaluation::STATUS_SENT;
        if (!$slot->sentTimestamp) {
            $slot->sentTimestamp = new DateTime();
        }
        // save() gera revisão padrão; sobrescrevemos a mensagem logo em seguida.
        $slot->save(true);
        $this->setLastRevisionMessage($slot, $revision_message);

        $registration = $slot->registration;
        $registration->consolidateResult(true, $slot);

        $app->enqueueEntityToPCacheRecreation($registration);
        $app->persistPCachePendingQueue();
    }

    /**
     * correction_type=record: grava revisão de auditoria sem alterar evaluationData/consolidação.
     */
    private function applyRecordCorrection(RegistrationEvaluation $slot, array $merged, string $revision_message): void
    {
        $message = $revision_message . ' ' . i::__('(somente registro — nota oficial não alterada)');

        $revision_data = $slot->_getRevisionData();
        $revision_data['appealCorrectionRecord'] = [
            'correctedEvaluationData' => $merged,
            'note' => 'record',
        ];

        $revision = new EntityRevision(
            $revision_data,
            $slot,
            EntityRevision::ACTION_MODIFIED,
            $message,
            flush: true
        );

        if ($revision->modified) {
            $revision->save(true);
        } else {
            // Garante revisão de auditoria mesmo sem mudança no evaluationData oficial
            $revision->save(true);
        }
    }

    private function setLastRevisionMessage(RegistrationEvaluation $slot, string $message): void
    {
        $revision = $slot->getLastRevision();
        if (!$revision) {
            return;
        }

        $revision->message = $message;
        $revision->save(true);
    }

    /**
     * Resultado recalculado pelo método do slot (setEvaluationData delega ao
     * EvaluationMethod::getEvaluationResult): technical = pontuação ponderada;
     * simple = código do status global; documentary = 1/-1 (válida/inválida).
     */
    private function computeScore(RegistrationEvaluation $slot, array $evaluation_data): ?float
    {
        $tmp = new RegistrationEvaluation();
        $tmp->registration = $slot->registration;
        $tmp->user = $slot->user;
        $tmp->setEvaluationData((object) $evaluation_data);

        return is_numeric($tmp->result) ? (float) $tmp->result : null;
    }

    private function assertFeatureEnabled(): void
    {
        $module = App::i()->modules['OpportunityAppealPhase'] ?? null;
        $default = false;
        if ($module instanceof Module) {
            $default = (bool) ($module->config['featureFlag.appealScoreCorrection'] ?? false);
        }

        if (!(bool) env('APPEAL_SCORE_CORRECTION', $default)) {
            throw new PermissionDenied(App::i()->user, null, 'applyCorrection');
        }
    }

    private function assertWithinWindow(RegistrationAppealReview $review): void
    {
        $now = new DateTime();

        if ($review->startsAt && $now < $review->startsAt) {
            throw new PermissionDenied(App::i()->user, $review, 'applyCorrection');
        }

        if ($review->endsAt && $now > $review->endsAt) {
            throw new PermissionDenied(App::i()->user, $review, 'applyCorrection');
        }
    }

    /**
     * F7 (#51): a correção vale para TODOS os métodos de avaliação (antes
     * restrita ao técnico). O que a aplicação exige do slot é apenas ter um
     * método resolvível (EMC presente) — é o método que define o formato do
     * evaluationData e o cálculo do resultado:
     * - technical: critérios (ids) → pontuação ponderada;
     * - documentary: fieldName/fileGroupName → {evaluation, obs} por campo;
     * - simple: resultado global (status) + obs.
     */
    private function assertEvaluationMethodResolvable(RegistrationEvaluation $slot): void
    {
        $emc = $slot->registration->opportunity->evaluationMethodConfiguration;
        if (!$emc) {
            throw new PermissionDenied(App::i()->user, $slot, 'applyCorrection');
        }
    }

    /**
     * Mescla apenas chaves liberadas em released_scope.criteria (ou .fields).
     * Chaves fora do escopo com valor diferente do atual são rejeitadas;
     * chaves fora do escopo iguais ao atual (ex.: rascunho com snapshot completo) são ignoradas.
     *
     * Genérico por método (F7): as chaves são as do evaluationData — critérios
     * (technical), fieldName/fileGroupName (documentary; o obs por campo viaja
     * DENTRO do objeto do campo) ou status/obs (simple).
     */
    private function mergeWithinReleasedScope(RegistrationEvaluation $slot, RegistrationAppealReview $review, array $incoming): array
    {
        $current = (array) $slot->getEvaluationData();
        $scope = $this->getReleasedCriteriaIds($review);

        if ($scope === null) {
            return array_merge($current, $incoming);
        }

        $out_of_scope = [];
        foreach ($incoming as $key => $value) {
            if ($key === 'obs' || $key === 'viability') {
                continue;
            }
            if (!in_array((string) $key, $scope, true)) {
                $current_value = $current[$key] ?? null;
                if (json_encode($value) !== json_encode($current_value)) {
                    $out_of_scope[] = $key;
                }
            }
        }

        if ($out_of_scope) {
            throw new \InvalidArgumentException(
                sprintf(i::__('Critérios fora do escopo liberado: %s'), implode(', ', $out_of_scope))
            );
        }

        foreach ($incoming as $key => $value) {
            if ($key === 'obs' || $key === 'viability' || in_array((string) $key, $scope, true)) {
                $current[$key] = $value;
            }
        }

        return $current;
    }

    /**
     * @return string[]|null null = sem restrição
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

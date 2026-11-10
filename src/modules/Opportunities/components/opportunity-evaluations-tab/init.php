<?php

use MapasCulturais\Entities\Opportunity;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;

$entity = $this->controller->requestedEntity;

$isEvaluator = false;
$hasAppealCorrections = false;

// Verifica se é avaliador na oportunidade principal
if ($comm = $entity->getEvaluationCommittee()) {
    foreach($comm as $member) {
        if($member->agent->owner->user->id == $app->user->id) {
            $isEvaluator = true;
            break;
        }
    }
}

// Se não for avaliador na principal, verifica em todas as fases
if (!$isEvaluator && $entity->allPhases) {
    foreach ($entity->allPhases as $phase) {
        if ($phase->id == $entity->id) {
            continue; // Já verificou a principal
        }
        
        if ($comm = $phase->getEvaluationCommittee()) {
            foreach($comm as $member) {
                if($member->agent->owner->user->id == $app->user->id) {
                    $isEvaluator = true;
                    break 2; // Sai dos dois loops
                }
            }
        }
    }
}

// Verifica se é avaliador na fase de recurso (appeal phase)
if (!$isEvaluator && $entity->appealPhase) {
    $appealPhase = $entity->appealPhase;
    if ($appealPhase->evaluationMethodConfiguration) {
        $comm = $appealPhase->evaluationMethodConfiguration->getCommittee(true);
        foreach($comm as $member) {
            if($member->agent->owner->user->id == $app->user->id) {
                $isEvaluator = true;
                break;
            }
        }
    }
}

// Corretor designado nesta oportunidade (correção de notas do recurso).
// Não usar `??` em associações Doctrine: isset() em proxy lazy devolve false
// e a oportunidade seria tratada como nula sem carregar a relação.
$appeal_module = $app->modules['OpportunityAppealPhase'] ?? null;
if (
    $appeal_module
    && method_exists($appeal_module, 'isAppealScoreCorrectionEnabled')
    && $appeal_module->isAppealScoreCorrectionEnabled()
    && !$app->user->is('guest')
    && $entity instanceof Opportunity
) {
    $first_phase_id = (int) ($entity->firstPhase->id ?? $entity->id);
    $reviews = $app->repo(RegistrationAppealReview::class)->findBy([
        'correctorUser' => $app->user->id,
    ]);

    foreach ($reviews as $review) {
        $registration = $review->registration;
        if (!$registration) {
            continue;
        }

        $opportunity = $registration->opportunity;
        if (!$opportunity) {
            continue;
        }

        $review_first = (int) ($opportunity->firstPhase->id ?? $opportunity->id);
        if (
            (int) $opportunity->id === (int) $entity->id
            || $review_first === $first_phase_id
            || $review_first === (int) $entity->id
        ) {
            $hasAppealCorrections = true;
            break;
        }
    }
}

$this->jsObject['config']['opportunityEvaluationsTab']['isEvaluator'] = $isEvaluator;
$this->jsObject['config']['opportunityEvaluationsTab']['hasAppealCorrections'] = $hasAppealCorrections;
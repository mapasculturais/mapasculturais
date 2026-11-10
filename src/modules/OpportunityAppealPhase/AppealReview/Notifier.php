<?php

namespace OpportunityAppealPhase\AppealReview;

use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\i;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use OpportunityAppealPhase\Module;

/**
 * Notificações internas e e-mails da correção de notas pós-recurso.
 *
 * Spec: PR5 / issue #15.
 * - Designação: corretor recebe notificação interna + e-mail.
 * - Envio definitivo: corretor recebe confirmação; gestores (group-admin)
 *   da oportunidade de origem são notificados.
 * - Tudo gated por APPEAL_SCORE_CORRECTION; e-mails additionally gated pela
 *   config sendMailNotification.opportunityAppealPhase (padrão do módulo).
 */
class Notifier
{
    public static function notifyDesignation(RegistrationAppealReview $review): void
    {
        if (!self::isScoreCorrectionEnabled()) {
            return;
        }

        $corrector = $review->correctorUser;
        if (!$corrector) {
            return;
        }

        $registration = $review->registration;
        $opportunity = $registration->opportunity;
        $deadline = $review->endsAt
            ? $review->endsAt->format('d/m/Y H:i')
            : i::__('sem prazo definido');

        $message = sprintf(
            i::__('Você foi designado(a) corretor(a) da avaliação da inscrição %s em %s. Prazo para correção: %s.'),
            $registration->number,
            $opportunity->name,
            $deadline
        );

        $notification_message = sprintf(
            '%s <a href="%s">%s</a>',
            $message,
            CorrectorEnvironment::correctionUrl($review),
            i::__('Acessar correção')
        );

        self::sendNotification($corrector, $notification_message);
        self::sendEmail($corrector, $review, true, $message);
    }

    /**
     * Reabertura pelo gestor: corretor é avisado de que pode revisar e
     * reenviar a correção (mesmo template de e-mail da designação).
     */
    public static function notifyReopened(RegistrationAppealReview $review): void
    {
        if (!self::isScoreCorrectionEnabled()) {
            return;
        }

        $corrector = $review->correctorUser;
        if (!$corrector) {
            return;
        }

        $registration = $review->registration;
        $opportunity = $registration->opportunity;
        $deadline = $review->endsAt
            ? $review->endsAt->format('d/m/Y H:i')
            : i::__('sem prazo definido');

        $message = sprintf(
            i::__('A correção da avaliação da inscrição %s em %s foi reaberta pela gestão. Revise e envie novamente. Prazo para correção: %s.'),
            $registration->number,
            $opportunity->name,
            $deadline
        );

        $notification_message = sprintf(
            '%s <a href="%s">%s</a>',
            $message,
            CorrectorEnvironment::correctionUrl($review),
            i::__('Acessar correção')
        );

        self::sendNotification($corrector, $notification_message);
        self::sendEmail(
            $corrector,
            $review,
            true,
            $message,
            sprintf(i::__('Correção de avaliação reaberta em %s'), $opportunity->name)
        );
    }

    /**
     * Prazo alterado pelo gestor em designação ativa.
     */
    public static function notifyDeadlineChanged(RegistrationAppealReview $review): void
    {
        if (!self::isScoreCorrectionEnabled()) {
            return;
        }

        $corrector = $review->correctorUser;
        if (!$corrector) {
            return;
        }

        $registration = $review->registration;
        $opportunity = $registration->opportunity;
        $deadline = $review->endsAt
            ? $review->endsAt->format('d/m/Y H:i')
            : i::__('sem prazo definido');

        $message = sprintf(
            i::__('O prazo da correção da avaliação da inscrição %s em %s foi alterado pela gestão. Novo prazo: %s.'),
            $registration->number,
            $opportunity->name,
            $deadline
        );

        $notification_message = sprintf(
            '%s <a href="%s">%s</a>',
            $message,
            CorrectorEnvironment::correctionUrl($review),
            i::__('Acessar correção')
        );

        self::sendNotification($corrector, $notification_message);
        self::sendEmail(
            $corrector,
            $review,
            true,
            $message,
            sprintf(i::__('Prazo de correção alterado em %s'), $opportunity->name)
        );
    }

    public static function notifyCorrectionSent(RegistrationAppealReview $review): void
    {
        if (!self::isScoreCorrectionEnabled()) {
            return;
        }

        $corrector = $review->correctorUser;
        $registration = $review->registration;
        $opportunity = $registration->opportunity;

        $corrector_name = '';
        if ($corrector) {
            $corrector_name = $corrector->profile
                ? (string) $corrector->profile->name
                : (string) $corrector->email;
        }

        if ($corrector) {
            $message = sprintf(
                i::__('Sua correção da avaliação da inscrição %s em %s foi aplicada e enviada.'),
                $registration->number,
                $opportunity->name
            );

            self::sendNotification($corrector, $message);
            self::sendEmail($corrector, $review, false, $message);
        }

        foreach (self::managerRelations($opportunity) as $relation) {
            if ($relation->group !== 'group-admin' || !$relation->agent || !$relation->agent->ownerUser) {
                continue;
            }

            $manager = $relation->agent->ownerUser;
            if ($corrector && $manager->equals($corrector)) {
                continue;
            }

            $message = sprintf(
                i::__('A correção da avaliação da inscrição %s em %s foi enviada pelo corretor %s.'),
                $registration->number,
                $opportunity->name,
                $corrector_name
            );

            self::sendNotification($manager, $message);
            self::sendEmail($manager, $review, false, $message);
        }
    }

    /**
     * Relações group-admin ativas, consultadas direto no repositório para
     * não depender do cache __agentRelations da entidade oportunidade.
     *
     * @param \MapasCulturais\Entities\Opportunity $opportunity
     * @return \MapasCulturais\Entities\OpportunityAgentRelation[]
     */
    private static function managerRelations($opportunity): array
    {
        $app = App::i();
        $relations = $app->repo(\MapasCulturais\Entities\OpportunityAgentRelation::class)
            ->findBy(['owner' => $opportunity]);

        $agent_statuses = [
            \MapasCulturais\Entities\Agent::STATUS_ENABLED,
            \MapasCulturais\Entities\Agent::STATUS_INVITED,
            \MapasCulturais\Entities\Agent::STATUS_RELATED,
        ];

        return array_values(array_filter(
            $relations,
            fn ($relation) => $relation->status > 0
                && $relation->agent
                && in_array($relation->agent->status, $agent_statuses, true)
        ));
    }

    private static function isScoreCorrectionEnabled(): bool
    {
        $module = App::i()->modules['OpportunityAppealPhase'] ?? null;
        $default = false;
        if ($module instanceof Module) {
            $default = (bool) ($module->config['featureFlag.appealScoreCorrection'] ?? false);
        }

        return (bool) env('APPEAL_SCORE_CORRECTION', $default);
    }

    private static function isMailEnabled(): bool
    {
        $module = App::i()->modules['OpportunityAppealPhase'] ?? null;
        $default = false;
        if ($module instanceof Module) {
            $default = (bool) ($module->config['sendMailNotification.opportunityAppealPhase'] ?? false);
        }

        return (bool) env('SEND_MAIL_OPPORTUNITY_APPEAL_PHASE', $default);
    }

    private static function sendNotification($user, string $message): void
    {
        $notification = new Notification;
        $notification->user = $user;
        $notification->message = $message;
        $notification->save(true);
    }

    private static function sendEmail($user, RegistrationAppealReview $review, bool $is_designation, string $message, ?string $subject = null): void
    {
        if (!self::isMailEnabled()) {
            return;
        }

        $app = App::i();

        $email = null;
        if ($user->profile) {
            $email = $user->profile->emailPrivado ?? $user->profile->emailPublico ?? null;
        }
        $email = $email ?? $user->email;

        if (!$email) {
            return;
        }

        $registration = $review->registration;
        $original_opportunity = $registration->opportunity;
        $appeal_phase = $review->appealPhase;

        $subject = $subject ?? ($is_designation
            ? sprintf(i::__('Você foi designado(a) corretor(a) em %s'), $original_opportunity->name)
            : sprintf(i::__('Correção de avaliação enviada em %s'), $original_opportunity->name));

        $params = [
            'siteName' => $app->siteName,
            'message' => $message,
            'isDesignation' => $is_designation,
            'isCorrectionSent' => !$is_designation,
            'opportunityName' => $original_opportunity->name,
            'opportunityUrl' => $original_opportunity->singleUrl,
            'phaseName' => $appeal_phase ? $appeal_phase->name : null,
            'phaseUrl' => $appeal_phase ? $appeal_phase->singleUrl : null,
            'registrationNumber' => $registration->number,
            'registrationUrl' => $registration->singleUrl,
            'correctionUrl' => CorrectorEnvironment::correctionUrl($review),
            'deadline' => $review->endsAt ? $review->endsAt->format('d/m/Y H:i') : null,
        ];

        $template = $is_designation
            ? 'opportunityappealphase/correction-designation.html'
            : 'opportunityappealphase/correction-sent.html';

        $app->createAndSendMailMessage([
            'from' => $app->config['mailer.from'],
            'to' => $email,
            'subject' => $subject,
            'body' => $app->renderMustacheTemplate($template, $params),
        ]);
    }
}

<?php

namespace Tests;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\OpportunityAgentRelation;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\User;
use OpportunityAppealPhase\AppealReview\CorrectorEnvironment;
use OpportunityAppealPhase\AppealReview\Notifier;
use OpportunityAppealPhase\AppealReview\Service;
use OpportunityAppealPhase\Entities\RegistrationAppealReview;
use Symfony\Component\Mime\Email;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Mailer\TestTransport;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * PR5 (issue #15): notificações internas e e-mails de designação e correção.
 */
class AppealCorrectionNotificationsTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $app = App::i();
        $app->clearHooks('mailer.transport');
        $app->hook('mailer.transport', function (&$transport) {
            $transport = new TestTransport;
        });
        $app->config['mailer.from'] = 'test@mapasculturais.org';
        $_ENV['SEND_MAIL_OPPORTUNITY_APPEAL_PHASE'] = 'true';
        TestTransport::reset();
    }

    private function enableFlag(): void
    {
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'true';
    }

    private function countNotifications(User $user): int
    {
        return count(App::i()->repo(Notification::class)->findBy(['user' => $user]));
    }

    /**
     * @return array{corrector: User, manager: User, review: RegistrationAppealReview, registration: Registration}
     */
    private function createScenario(): array
    {
        $this->enableFlag();

        $admin = $this->userDirector->createUser('admin');
        $slot_owner = $this->userDirector->createUser();
        $corrector = $this->userDirector->createUser();
        $this->manager = $this->userDirector->createUser();
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->fillRequiredProperties()
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->config()
                    ->addSection('sec-1', 'Seção 1')
                    ->addCriterion('c-1', 'sec-1', 'Critério 1', 0, 10, 1)
                    ->done()
                ->save()
                ->addValuer('Comissão', 'Avaliador', $slot_owner->profile)
                    ->done()
                ->done()
            ->save();

        $opportunity = $this->opportunityBuilder->getInstance();
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $app = App::i();
        $app->disableAccessControl();

        $slot = new RegistrationEvaluation();
        $slot->registration = $registration;
        $slot->user = $slot_owner;
        $slot->setEvaluationData((object) ['c-1' => 4]);
        $slot->status = RegistrationEvaluation::STATUS_SENT;
        $slot->sentTimestamp = new DateTime();
        $slot->save(true);

        // gestor (group-admin) da oportunidade — destinatário da notificação de correção enviada
        $manager_relation = new OpportunityAgentRelation();
        $manager_relation->owner = $opportunity;
        $manager_relation->group = 'group-admin';
        $manager_relation->agent = $this->manager->profile;
        $manager_relation->hasControl = true;
        $manager_relation->save(true);

        $appeal_phase_class = $opportunity->getSpecializedClassName();
        /** @var Opportunity $appeal_phase */
        $appeal_phase = new $appeal_phase_class();
        $appeal_phase->parent = $opportunity;
        $appeal_phase->status = Opportunity::STATUS_APPEAL_PHASE;
        $appeal_phase->name = 'Recurso técnico';
        $appeal_phase->ownerEntity = $opportunity->ownerEntity;
        $appeal_phase->owner = $opportunity->owner;
        $appeal_phase->isDataCollection = true;
        $appeal_phase->isAppealPhase = true;
        $appeal_phase->registrationFrom = new DateTime('-1 day');
        $appeal_phase->registrationTo = new DateTime('+1 day');
        $appeal_phase->save(true);

        $opportunity->appealPhase = $appeal_phase;
        $opportunity->save(true);

        $app->enableAccessControl();

        return [
            'corrector' => $corrector,
            'manager' => $this->manager,
            'registration' => $registration,
            'slot' => $slot,
            'appealPhase' => $appeal_phase,
            'opportunity' => $opportunity,
        ];
    }

    private function createReview(array $scenario): RegistrationAppealReview
    {
        $app = App::i();
        $app->disableAccessControl();

        $review = new RegistrationAppealReview();
        $review->originalEvaluation = $scenario['slot'];
        $review->registration = $scenario['registration'];
        $review->appealPhase = $scenario['appealPhase'];
        $review->slotOwnerUser = $scenario['slot']->user;
        $review->correctorUser = $scenario['corrector'];
        $review->status = RegistrationAppealReview::STATUS_DESIGNATED;
        $review->correctionType = RegistrationAppealReview::CORRECTION_TYPE_OFFICIAL;
        $review->releasedScope = (object) ['criteria' => ['c-1'], 'showAppealOpinion' => false];
        $review->startsAt = new DateTime('-1 hour');
        $review->endsAt = new DateTime('+1 day');
        $review->originalValue = $scenario['slot']->getEvaluationData();
        $review->originalScore = (float) $scenario['slot']->result;
        $review->save(true);

        $app->enableAccessControl();

        return $review;
    }

    function testDesignatedCorrectorReceivesInternalNotification(): void
    {
        $scenario = $this->createScenario();
        TestTransport::reset();
        $before = $this->countNotifications($scenario['corrector']);

        $review = $this->createReview($scenario);

        $after = $this->countNotifications($scenario['corrector']);
        $this->assertSame($before + 1, $after, 'Corretor designado deveria receber exatamente 1 notificação interna');

        $last = App::i()->repo(Notification::class)->findOneBy(['user' => $scenario['corrector']], ['id' => 'DESC']);
        $this->assertStringContainsString('designado', $last->message);
        $this->assertStringContainsString($scenario['registration']->number, $last->message);
        $this->assertStringContainsString(CorrectorEnvironment::correctionUrl($review), $last->message);
        $this->assertNotNull($review->id);
    }

    function testDesignatedCorrectorReceivesEmail(): void
    {
        $scenario = $this->createScenario();
        TestTransport::reset();

        $review = $this->createReview($scenario);

        $this->assertGreaterThan(0, TestTransport::getMessagesCount(), 'Deveria disparar e-mail na designação');

        $found = false;
        foreach (TestTransport::getSentMessages() as $sent) {
            $original = $sent->getOriginalMessage();
            if (!$original instanceof Email) {
                continue;
            }
            foreach ($original->getTo() as $address) {
                if ($address->getAddress() === $scenario['corrector']->email) {
                    $this->assertStringContainsString('designado', $original->getSubject());
                    $this->assertStringContainsString($scenario['registration']->number, (string) $original->getHtmlBody());
                    $this->assertStringContainsString(CorrectorEnvironment::correctionUrl($review), (string) $original->getHtmlBody());
                    $found = true;
                }
            }
        }
        $this->assertTrue($found, 'E-mail de designação deveria ir para o e-mail do corretor');
    }

    function testManagerNotifiedWhenCorrectionSent(): void
    {
        $scenario = $this->createScenario();
        $review = $this->createReview($scenario);

        $manager_before = $this->countNotifications($scenario['manager']);
        $corrector_before = $this->countNotifications($scenario['corrector']);
        TestTransport::reset();

        $this->login($scenario['corrector']);
        (new Service())->applyCorrection($review, ['c-1' => 9]);

        $this->assertSame($manager_before + 1, $this->countNotifications($scenario['manager']),
            'Gestor (group-admin) deveria ser notificado do envio da correção');
        $this->assertSame($corrector_before + 1, $this->countNotifications($scenario['corrector']),
            'Corretor deveria receber confirmação do envio');

        $last = App::i()->repo(Notification::class)->findOneBy(['user' => $scenario['manager']], ['id' => 'DESC']);
        $this->assertStringContainsString('enviada pelo corretor', $last->message);
    }

    function testCorrectionSentEmailsToCorrectorAndManager(): void
    {
        $scenario = $this->createScenario();
        $review = $this->createReview($scenario);
        TestTransport::reset();

        $this->login($scenario['corrector']);
        (new Service())->applyCorrection($review, ['c-1' => 9]);

        $recipients = [];
        foreach (TestTransport::getSentMessages() as $sent) {
            $original = $sent->getOriginalMessage();
            if ($original instanceof Email) {
                foreach ($original->getTo() as $address) {
                    $recipients[] = $address->getAddress();
                }
            }
        }

        $this->assertContains($scenario['corrector']->email, $recipients, 'Corretor deveria receber e-mail de confirmação');
        $this->assertContains($scenario['manager']->email, $recipients, 'Gestor deveria receber e-mail de correção enviada');
    }

    function testNoNotificationWhenFlagDisabled(): void
    {
        $scenario = $this->createScenario();
        $_ENV['APPEAL_SCORE_CORRECTION'] = 'false';
        TestTransport::reset();
        $before = $this->countNotifications($scenario['corrector']);

        $this->createReview($scenario);

        $this->assertSame($before, $this->countNotifications($scenario['corrector']),
            'Flag desligada não deve gerar notificação de designação');
        $this->assertSame(0, TestTransport::getMessagesCount(),
            'Flag desligada não deve disparar e-mails');
    }

    function testTemplatesRenderWithExpectedContent(): void
    {
        $app = App::i();
        $params = [
            'siteName' => $app->siteName,
            'message' => 'mensagem de teste',
            'isDesignation' => true,
            'isCorrectionSent' => false,
            'opportunityName' => 'Edital Teste',
            'opportunityUrl' => 'https://example.org/opportunity',
            'phaseName' => 'Recurso',
            'phaseUrl' => 'https://example.org/recurso',
            'registrationNumber' => '1234',
            'registrationUrl' => 'https://example.org/inscricao',
            'deadline' => '05/09/2026 23:59',
        ];

        $designation = $app->renderMustacheTemplate('opportunityappealphase/correction-designation.html', $params);
        $this->assertStringContainsString('designado', $designation);
        $this->assertStringContainsString('Prazo para correção', $designation);
        $this->assertStringContainsString('1234', $designation);

        $params['isDesignation'] = false;
        $params['isCorrectionSent'] = true;
        $sent = $app->renderMustacheTemplate('opportunityappealphase/correction-sent.html', $params);
        $this->assertStringContainsString('foi aplicada e enviada', $sent);
        $this->assertStringContainsString('Edital Teste', $sent);
    }

    function testNotifierRespectsMailConfigDisabled(): void
    {
        $scenario = $this->createScenario();
        $_ENV['SEND_MAIL_OPPORTUNITY_APPEAL_PHASE'] = 'false';
        TestTransport::reset();
        $before = $this->countNotifications($scenario['corrector']);

        $this->createReview($scenario);

        // notificação interna sempre; e-mail só com mail habilitado
        $this->assertSame($before + 1, $this->countNotifications($scenario['corrector']));
        $this->assertSame(0, TestTransport::getMessagesCount());
    }
}

<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\User;
use Tests\Abstract\TestCase;
use Tests\Captcha\MockCaptcha;
use Tests\Mailer\TestTransport;
use Tests\Traits\AgentDirector;
use Tests\Traits\Faker;
use Tests\Traits\RequestFactory;
use Tests\Traits\SpaceDirector;
use Tests\Traits\UserDirector;

class CompliantSuggestionTest extends TestCase
{
    use RequestFactory,
        UserDirector,
        SpaceDirector,
        AgentDirector,
        Faker;

    protected array $defaultModuleConfig = [
        'compliant' => true,
        'suggestion' => true,
        'complaint.to' => [],
        'complaint.bcc' => [],
        'suggestion.to' => [],
        'suggestion.bcc' => null,
        'suggestion.sendToEntityAdmins' => false,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $app = App::i();

        TestTransport::reset();

        // Evita acumular hooks entre testes (App::reset não limpa hooks).
        $app->clearHooks('mailer.transport');
        $app->hook('mailer.transport', function (&$transport) {
            $transport = new TestTransport();
        });

        // Captcha de teste: arquivo local com success=true (sem Google / sem stream wrapper).
        MockCaptcha::register();

        $app->config['mailer.from'] = 'test@mapasculturais.org';

        // Isola config do módulo entre testes (App::reset não restaura config).
        foreach ($this->defaultModuleConfig as $key => $value) {
            $app->config['module.CompliantSuggestion'][$key] = $value;
        }
    }

    protected function tearDown(): void
    {
        TestTransport::reset();
        parent::tearDown();
    }

    /**
     * Persiste e-mail do usuário com AC desligado (guest não pode modify User).
     */
    protected function setUserEmail(User $user, string $email): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->email = $email;
        $user->save(true);
        $app->enableAccessControl();
    }

    /**
     * Persiste metadados de e-mail do agente com AC desligado.
     */
    protected function setAgentEmails(Agent $agent, ?string $emailPrivado = null, ?string $emailPublico = null): void
    {
        $app = App::i();
        $app->disableAccessControl();
        if ($emailPrivado !== null) {
            $agent->emailPrivado = $emailPrivado;
        }
        if ($emailPublico !== null) {
            $agent->emailPublico = $emailPublico;
        }
        $agent->save(true);
        $app->enableAccessControl();
    }

    protected function configureModule(array $config): void
    {
        $app = App::i();
        foreach ($config as $key => $value) {
            $app->config['module.CompliantSuggestion'][$key] = $value;
        }
    }

    protected function sendComplaint($entity, array $extra = []): void
    {
        $payload = array_merge([
            'entityId' => $entity->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'type' => 'Conteúdo ofensivo',
            'message' => $this->faker->text(200),
            'g-recaptcha-response' => 'valid-token',
        ], $extra);

        $request = $this->requestFactory->POST($entity->controllerId, 'sendComplaintMessage', [], $payload);
        $this->assertStatus200($request);
    }

    protected function sendSuggestion($entity, array $extra = []): void
    {
        $payload = array_merge([
            'entityId' => $entity->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'type' => 'Sugestão',
            'message' => $this->faker->text(200),
            'g-recaptcha-response' => 'valid-token',
        ], $extra);

        $request = $this->requestFactory->POST($entity->controllerId, 'sendSuggestionMessage', [], $payload);
        $this->assertStatus200($request);
    }

    protected function assertEmailSentTo(string $email, string $message = ''): void
    {
        $found = false;
        foreach (TestTransport::getSentMessages() as $sentMessage) {
            foreach ($sentMessage->getEnvelope()->getRecipients() as $recipient) {
                if ($recipient->getAddress() === $email) {
                    $found = true;
                    break 2;
                }
            }
        }
        $this->assertTrue($found, $message ?: "E-mail deveria ter sido enviado para {$email}");
    }

    protected function assertEmailNotSentTo(string $email, string $message = ''): void
    {
        foreach (TestTransport::getSentMessages() as $sentMessage) {
            foreach ($sentMessage->getEnvelope()->getRecipients() as $recipient) {
                $this->assertNotEquals($email, $recipient->getAddress(), $message ?: "E-mail não deveria ter sido enviado para {$email}");
            }
        }
    }

    // ===================== DENÚNCIA =====================

    function testComplaintSendsToSaasSuperAdminsByDefault()
    {
        $adminUser = $this->userDirector->createUser('saasSuperAdmin');
        $this->setUserEmail($adminUser, 'saasadmin@example.org');

        $spaceOwner = $this->userDirector->createUser();
        $space = $this->spaceDirector->createSpace($spaceOwner->profile, disable_access_control: true);

        $this->sendComplaint($space);

        $this->assertEmailSentTo('saasadmin@example.org');
        $this->assertEmailNotSentTo($spaceOwner->email);
    }

    function testComplaintSendsToConfiguredToAndSaasSuperAdmins()
    {
        $adminUser = $this->userDirector->createUser('saasSuperAdmin');
        $this->setUserEmail($adminUser, 'saasadmin@example.org');

        $spaceOwner = $this->userDirector->createUser();
        $space = $this->spaceDirector->createSpace($spaceOwner->profile, disable_access_control: true);

        $this->configureModule(['complaint.to' => ['denuncia@example.org']]);

        $this->sendComplaint($space);

        $this->assertEmailSentTo('saasadmin@example.org');
        $this->assertEmailSentTo('denuncia@example.org');
    }

    function testComplaintSendsBccToConfiguredList()
    {
        $adminUser = $this->userDirector->createUser('saasSuperAdmin');
        $this->setUserEmail($adminUser, 'saasadmin@example.org');

        $spaceOwner = $this->userDirector->createUser();
        $space = $this->spaceDirector->createSpace($spaceOwner->profile, disable_access_control: true);

        $this->configureModule(['complaint.bcc' => ['bcc@example.org']]);

        $this->sendComplaint($space);

        $this->assertEmailSentTo('saasadmin@example.org');
        $this->assertEmailSentTo('bcc@example.org');
    }

    function testComplaintSendsCopyToSender()
    {
        $adminUser = $this->userDirector->createUser('saasSuperAdmin');
        $this->setUserEmail($adminUser, 'saasadmin@example.org');

        $spaceOwner = $this->userDirector->createUser();
        $space = $this->spaceDirector->createSpace($spaceOwner->profile, disable_access_control: true);

        $senderEmail = 'sender@example.org';
        $this->sendComplaint($space, ['email' => $senderEmail, 'copy' => true]);

        $this->assertEmailSentTo('saasadmin@example.org');
        $this->assertEmailSentTo($senderEmail);
    }

    function testComplaintFailsWithoutRecaptcha()
    {
        $spaceOwner = $this->userDirector->createUser();
        $space = $this->spaceDirector->createSpace($spaceOwner->profile, disable_access_control: true);

        $request = $this->requestFactory->POST($space->controllerId, 'sendComplaintMessage', [], [
            'entityId' => $space->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'type' => 'Conteúdo ofensivo',
            'message' => $this->faker->text(200),
        ]);

        $this->assertStatus400($request);
        $this->assertSame(0, TestTransport::getMessagesCount(), 'Sem captcha, nenhum e-mail deve ser enviado');
    }

    // ===================== CONTATO/SUGESTÃO =====================

    function testSuggestionSendsToOwnerByDefault()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');
        $this->setAgentEmails($owner->profile, emailPrivado: 'owner-private@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
    }

    function testSuggestionSendsToConfiguredToAndOwner()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule(['suggestion.to' => ['sugestao@example.org']]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('sugestao@example.org');
    }

    function testSuggestionSendsBccToSystemAdminsByDefault()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->setUserEmail($admin, 'sysadmin@example.org');

        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('sysadmin@example.org');
    }

    function testSuggestionBccOffDoesNotSendToSystemAdmins()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->setUserEmail($admin, 'sysadmin@example.org');

        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule(['suggestion.bcc' => 'off']);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailNotSentTo('sysadmin@example.org');
    }

    function testSuggestionBccFixedList()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->setUserEmail($admin, 'sysadmin@example.org');

        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule(['suggestion.bcc' => ['bcc-fixed@example.org']]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('bcc-fixed@example.org');
        $this->assertEmailNotSentTo('sysadmin@example.org');
    }

    function testSuggestionSendToEntityAdminsSendsToOwnerAndAdminAgents()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $adminAgentUser = $this->userDirector->createUser();
        $this->setUserEmail($adminAgentUser, 'adminagent-user@example.org');
        $this->setAgentEmails($adminAgentUser->profile, emailPublico: 'adminagent-public@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $app = App::i();
        $app->disableAccessControl();
        $space->createAgentRelation($adminAgentUser->profile, Agent::AGENT_RELATION_ADMIN_GROUP, has_control: true, save: true, flush: true);
        $app->enableAccessControl();

        $this->configureModule(['suggestion.sendToEntityAdmins' => true]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('adminagent-user@example.org');
        $this->assertEmailSentTo('adminagent-public@example.org');
    }

    function testSuggestionSendToEntityAdminsDoesNotSendToSystemAdminsByDefault()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->setUserEmail($admin, 'sysadmin@example.org');

        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule(['suggestion.sendToEntityAdmins' => true]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailNotSentTo('sysadmin@example.org');
    }

    function testSuggestionSendToEntityAdminsWithFixedBcc()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule([
            'suggestion.sendToEntityAdmins' => true,
            'suggestion.bcc' => ['bcc@example.org'],
        ]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('bcc@example.org');
    }

    function testSuggestionSendToEntityAdminsWithConfiguredTo()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->configureModule([
            'suggestion.sendToEntityAdmins' => true,
            'suggestion.to' => ['extra@example.org'],
        ]);

        $this->sendSuggestion($space);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo('extra@example.org');
    }

    function testSuggestionCopyToSender()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $senderEmail = 'sender@example.org';
        $this->sendSuggestion($space, ['email' => $senderEmail, 'copy' => true]);

        $this->assertEmailSentTo('owner@example.org');
        $this->assertEmailSentTo($senderEmail);
    }

    function testSuggestionFailsForUnderageAuthenticatedUser()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $underage = $this->userDirector->createUser();
        $app = App::i();
        $app->disableAccessControl();
        $underage->profile->dataDeNascimento = date('Y-m-d', strtotime('-16 years'));
        $underage->profile->save(true);
        $app->enableAccessControl();

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $this->login($underage);

        $request = $this->requestFactory->POST($space->controllerId, 'sendSuggestionMessage', [], [
            'entityId' => $space->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'type' => 'Sugestão',
            'message' => $this->faker->text(200),
            'g-recaptcha-response' => 'valid-token',
        ]);

        $this->assertStatus400($request);
        $this->assertSame(0, TestTransport::getMessagesCount(), 'Menor de idade autenticado não deve enviar e-mail de contato');
    }

    function testSuggestionFailsWithoutRecaptcha()
    {
        $owner = $this->userDirector->createUser();
        $this->setUserEmail($owner, 'owner@example.org');

        $space = $this->spaceDirector->createSpace($owner->profile, disable_access_control: true);

        $request = $this->requestFactory->POST($space->controllerId, 'sendSuggestionMessage', [], [
            'entityId' => $space->id,
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'type' => 'Sugestão',
            'message' => $this->faker->text(200),
        ]);

        $this->assertStatus400($request);
        $this->assertSame(0, TestTransport::getMessagesCount(), 'Sem captcha, nenhum e-mail deve ser enviado');
    }
}

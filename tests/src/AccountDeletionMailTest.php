<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use Symfony\Component\Mime\Email;
use Tests\Abstract\TestCase;
use Tests\Mailer\TestTransport;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;
use UserManagement\Module as UserManagementModule;

/**
 * Backend da exclusão de conta (LGPD): hooks User::delete, e-mails de confirmação
 * e renderização da mensagem da solicitação sem tags HTML escapadas.
 */
class AccountDeletionMailTest extends TestCase
{
    use RequestFactory;
    use UserDirector;

    protected function setUp(): void
    {
        parent::setUp();

        $app = App::i();

        TestTransport::reset();
        $app->clearHooks('mailer.transport');
        $app->hook('mailer.transport', function (&$transport) {
            $transport = new TestTransport();
        });

        $app->config['mailer.from'] = 'test@mapasculturais.org';
    }

    protected function tearDown(): void
    {
        TestTransport::reset();
        parent::tearDown();
    }

    protected function setUserEmail(User $user, string $email): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->email = $email;
        $user->save(true);
        $app->enableAccessControl();
    }

    protected function getLastEmailHtml(): string
    {
        $sent = TestTransport::getLastMessage();
        $this->assertNotNull($sent, 'Deveria haver pelo menos um e-mail enviado');

        $original = $sent->getOriginalMessage();
        $this->assertInstanceOf(Email::class, $original);

        return (string) $original->getHtmlBody();
    }

    function testRequestAccountDeletionMessageRendersLineBreaksAsHtml()
    {
        $app = App::i();

        $message = "Prezados,\n\nSolicito a exclusão.\n\nAtenciosamente";
        $params = [
            'siteName' => 'Mapas Test',
            'baseUrl' => $app->getBaseUrl(),
            'userName' => 'Teste',
            'userEmail' => 'teste@example.org',
            'userId' => 99,
            'message' => nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'), false),
        ];

        $rendered = $app->renderMailerTemplate('request_account_deletion', $params);

        $this->assertStringContainsString('Solicito a exclusão.', $rendered['body']);
        $this->assertMatchesRegularExpression('/Solicito a exclusão\.<br\s*\/?>/', $rendered['body']);
        $this->assertStringNotContainsString('&lt;br', $rendered['body'], 'Tags <br> não devem aparecer escapadas no HTML do e-mail');
    }

    function testRequestAccountDeletionEndpointSendsMailWithHtmlBreaks()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->setUserEmail($admin, 'admin-lgpd@example.org');

        $user = $this->userDirector->createUser();
        $this->setUserEmail($user, 'solicitante@example.org');
        $this->login($user);

        $payload = [
            'message' => "Linha um\nLinha dois",
            'sendCopy' => false,
        ];

        $request = $this->requestFactory->POST('user', 'requestAccountDeletion', [], $payload);
        $this->assertStatus200($request);

        $this->assertEmailSentTo('admin-lgpd@example.org');
        $html = $this->getLastEmailHtml();
        $this->assertStringContainsString('Linha um', $html);
        $this->assertMatchesRegularExpression('/Linha um<br\s*\/?>/', $html);
        $this->assertStringNotContainsString('&lt;br', $html);
    }

    function testUserDeleteFiresDeleteAfterHook()
    {
        $app = App::i();
        $called = 0;

        $app->hook('entity(User).delete:after', function () use (&$called) {
            $called++;
        });

        $admin = $this->userDirector->createUser('admin');
        $user = $this->userDirector->createUser();
        // Admin exclui outro usuário — não pode ser o próprio logado (status trash
        // mid-save faz o AuthProvider rejeitar "usuário inativo").
        $this->login($admin);

        $user->delete(true);

        $this->assertSame(1, $called, 'User::delete deve disparar entity(User).delete:after');
        $this->assertSame(User::STATUS_TRASH, $user->status);
    }

    function testPartialDeletionSendsConfirmationMailToUser()
    {
        $admin = $this->userDirector->createUser('admin');
        $user = $this->userDirector->createUser();
        $this->setUserEmail($user, 'parcial@example.org');
        $this->login($admin);

        TestTransport::reset();
        $user->delete(true);

        $this->assertSame(1, TestTransport::getMessagesCount(), 'Exclusão parcial deve enviar 1 e-mail de confirmação');
        $this->assertEmailSentTo('parcial@example.org');

        $html = $this->getLastEmailHtml();
        $this->assertStringContainsString('excluído de forma parcial', $html);
    }

    function testPermanentDeletionSendsConfirmationMailToUser()
    {
        $admin = $this->userDirector->createUser('admin');
        $user = $this->userDirector->createUser();
        $this->setUserEmail($user, 'permanente@example.org');

        // destroy exige permissão remove/destroy — admin sobre outro usuário
        $this->login($admin);

        TestTransport::reset();
        $user->destroy(true);

        $this->assertGreaterThanOrEqual(1, TestTransport::getMessagesCount(), 'Exclusão permanente deve enviar e-mail de confirmação');
        $this->assertEmailSentTo('permanente@example.org');

        $html = $this->getLastEmailHtml();
        $this->assertStringContainsString('exclusão permanente', $html);
    }

    function testSendAccountDeletionConfirmationMailSkipsInvalidEmail()
    {
        $user = $this->userDirector->createUser();
        $app = App::i();
        $app->disableAccessControl();
        $user->email = 'nao-e-email';
        $user->save(true);
        $app->enableAccessControl();

        TestTransport::reset();
        UserManagementModule::sendAccountDeletionConfirmationMail($user, 'account_deletion_partial');

        $this->assertSame(0, TestTransport::getMessagesCount(), 'E-mail inválido não deve disparar envio');
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
}

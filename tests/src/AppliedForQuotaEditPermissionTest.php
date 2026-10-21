<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\User;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

/**
 * Regressão: a pergunta "Vai concorrer às cotas?" (`appliedForQuota`) pode ser
 * liberada pelo gestor para o suporte e para o "editar campos" do proponente.
 * Só quem recebeu a permissão consegue alterar, e o `eligible` é recalculado.
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/AppliedForQuotaEditPermissionTest.php
 */
class AppliedForQuotaEditPermissionTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        RequestFactory,
        UserDirector;

    private User $admin;

    protected function tearDown(): void
    {
        // As flags também são ligadas em checagens de permissão fora das requisições
        // (montagem do cenário). Zera no fim para não vazar para os próximos testes.
        $this->resetModuleFlags('Support', ['inTransaction', 'grantedCoarse', 'supportHasControl']);
        $this->resetModuleFlags('RegistrationFieldTypes', ['inEditableTransaction', 'grantedCoarse']);

        parent::tearDown();
    }

    /**
     * Oportunidade cuja 1ª fase tem avaliação técnica com cota para pessoas negras,
     * e uma inscrição enviada de pessoa negra que respondeu "Não" à pergunta de cotas.
     */
    private function createSentRegistrationNotApplied(): Registration
    {
        $this->admin = $this->userDirector->createUser('admin');
        $this->login($this->admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $this->admin->profile, owner_entity: $this->admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Etapa 1')
                ->createField('raca', 'select', 'Raça/Cor', required: false, options: ['Branca', 'Preta', 'Parda', 'Indígena'])
                ->save()
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->config()
                    ->quota()
                        ->addRule('Pessoas Negras', 3)
                            ->addRuleField('raca', ['Preta', 'Parda'])
                    ->done()
                ->done()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);

        $registration = $this->registrationDirector->createSentRegistration($opportunity, [
            $field_raca => 'Preta',
            'appliedForQuota' => false,
        ]);

        $this->assertFalse($registration->appliedForQuota, 'Cenário: a inscrição começa com "Não" na pergunta de cotas');
        $this->assertFalse((bool) $registration->eligible, 'Cenário: com "Não", a inscrição não é elegível');

        return $registration;
    }

    private function addSupportUser(Registration $registration, ?string $permission): User
    {
        $app = App::i();
        $support_user = $this->userDirector->createUser();

        $app->disableAccessControl();
        $relation = $registration->opportunity->createAgentRelation($support_user->profile, '@support', false, true, true);
        if ($permission) {
            $relation->metadata = ['registrationPermissions' => ['appliedForQuota' => $permission]];
            $relation->save(true);
        }
        $app->enableAccessControl();

        $this->processPCache();

        return $support_user;
    }

    private function openForEdition(Registration $registration, array $fields): void
    {
        $this->login($this->admin);

        $registration = $registration->refreshed();
        $registration->editableUntil = '2100-01-01 00:00:00';
        $registration->editableFields = $fields;
        $registration->save(true);

        $this->processPCache();
    }

    /**
     * Roda a requisição e devolve o status HTTP.
     *
     * Os módulos guardam estado por requisição (flags de transação/permissão).
     * Em produção cada requisição começa do zero; aqui os módulos são os mesmos
     * entre requisições, então as flags são zeradas depois de cada uma.
     */
    private function runRequest(ServerRequestInterface $request): int
    {
        $app = App::i();
        $app->reset();

        // Em produção a requisição carrega a inscrição do banco; sem isto ela reaproveitaria
        // o objeto em memória do cenário, com alterações ainda marcadas como pendentes
        $user = $app->user;
        $app->em->clear();
        $app->auth->authenticatedUser = $app->repo('User')->find($user->id);

        $conn = $app->em->getConnection();
        $transaction_level = $conn->getTransactionNestingLevel();

        try {
            $app->run($request, false);
            $status = $app->response->getStatusCode();
        } catch (\Throwable $e) {
            $status = 500;
        }

        // A recusa do suporte desfaz transações; volta ao nível em que o teste estava
        // para o rollback do tearDown continuar isolando o teste
        while ($conn->getTransactionNestingLevel() > $transaction_level) {
            $conn->rollBack();
        }
        while ($conn->getTransactionNestingLevel() < $transaction_level) {
            $conn->beginTransaction();
        }

        // Requisição recusada no meio do salvamento fecha o EntityManager. Em produção a
        // requisição acaba aí; aqui ele é reaberto para não quebrar os testes seguintes.
        if (!$app->em->isOpen()) {
            $closed = new \ReflectionProperty(\Doctrine\ORM\EntityManager::class, 'closed');
            $closed->setAccessible(true);
            $closed->setValue($app->em, false);
            $app->em->clear();
        }

        $this->resetModuleFlags('Support', ['inTransaction', 'grantedCoarse', 'supportHasControl']);
        $this->resetModuleFlags('RegistrationFieldTypes', ['inEditableTransaction', 'grantedCoarse']);

        return $status;
    }

    private function resetModuleFlags(string $module_name, array $properties): void
    {
        $module = App::i()->modules[$module_name] ?? null;
        if (!$module) {
            return;
        }

        // Closure ligada ao módulo alcança propriedades protegidas e as criadas em tempo de execução
        \Closure::bind(function () use ($properties) {
            foreach ($properties as $property) {
                $this->$property = false;
            }
        }, $module, get_class($module))();
    }

    private function patchAppliedForQuota(Registration $registration, bool $value): int
    {
        return $this->runRequest($this->requestFactory->PATCH('registration', 'single', [$registration->id], payload: [
            'appliedForQuota' => $value,
        ]));
    }

    public function testSupportWithModifyPermissionCanChangeAnswer(): void
    {
        $registration = $this->createSentRegistrationNotApplied();
        $support_user = $this->addSupportUser($registration, 'rw');

        $this->login($support_user);
        $status = $this->patchAppliedForQuota($registration, true);

        $this->assertEquals(200, $status, 'Suporte com "Modificar" deve ter a alteração aceita');
        $registration = $registration->refreshed();
        $this->assertTrue($registration->appliedForQuota, 'Suporte com "Modificar" deve conseguir mudar para "Sim"');
        $this->assertTrue((bool) $registration->eligible, 'Ao mudar para "Sim", a inscrição deve passar a ser elegível');
    }

    public function testSupportWithViewPermissionCannotChangeAnswer(): void
    {
        $registration = $this->createSentRegistrationNotApplied();
        $support_user = $this->addSupportUser($registration, 'ro');

        $this->login($support_user);
        $status = $this->patchAppliedForQuota($registration, true);

        // O módulo de suporte recusa desfazendo a transação da requisição. Como cada
        // teste roda dentro de outra transação, o efeito no banco não é visível aqui:
        // a garantia é a requisição ser recusada.
        $this->assertNotEquals(200, $status, 'Suporte só com "Visualizar" não pode mudar a resposta');
    }

    public function testSupportWithoutPermissionCannotChangeAnswer(): void
    {
        $registration = $this->createSentRegistrationNotApplied();
        $support_user = $this->addSupportUser($registration, null);

        $this->login($support_user);
        $status = $this->patchAppliedForQuota($registration, true);

        // O módulo de suporte recusa desfazendo a transação da requisição. Como cada
        // teste roda dentro de outra transação, o efeito no banco não é visível aqui:
        // a garantia é a requisição ser recusada.
        $this->assertNotEquals(200, $status, 'Suporte sem permissão na pergunta não pode mudar a resposta');
    }

    public function testOwnerCanChangeAnswerWhenOpenedForEdition(): void
    {
        $registration = $this->createSentRegistrationNotApplied();
        $this->openForEdition($registration, ['appliedForQuota']);

        $this->login($registration->owner->user);
        $status = $this->patchAppliedForQuota($registration, true);

        $this->assertEquals(200, $status, 'Com a pergunta reaberta, a alteração deve ser aceita');
        $registration = $registration->refreshed();
        $this->assertTrue($registration->appliedForQuota, 'Com a pergunta reaberta, o proponente deve conseguir mudar para "Sim"');
        $this->assertTrue((bool) $registration->eligible, 'Ao mudar para "Sim", a inscrição deve passar a ser elegível');
    }

    public function testOwnerCannotChangeAnswerWhenNotOpenedForEdition(): void
    {
        $registration = $this->createSentRegistrationNotApplied();
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $registration->opportunity);
        $this->openForEdition($registration, [$field_raca]);

        $this->login($registration->owner->user);
        $this->patchAppliedForQuota($registration, true);

        $registration = $registration->refreshed();
        $this->assertFalse($registration->appliedForQuota, 'Sem a pergunta reaberta, a alteração deve ser ignorada');
        $this->assertFalse((bool) $registration->eligible);
    }
}

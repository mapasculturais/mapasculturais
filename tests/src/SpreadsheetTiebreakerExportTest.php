<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use ReflectionMethod;
use ReflectionProperty;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão: a coluna "Critérios de desempate" (`tiebreaker`) saía vazia na
 * planilha de inscrições porque o array associativo ({nome} => {valor}) montado
 * por EvaluationMethodTechnical\Quotas era zerado por replaceArraysWithNull().
 *
 * A célula deve sair como texto no formato "{nome}: {valor}", um critério por
 * linha, espelhando a exibição da tabela de inscrições da tela.
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/SpreadsheetTiebreakerExportTest.php
 */
class SpreadsheetTiebreakerExportTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private const JOB_SLUG = 'registrations-spreadsheets';

    private function createTechnicalOpportunityWithTiebreaker(array $tiebreaker_config): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(10)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->done();

        $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(0)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->config()
                    ->addSection('sec-1', 'Seção 1')
                    ->addCriterion('c-1', 'sec-1', 'Critério 1', 0, 10, 1)
                    ->addCriterion('c-2', 'sec-1', 'Critério 2', 0, 10, 1)
                    ->setTiebreakerCriteriaConfiguration($tiebreaker_config)
                    ->done()
                ->save()
                ->addValuers(2, 'Comissão')
                ->done()
            ->save();

        return $this->opportunityBuilder->getInstance()->refreshed();
    }

    private function evaluateRegistrationWithTwoValuers(
        Opportunity $opportunity,
        Registration $registration,
        float $valuer1_criterion1_score,
        float $valuer1_criterion2_score,
        float $valuer2_criterion1_score,
        float $valuer2_criterion2_score
    ): void {
        $app = App::i();
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();
        $valuer_user_ids = array_keys($registration->valuers);

        $scores = [
            [$valuer1_criterion1_score, $valuer1_criterion2_score],
            [$valuer2_criterion1_score, $valuer2_criterion2_score],
        ];

        foreach ($valuer_user_ids as $i => $user_id) {
            $user = $app->repo('User')->find($user_id);
            $evaluation = new RegistrationEvaluation();
            $evaluation->registration = $registration;
            $evaluation->user = $user;
            $evaluation->setEvaluationData((object) [
                'c-1' => $scores[$i][0],
                'c-2' => $scores[$i][1],
                'obs' => 'teste',
            ]);
            $evaluation->save();
            $app->disableAccessControl();
            $evaluation->send();
            $app->enableAccessControl();
        }
    }

    private function setSentTimestamp(Registration $registration, string $timestamp): void
    {
        $app = App::i();
        $app->em->getConnection()->update(
            'registration',
            ['sent_timestamp' => $timestamp],
            ['id' => $registration->id]
        );
    }

    private function createJob(Opportunity $opportunity, string $select)
    {
        $app = App::i();

        return $app->enqueueOrReplaceJob(self::JOB_SLUG, [
            'owner' => $opportunity,
            'authenticatedUser' => $app->user,
            'extension' => 'csv',
            'entityClassName' => Registration::class,
            'query' => [
                '@select' => $select,
                '@order' => '@quota',
                '@opportunity' => $opportunity->id,
            ],
        ]);
    }

    private function invoke(string $method_name, ...$arguments)
    {
        $job_type = App::i()->getRegisteredJobType(self::JOB_SLUG);

        $method = new ReflectionMethod($job_type, $method_name);
        $method->setAccessible(true);

        return $method->invoke($job_type, ...$arguments);
    }

    private function getBatchRows(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);

        $page = new ReflectionProperty(App::i()->getRegisteredJobType(self::JOB_SLUG), 'page');
        $page->setAccessible(true);
        $page->setValue(App::i()->getRegisteredJobType(self::JOB_SLUG), 1);

        return $this->invoke('_getBatch', $job);
    }

    public function testTiebreakerIsExportedAsTableText(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithTiebreaker([
            (object) [
                'id' => 1,
                'name' => 'critério 1',
                'criterionType' => 'criterion',
                'preferences' => 'c-1',
            ],
            (object) [
                'id' => 2,
                'name' => 'critério 2',
                'criterionType' => 'submissionDate',
                'preferences' => 'smallest',
            ],
        ]);

        $registration_a = $this->registrationDirector->createSentRegistration($opportunity, data: []);
        $registration_b = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $this->setSentTimestamp($registration_a, '2024-01-10 10:00:00');
        $this->setSentTimestamp($registration_b, '2024-01-20 10:00:00');

        // Score e critério c-1 empatados: o desempate acontece na segunda regra
        // (submissionDate) e as duas regras são registradas no array de desempate.
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_a, 5, 5, 5, 5);
        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration_b, 5, 5, 5, 5);

        $rows = $this->getBatchRows($opportunity, 'number,tiebreaker,quotas');
        $this->assertNotEmpty($rows, 'A exportação precisa retornar as inscrições');

        $by_number = [];
        foreach ($rows as $row) {
            $by_number[$row['number']] = $row;
        }

        $this->assertArrayHasKey($registration_a->number, $by_number, 'Linha da inscrição A presente');
        $this->assertArrayHasKey($registration_b->number, $by_number, 'Linha da inscrição B presente');

        $expected = [
            $registration_a->number => "Critério 1: 5.00\nsubmissionDate: 10/01/2024 10:00:00",
            $registration_b->number => "Critério 1: 5.00\nsubmissionDate: 20/01/2024 10:00:00",
        ];

        foreach ($expected as $number => $expected_tiebreaker) {
            $tiebreaker = $by_number[$number]['tiebreaker'] ?? null;

            $this->assertIsString($tiebreaker, 'Critérios de desempate devem ser exportados como string, não array');
            $this->assertSame(
                $expected_tiebreaker,
                $tiebreaker,
                'Critérios de desempate no formato "{nome}: {valor}", um por linha'
            );

            $this->assertIsString(
                $by_number[$number]['quotas'] ?? null,
                'Cotas continuam exportadas como string (guarda de regressão)'
            );
        }
    }

    public function testRowWithoutTiebreakerStaysEmpty(): void
    {
        $opportunity = $this->createTechnicalOpportunityWithTiebreaker([
            (object) [
                'id' => 1,
                'name' => 'critério 1',
                'criterionType' => 'criterion',
                'preferences' => 'c-1',
            ],
        ]);

        // Uma única inscrição enviada: o usort do cálculo de desempate não compara
        // nada, logo nenhum critério é registrado e a célula fica vazia.
        $registration = $this->registrationDirector->createSentRegistration($opportunity, data: []);

        $this->evaluateRegistrationWithTwoValuers($opportunity, $registration, 5, 5, 5, 5);

        $rows = $this->getBatchRows($opportunity, 'number,tiebreaker,quotas');
        $this->assertNotEmpty($rows, 'A exportação precisa retornar a inscrição');

        $by_number = [];
        foreach ($rows as $row) {
            $by_number[$row['number']] = $row;
        }

        $this->assertArrayHasKey($registration->number, $by_number, 'Linha da inscrição presente');

        $tiebreaker = $by_number[$registration->number]['tiebreaker'] ?? null;
        $this->assertTrue(
            $tiebreaker === null || $tiebreaker === '',
            'Sem critérios calculados, a célula de desempate deve ficar vazia'
        );
    }
}

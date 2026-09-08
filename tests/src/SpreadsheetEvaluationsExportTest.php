<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
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

class SpreadsheetEvaluationsExportTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    private const JOB_SLUG = 'technical-spreadsheets';
    private const FIELD_IDENTIFIER = 'campo_teste';
    private const FIELD_VALUE = 'Valor de teste';

    private $registration;

    private function createOpportunityWithEvaluationPhase(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->createStep('Etapa 1')
                ->createField(self::FIELD_IDENTIFIER, 'text', 'Campo de Teste')
                ->save()
                ->done();

        $evaluation_phase_builder = $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->config()
                    ->addSection('sec1', 'Seção 1')
                    ->addCriterion('cri1', 'sec1', 'Critério 1', 0, 10, 1)
                    ->done()
                ->save()
                ->addValuers(1, 'Comissão');

        $opportunity = $evaluation_phase_builder->done()->getInstance();

        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => self::FIELD_VALUE]
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();
        $this->registration = $registration;

        foreach ($this->getDistributedValuerNames($opportunity, $registration) as $valuer_name) {
            $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
                ->evaluation($registration)
                    ->setCriterionScore('cri1', 5)
                    ->setViabilityValid()
                    ->save()
                    ->send()
                    ->done();
        }

        return $opportunity->refreshed();
    }

    private function getDistributedValuerNames(Opportunity $opportunity, $registration): array
    {
        $valuer_user_ids = array_keys($registration->valuers);
        $valuer_names = [];

        $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão'] ?? [];
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                $valuer_names[] = $relation->agent->name;
            }
        }

        return $valuer_names;
    }

    private function createJob(Opportunity $opportunity, string $select)
    {
        $app = App::i();

        return $app->enqueueOrReplaceJob(self::JOB_SLUG, [
            'owner' => $opportunity,
            'authenticatedUser' => $app->user,
            'extension' => 'csv',
            'entityClassName' => RegistrationEvaluation::class,
            'query' => ['@select' => $select],
        ]);
    }

    private function invoke(string $method_name, ...$arguments)
    {
        $job_type = App::i()->getRegisteredJobType(self::JOB_SLUG);

        $method = new ReflectionMethod($job_type, $method_name);
        $method->setAccessible(true);

        return $method->invoke($job_type, ...$arguments);
    }

    private function getRegistrationSelect(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);

        return $this->invoke('splitSelect', $this->invoke('getRegistrationSelect', $job));
    }

    private function getFirstRow(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);

        $page = new ReflectionProperty(App::i()->getRegisteredJobType(self::JOB_SLUG), 'page');
        $page->setAccessible(true);
        $page->setValue(App::i()->getRegisteredJobType(self::JOB_SLUG), 1);

        $batch = $this->invoke('_getBatch', $job);

        $this->assertNotEmpty($batch, 'O cenário precisa produzir ao menos uma avaliação exportável');

        return $batch[0];
    }

    private function getSubHeader(Opportunity $opportunity, string $select): array
    {
        $job = $this->createJob($opportunity, $select);
        $header = App::i()->getRegisteredJobType(self::JOB_SLUG)->getHeader($job);

        return $header[1];
    }

    public function testRequiredPropertiesRemainWhenTheSelectionOmitsThem(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $properties = $this->getRegistrationSelect($opportunity, 'number');

        foreach (['id', 'number', 'status', 'projectName', 'consolidatedResult', 'agentsData', 'owner.{name}'] as $required) {
            $this->assertContains($required, $properties, "A propriedade {$required} precisa ser buscada mesmo fora da seleção");
        }
    }

    public function testSelectedRegistrationFieldIsRequested(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();
        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);

        $properties = $this->getRegistrationSelect($opportunity, "number,{$field_name}");

        $this->assertContains($field_name, $properties, 'O campo de formulário selecionado precisa ser buscado na API');
    }

    public function testEvaluationPropertiesAreNotRequestedFromTheRegistration(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $properties = $this->getRegistrationSelect(
            $opportunity,
            'number,committeeSequentialNumber,valuerUserId,valuerAgentId,user,result,evaluationData'
        );

        foreach (['committeeSequentialNumber', 'valuerUserId', 'valuerAgentId', 'user', 'result', 'evaluationData'] as $property) {
            $this->assertNotContains($property, $properties, "A propriedade {$property} não é da inscrição e não pode ir para a consulta");
        }
    }

    public function testSelectedRegistrationFieldIsExportedWithItsValue(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();
        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);

        $select = "number,{$field_name},user,result,status,evaluationData";

        $this->assertArrayHasKey($field_name, $this->getSubHeader($opportunity, $select), 'O campo selecionado precisa virar coluna');
        $this->assertSame(self::FIELD_VALUE, $this->getFirstRow($opportunity, $select)[$field_name] ?? null, 'A coluna do campo selecionado precisa trazer o valor da inscrição');
    }

    public function testUnselectedPropertyDoesNotBecomeColumn(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $sub_header = $this->getSubHeader($opportunity, 'number,user,result,status,evaluationData');

        $this->assertArrayNotHasKey('projectName', $sub_header, 'Propriedade fora da seleção não pode virar coluna');
        $this->assertArrayNotHasKey('proponentType', $sub_header, 'Propriedade fora da seleção não pode virar coluna');
        $this->assertArrayHasKey('number', $sub_header);
    }

    public function testRegistrationColumnsWithSpecialTreatmentKeepTheirValues(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $select = 'number,owner.{name},coletivo,status,user,result,evaluationData';
        $row = $this->getFirstRow($opportunity, $select);

        $this->assertSame($this->registration->owner->name, $row['name'] ?? null, 'A coluna do agente responsável vem do dono da inscrição');
        $this->assertSame($this->invoke('statusName', $this->registration->status), $row['status'] ?? null, 'O status da inscrição vem traduzido');
        $this->assertSame('', $row['coletivo'] ?? null, 'Sem agente coletivo, a coluna existe e vem vazia');
    }

    public function testHeaderFollowsTheOrderOfTheSelectedColumns(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();
        $field_name = $this->opportunityBuilder->getFieldName(self::FIELD_IDENTIFIER);

        $columns = array_keys($this->getSubHeader($opportunity, "number,status,{$field_name},result,user,evaluationData"));

        $this->assertSame(
            ['number', 'status', $field_name, 'result', 'user'],
            array_slice($columns, 0, 5),
            'O cabeçalho precisa seguir a ordem em que as colunas foram selecionadas'
        );
    }

    public function testDateColumnsAreExportedAsReadableText(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $row = $this->getFirstRow($opportunity, 'number,createTimestamp,sentTimestamp,user,result,status,evaluationData');

        $this->assertMatchesRegularExpression('#^\d{2}/\d{2}/\d{4} \d{2}:\d{2}:\d{2}$#', $row['createTimestamp'] ?? '', 'A data de criação precisa sair legível');
        $this->assertMatchesRegularExpression('#^\d{2}/\d{2}/\d{4} \d{2}:\d{2}:\d{2}$#', $row['sentTimestamp'] ?? '', 'A data de envio precisa sair legível');
    }

    public function testColumnsWithoutRegistrationLabelAreNamedInTheHeader(): void
    {
        $opportunity = $this->createOpportunityWithEvaluationPhase();

        $sub_header = $this->getSubHeader($opportunity, 'number,coletivo,user,result,status,evaluationData');

        $this->assertSame('Agente coletivo', $sub_header['coletivo'] ?? null, 'A coluna do agente coletivo precisa de rótulo próprio');
    }

    public function testSplitSelectKeepsBracedGroupsTogether(): void
    {
        $this->assertSame(
            ['number', 'owner.{name,id}', 'status'],
            $this->invoke('splitSelect', 'number,owner.{name,id},status')
        );

        $this->assertSame([], $this->invoke('splitSelect', ''));
    }
}

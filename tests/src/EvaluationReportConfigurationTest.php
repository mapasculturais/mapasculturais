<?php

namespace Tests;

use Tests\Traits\Faker;
use Tests\Traits\UserDirector;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;

class EvaluationReportConfigurationTest extends Abstract\TestCase
{
    use Faker,
        UserDirector,
        OpportunityBuilder;

    function testEvaluationReportIncludesRangeColumnWhenOpportunityHasRanges()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setRanges(ranges: [[
                'label' => 'Linha A',
                'limit' => 10,
                'value' => 0
            ]], save: false)
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->getInstance();

        $cfg = $opportunity->getEvaluationMethod()->getReportConfiguration($opportunity);

        $this->assertArrayHasKey('range', $cfg['registration']->columns, 'Garantindo que o relatório de avaliações inclui a coluna Faixa/Linha');
        $this->assertEquals('Faixa/Linha', $cfg['registration']->columns['range']->label);
    }

    function testEvaluationReportDoesNotIncludeRangeColumnWhenOpportunityHasNoRanges()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->save()
                ->done()
            ->getInstance();

        $cfg = $opportunity->getEvaluationMethod()->getReportConfiguration($opportunity);

        $this->assertArrayNotHasKey('range', $cfg['registration']->columns, 'Garantindo que o relatório não adiciona Faixa/Linha quando a oportunidade não usa faixas');
    }
}

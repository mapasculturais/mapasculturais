<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Regressão: a pergunta "Vai concorrer às cotas?" (`appliedForQuota`) começa sem resposta
 * e é obrigatória no envio quando habilitada na oportunidade (`enableQuotasQuestion`).
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/AppliedForQuotaRequiredTest.php
 */
class AppliedForQuotaRequiredTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    private function createOpportunity(bool $enable_quotas_question): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->getInstance();

        $opportunity->enableQuotasQuestion = $enable_quotas_question;
        $opportunity->save(true);

        return $opportunity->refreshed();
    }

    public function testAppliedForQuotaStartsWithoutAnswer(): void
    {
        $opportunity = $this->createOpportunity(true);
        [$registration] = $this->registrationDirector->createDraftRegistrations($opportunity, 1);

        $this->assertNull($registration->appliedForQuota, 'Sem resposta, appliedForQuota deve ser null (não "Não")');
    }

    public function testRequiredWhenQuestionIsEnabled(): void
    {
        $opportunity = $this->createOpportunity(true);
        [$registration] = $this->registrationDirector->createDraftRegistrations($opportunity, 1);

        $this->assertArrayHasKey('appliedForQuota', $registration->getSendValidationErrors(), 'Sem resposta, o envio deve ser bloqueado');

        $registration->appliedForQuota = false;
        $this->assertArrayNotHasKey('appliedForQuota', $registration->getSendValidationErrors(), 'Responder "Não" deve permitir o envio');

        $registration->appliedForQuota = true;
        $this->assertArrayNotHasKey('appliedForQuota', $registration->getSendValidationErrors(), 'Responder "Sim" deve permitir o envio');
    }

    public function testNotRequiredWhenQuestionIsDisabled(): void
    {
        $opportunity = $this->createOpportunity(false);
        [$registration] = $this->registrationDirector->createDraftRegistrations($opportunity, 1);

        $this->assertArrayNotHasKey('appliedForQuota', $registration->getSendValidationErrors(), 'Com a pergunta desligada, não deve ser obrigatória');
    }
}

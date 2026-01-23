<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class EvaluationConsolidationTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testSimpleEvaluationConsolidationResult()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setAutoApplicationAllowed(true)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->addValuers(2, 'Comissão');
        
        $opportunity = $evaluation_phase_builder
            ->done()
            ->getInstance();

        // Criar inscrição
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: []
        );

        // Redistribuir inscrições para os avaliadores
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration = $registration->refreshed();

        $valuers = $registration->valuers;

        // Obter os nomes dos avaliadores
        $valuer_user_ids = array_keys($valuers);
        $valuer_names = [];
        
        $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão'] ?? [];
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                $valuer_names[] = $relation->agent->name;
            }
        }

        // Verificar que o consolidatedResult não está consolidado (ainda não há avaliações enviadas)
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $registration = $registration->refreshed();
        
        $this->assertEmpty(
            $registration->consolidatedResult,
            'Certificando que o resultado consolidado não está definido enquanto não há avaliações enviadas'
        );

        // Primeiro avaliador cria avaliação mas não envia (STATUS_DRAFT)
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names[0])
            ->evaluation($registration)
                ->setSelected('Avaliação positiva')
                ->save()
                ->done();

        // Verificar que o consolidatedResult ainda não está consolidado (avaliação não foi enviada)
        $registration = $registration->refreshed();
        
        $this->assertEmpty(
            $registration->consolidatedResult,
            'Certificando que o resultado consolidado não está definido enquanto há avaliações não enviadas'
        );

        // Buscar a avaliação criada para enviá-la
        $app = App::i();
        $first_user = $app->repo('User')->find($valuer_user_ids[0]);
        $first_evaluation = $app->repo('RegistrationEvaluation')->findOneBy([
            'registration' => $registration,
            'user' => $first_user
        ]);
        
        $app->disableAccessControl();
        $first_evaluation->send();
        $app->enableAccessControl();

        // Verificar que o consolidatedResult ainda não está consolidado
        $registration = $registration->refreshed();
        
        $this->assertEmpty(
            $registration->consolidatedResult,
            'Certificando que o resultado consolidado não está definido enquanto nem todos os avaliadores enviaram suas avaliações'
        );

        // Segundo avaliador cria e envia avaliação
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names[1])
            ->evaluation($registration)
                ->setSelected('Avaliação positiva')
                ->save()
                ->send()
                ->done();

        // Verificar que o consolidatedResult está consolidado após todas as avaliações serem enviadas
        $registration = $registration->refreshed();
        
        $this->assertNotEmpty(
            $registration->consolidatedResult,
            'Certificando que o resultado consolidado está definido após todas as avaliações serem enviadas'
        );
        
        $this->assertEquals(
            Registration::STATUS_APPROVED,
            (string)$registration->consolidatedResult,
            'Certificando que o resultado consolidado é o valor correto (selecionado = 10)'
        );
    }
}

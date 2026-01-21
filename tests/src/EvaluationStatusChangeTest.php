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

class EvaluationStatusChangeTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testRegistrationStatusNotChangedUntilAllEvaluatorsEvaluate()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setCategories(['Música', 'Teatro'])
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setAutoApplicationAllowed(true)
                ->setCommitteeValuersPerRegistration('Comissão Música', 2)
                ->setCommitteeValuersPerRegistration('Comissão Teatro', 1)
                ->setCommitteeFilterCategory('Comissão Música', ['Música'])
                ->setCommitteeFilterCategory('Comissão Teatro', ['Teatro'])
                ->save();
        
        // Adicionar avaliadores para cada comissão
        $evaluation_phase_builder->addValuers(2, 'Comissão Música');
        $evaluation_phase_builder->addValuers(1, 'Comissão Teatro');
        
        $opportunity = $evaluation_phase_builder
            ->done()
            ->getInstance();

        // Criar inscrições
        $registration_music = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: ['category' => 'Música']
        );

        $registration_theater = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: ['category' => 'Teatro']
        );

        // Redistribuir inscrições para os avaliadores
        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration_music = $registration_music->refreshed();
        $registration_theater = $registration_theater->refreshed();

        // Obter os avaliadores da inscrição
        $valuers = $registration_music->valuers;
        $this->assertCount(
            2,
            $valuers,
            'Certificando que a inscrição de Música tem 2 avaliadores'
        );

        $valuer_user_ids = array_keys($valuers);
        $valuer_names_music = [];
        
        // Buscar os nomes dos avaliadores na comissão
        $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão Música'] ?? [];
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                $valuer_names_music[] = $relation->agent->name;
            }
        }

        // Primeiro avaliador avalia
        $evaluation_phase_builder->withValuer('Comissão Música', $valuer_names_music[0])
            ->evaluation($registration_music)
                ->setSelected('Avaliação positiva')
                ->save()
                ->send()
                ->done();

        // Verificar que o status da inscrição NÃO mudou ainda (faltam avaliadores)
        $registration_music = $registration_music->refreshed();
        $this->assertEquals(
            Registration::STATUS_SENT,
            $registration_music->status,
            'Certificando que a inscrição não foi aprovada enquanto nem todos os avaliadores avaliaram'
        );

        // Segundo avaliador avalia
        $evaluation_phase_builder->withValuer('Comissão Música', $valuer_names_music[1])
            ->evaluation($registration_music)
                ->setSelected('Avaliação positiva')
                ->save()
                ->send()
                ->done();
        
        // Verificar que o status da inscrição foi aprovado após todos os avaliadores avaliarem como selecionado
        $registration_music = $registration_music->refreshed();
        $this->assertEquals(
            Registration::STATUS_APPROVED,
            $registration_music->status,
            'Certificando que a inscrição foi aprovada após todos os avaliadores avaliarem como selecionado'
        );

        // Verificar que a inscrição de Teatro tem apenas 1 avaliador
        $valuers_theater = $registration_theater->valuers;
        $this->assertCount(
            1,
            $valuers_theater,
            'Certificando que a inscrição de Teatro tem 1 avaliador'
        );

        // Obter o nome do avaliador de Teatro
        $theater_valuer_user_ids = array_keys($valuers_theater);
        $theater_valuer_name = null;
        
        $committee_relations_theater = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão Teatro'] ?? [];
        foreach ($committee_relations_theater as $relation) {
            if (in_array($relation->agent->user->id, $theater_valuer_user_ids)) {
                $theater_valuer_name = $relation->agent->name;
                break;
            }
        }
        
        // Único avaliador de Teatro avalia
        $evaluation_phase_builder->withValuer('Comissão Teatro', $theater_valuer_name)
            ->evaluation($registration_theater)
                ->setSelected('Avaliação positiva')
                ->save()
                ->send()
                ->done();

        // Verificar que o status da inscrição de Teatro foi aprovado
        $registration_theater = $registration_theater->refreshed();
        $this->assertEquals(Registration::STATUS_APPROVED, $registration_theater->status, 'Certificando que a inscrição de Teatro foi aprovada');
    }

    function testSimpleEvaluationStatusMatchesConsolidatedResult()
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
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save()
                ->addValuers(1, 'Comissão')
                ->done();
        
        $opportunity = $evaluation_phase_builder->getInstance();

        // Testar cada valor consolidado: inválido (2), não selecionado (3), suplente (8), selecionado (10)
        $test_cases = [
            ['status' => Registration::STATUS_INVALID, 'method' => 'setInvalid', 'description' => 'inválido'],
            ['status' => Registration::STATUS_NOTAPPROVED, 'method' => 'setNotSelected', 'description' => 'não selecionado'],
            ['status' => Registration::STATUS_WAITLIST, 'method' => 'setWaitlist', 'description' => 'suplente'],
            ['status' => Registration::STATUS_APPROVED, 'method' => 'setSelected', 'description' => 'selecionado'],
        ];

        foreach ($test_cases as $test_case) {
            // Criar inscrição para este teste
            $registration = $this->registrationDirector->createSentRegistration(
                $opportunity,
                data: []
            );

            // Redistribuir inscrições para os avaliadores
            $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
            $registration = $registration->refreshed();

            // Obter o nome do avaliador
            $valuers = $registration->valuers;
            $valuer_user_ids = array_keys($valuers);
            $valuer_name = null;
            
            $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão'] ?? [];
            foreach ($committee_relations as $relation) {
                if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                    $valuer_name = $relation->agent->name;
                    break;
                }
            }

            // Avaliar com o valor do teste
            $evaluation_phase_builder->withValuer('Comissão', $valuer_name)
                ->evaluation($registration)
                    ->{$test_case['method']}('Avaliação')
                    ->save()
                    ->send()
                    ->done();

            // Verificar que o status da inscrição é igual ao valor consolidado
            $registration = $registration->refreshed();
            $this->assertEquals(
                $test_case['status'],
                $registration->status,
                "Certificando que o status da inscrição é igual ao resultado consolidado ({$test_case['description']})"
            );

            // Verificar também que o consolidatedResult corresponde ao status
            $this->assertEquals(
                (string)$test_case['status'],
                (string)$registration->consolidatedResult,
                "Certificando que o consolidatedResult corresponde ao status ({$test_case['description']})"
            );
        }
    }
}

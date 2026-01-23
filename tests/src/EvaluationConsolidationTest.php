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

    function testDocumentaryEvaluationConsolidationResult()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        // Configurar oportunidade com campo de registro
        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->save()
                ->createStep('Etapa 1')
                ->createField('campo_teste', 'text', 'Campo de Teste')
                ->save()
                ->done();
        
        $evaluation_phase_builder = $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::documentary)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setAutoApplicationAllowed(true)
                ->setCommitteeValuersPerRegistration('Comissão', 2)
                ->save()
                ->addValuers(2, 'Comissão');
        
        $opportunity_builder = $evaluation_phase_builder->done();
        $opportunity = $opportunity_builder->getInstance();
        $opportunity_builder->save();
        $opportunity = $opportunity->refreshed();
        
        // Obter o campo criado
        $field = $this->opportunityBuilder->getField('campo_teste', $opportunity);
        $field_name = $field->fieldName;

        // Teste 1: 2 avaliações válidas => resultado selecionado (1)
        $registration_valid = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => 'Valor de teste']
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration_valid = $registration_valid->refreshed();

        $valuers = $registration_valid->valuers;
        $valuer_user_ids = array_keys($valuers);
        $valuer_names = [];
        
        $committee_relations = $opportunity->evaluationMethodConfiguration->getAgentRelationsGrouped()['Comissão'] ?? [];
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids)) {
                $valuer_names[] = $relation->agent->name;
            }
        }

        // Primeiro avaliador avalia como válido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names[0])
            ->evaluation($registration_valid)
                ->setValid()
                ->save()
                ->send()
                ->done();

        // Segundo avaliador avalia como válido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names[1])
            ->evaluation($registration_valid)
                ->setValid()
                ->save()
                ->send()
                ->done();

        // Verificar que o resultado consolidado é válido (1 = selecionado)
        $registration_valid = $registration_valid->refreshed();
        
        $this->assertEquals(
            '1',
            (string)$registration_valid->consolidatedResult,
            'Certificando que 2 avaliações válidas resultam em selecionado (1)'
        );

        // Teste 2: 2 avaliações inválidas => resultado inválido (-1)
        $opportunity = $opportunity->refreshed();
        $registration_invalid = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => 'Valor de teste']
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration_invalid = $registration_invalid->refreshed();

        $valuers_invalid = $registration_invalid->valuers;
        $valuer_user_ids_invalid = array_keys($valuers_invalid);
        $valuer_names_invalid = [];
        
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids_invalid)) {
                $valuer_names_invalid[] = $relation->agent->name;
            }
        }

        // Primeiro avaliador avalia como inválido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names_invalid[0])
            ->evaluation($registration_invalid)
                ->setInvalid((string)$field->id, $field->title, 'Item não cumprido')
                ->save()
                ->send()
                ->done();

        // Segundo avaliador avalia como inválido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names_invalid[1])
            ->evaluation($registration_invalid)
                ->setInvalid((string)$field->id, $field->title, 'Item não cumprido')
                ->save()
                ->send()
                ->done();

        // Verificar que o resultado consolidado é inválido (-1)
        $registration_invalid = $registration_invalid->refreshed();
        
        $this->assertEquals(
            '-1',
            (string)$registration_invalid->consolidatedResult,
            'Certificando que 2 avaliações inválidas resultam em inválido (-1)'
        );

        // Teste 3: 1 avaliação válida e 1 inválida => resultado inválido (-1)
        $opportunity = $opportunity->refreshed();
        $registration_mixed = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => 'Valor de teste']
        );

        $opportunity->evaluationMethodConfiguration->redistributeCommitteeRegistrations();
        $registration_mixed = $registration_mixed->refreshed();

        $valuers_mixed = $registration_mixed->valuers;
        $valuer_user_ids_mixed = array_keys($valuers_mixed);
        $valuer_names_mixed = [];
        
        foreach ($committee_relations as $relation) {
            if (in_array($relation->agent->user->id, $valuer_user_ids_mixed)) {
                $valuer_names_mixed[] = $relation->agent->name;
            }
        }

        // Primeiro avaliador avalia como válido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names_mixed[0])
            ->evaluation($registration_mixed)
                ->setValid()
                ->save()
                ->send()
                ->done();

        // Segundo avaliador avalia como inválido
        $evaluation_phase_builder->withValuer('Comissão', $valuer_names_mixed[1])
            ->evaluation($registration_mixed)
                ->setInvalid((string)$field->id, $field->title, 'Item não cumprido')
                ->save()
                ->send()
                ->done();

        // Verificar que o resultado consolidado é inválido (-1)
        $registration_mixed = $registration_mixed->refreshed();
        
        $this->assertEquals(
            '-1',
            (string)$registration_mixed->consolidatedResult,
            'Certificando que 1 avaliação válida e 1 inválida resultam em inválido (-1)'
        );
    }
}

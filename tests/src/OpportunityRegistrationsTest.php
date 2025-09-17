<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityRegistrationsTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;


    function testRequiredOneLevelConditionalField() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                            ->fillRequiredProperties()
                            ->save()
                            ->firstPhase()
                                ->setRegistrationPeriod(new Open)
                                ->createStep('etapa')
                                ->createField('cor', 'select', required:true, options:['Azul', 'Vermelho', 'Amarelo'])
                                ->createField('tom-de-azul', 'text', required:true, field_condition:'cor:Azul')
                                ->createField('tom-de-vermelho', 'text', required:true, field_condition:'cor:Vermelho')
                                ->done()
                            ->save()
                            ->refresh()
                            ->getInstance();
        
        $field_cor = $this->opportunityBuilder->getFieldName('cor');
        $field_tom_de_azul = $this->opportunityBuilder->getFieldName('tom-de-azul');
        $field_tom_de_vermelho = $this->opportunityBuilder->getFieldName('tom-de-vermelho');

        $registrations = $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 3
        );

        
        list($azul, $vermelho, $amarelo) = $registrations;
        
        $amarelo->$field_cor = 'Amarelo';
        $this->assertEmpty($amarelo->validationErrors, 
            'Certificando que um CAMPO OBRIGATÓRIO NÃO PREENCHIDO, quando condicionado a outro campo, NÃO causa erro de validação quando a condição para sua exibição NÃO foi ATENDIDA');
        
        $azul->$field_cor = 'Azul';
        $this->assertArrayHasKey($field_tom_de_azul, $azul->validationErrors, 
            'Certificando que um CAMPO OBRIGATÓRIO NÃO PREENCHIDO, quando condicionado a outro campo, CAUSA erro de validação quando a condição para sua exibição FOI ATENDIDA');

        $this->assertCount(1, $azul->validationErrors, 
            'Certificando que há o número certo de campos com erro de validação quando um CAMPO OBRIGATÓRIO NÃO PREENCHIDO condicionado a outro campo CAUSA erro de validação quando a condição para sua exibição FOI ATENDIDA');

        $vermelho->$field_cor = 'Vermelho';
        $vermelho->$field_tom_de_vermelho = 'Escuro';
        $this->assertEmpty($amarelo->validationErrors, 
            'Certificando que um CAMPO OBRIGATÓRIO PREENCHIDO, quando condicionado a outro campo, NÃO causa erro de validação quando a condição para sua exibição FOI ATENDIDA');

    }

    function testRequiredTwoLevelsConditionalField() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                            ->fillRequiredProperties()
                            ->save()
                            ->firstPhase()
                                ->setRegistrationPeriod(new Open)
                                ->createStep('etapa')
                                ->createField('cor', 'select', required:true, options:['Azul', 'Vermelho', 'Amarelo'])
                                ->createField('tom-de-vermelho', 'text', required:true, field_condition:'cor:Vermelho')
                                ->createField('pq-vermelho-escuro', 'text', required:true, field_condition:'tom-de-vermelho:Escuro')
                                ->done()
                            ->save()
                            ->refresh()
                            ->getInstance();
        
        $field_cor = $this->opportunityBuilder->getFieldName('cor');
        $field_tom_de_vermelho = $this->opportunityBuilder->getFieldName('tom-de-vermelho');
        $field_pq_vermelho_escuro = $this->opportunityBuilder->getFieldName('pq-vermelho-escuro');

        $registrations = $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 1
        );
        
        $registration = $registrations[0];
        
        $registration->$field_cor = 'Vermelho';
        $registration->$field_tom_de_vermelho = 'Escuro';

        $this->assertArrayHasKey($field_pq_vermelho_escuro, $registration->validationErrors, 
            'Certificando que um CAMPO OBRIGATÓRIO NÃO PREENCHIDO, quando condicionado a outro campo condicionado, CAUSA erro de validação quando a condição para sua exibição FOI ATENDIDA');

        $this->assertCount(1, $registration->validationErrors, 
            'Certificando que há o número certo de campos com erro de validação quando um CAMPO OBRIGATÓRIO NÃO PREENCHIDO condicionado a outro campo condicionado CAUSA erro de validação quando a condição para sua exibição FOI ATENDIDA');

        $registration->$field_cor = 'Amarelo';
        $this->assertEmpty($registration->validationErrors, 
            'Certificando que um CAMPO OBRIGATÓRIO NÃO PREENCHIDO, quando condicionado a outro campo condicionado, NÃO causa erro de validação quando a condição para sua exibição NÃO foi ATENDIDA APÓS TER SIDO ATENDIDA ANTERIORMENTE');

    }

    function testRequiredFirstPhaseFieldOnSecondDataCollectionPhase() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
                            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
                            ->fillRequiredProperties()
                            ->save()
                            ->firstPhase()
                                ->setRegistrationPeriod(new Open)
                                ->createStep('etapa')
                                ->createField('cor', 'select', required:true, options:['Azul', 'Vermelho', 'Amarelo'])
                                ->done()
                            ->addDataCollectionPhase()
                                ->setRegistrationPeriod(new Open)
                                ->createStep('etapa')
                                ->createField('fruta', 'select', required:true, options:['Abacate', 'Morango', 'Banana'])
                                ->done()
                            ->save()
                            ->refresh()
                            ->getInstance();
        
        $field_cor = $this->opportunityBuilder->getFieldName('cor');
        $field_fruta = $this->opportunityBuilder->getFieldName('fruta');

        $registrations = $this->registrationDirector->createDraftRegistrations(
            $opportunity,
            number_of_registrations: 1
        );
        
        $registration = $registrations[0];
        
        $registration->$field_cor = 'Vermelho';
        
        $this->assertEmpty($registration->validationErrors, 
            'Certificando que não há erro de validação na primeira fase, após preencher todos os campos obrigatórios');

        $registration->send();
        $registration->setStatus(10);

        $second_phase = $opportunity->nextPhase;
        $second_phase->syncRegistrations();

        $second_phase_registration = $registration->nextPhase;
        $second_phase_registration->$field_fruta = 'Banana';

        $this->assertEmpty($registration->validationErrors, 
            'Certificando que não há erro de validação na segunda fase, após preencher todos os campos obrigatórios');
        
    }
}

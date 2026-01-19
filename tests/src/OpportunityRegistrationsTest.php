<?php

namespace Test;

use MapasCulturais\API;
use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Connection;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Traits\AgentDirector;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityRegistrationsTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        AgentDirector,
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

        $this->assertEmpty($second_phase_registration->validationErrors, 
            'Certificando que não há erro de validação na segunda fase, após preencher todos os campos obrigatórios');
        
    }

    function testAgentOwnerFieldsApi() {
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
                                ->createOwnerField('campo-pessoa-deficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                                ->createOwnerField('campo-escolaridade', 'escolaridade', 'Escolaridade', required: false)
                                ->createField('campo-texto', 'text', 'Campo Texto', required: false)
                                ->createField('campo-select', 'select', 'Campo Select', required: false, options: ['Opção 1', 'Opção 2'])
                                ->done()
                            ->save()
                            ->refresh()
                            ->getInstance();

        // Obter os nomes dos campos
        $field_pessoa_deficiente_name = $this->opportunityBuilder->getFieldName('campo-pessoa-deficiente');
        $field_escolaridade_name = $this->opportunityBuilder->getFieldName('campo-escolaridade');
        $field_texto_name = $this->opportunityBuilder->getFieldName('campo-texto');

        // Criar inscrições
        $number_of_registrations = 50;
        $registrations = $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: $number_of_registrations
        );

        // Preencher valores nos campos @ através do agente responsável
        $pessoa_deficiente_options = ['Auditiva', 'Visual', 'Física-motora', 'Intelectual'];
        $escolaridade_options = [
            'Ensino Fundamental Completo',
            'Ensino Médio Completo',
            'Ensino Superior Completo',
            'Mestrado Completo'
        ];

        // Armazenar valores esperados por ID de inscrição para comparação posterior
        $expected_values_by_id = [];

        // Preencher valores diretamente nas inscrições usando os nomes dos campos
        foreach ($registrations as $index => $registration) {
            // Preencher pessoaDeficiente
            $pessoa_deficiente_value = [$pessoa_deficiente_options[$index % count($pessoa_deficiente_options)]];
            $registration->$field_pessoa_deficiente_name = $pessoa_deficiente_value;
            
            // Preencher escolaridade
            $escolaridade_value = $escolaridade_options[$index % count($escolaridade_options)];
            $registration->$field_escolaridade_name = $escolaridade_value;
            
            // Armazenar valores esperados pelo ID da inscrição
            $expected_values_by_id[$registration->id] = [
                'pessoaDeficiente' => $pessoa_deficiente_value,
                'escolaridade' => $escolaridade_value
            ];
            
            $registration->save(true);
        }

        $opportunity->registerRegistrationMetadata();

        // Buscar inscrições via API, selecionando apenas campos do tipo @
        $query = new ApiQuery(Registration::class, [
            '@select' => "id,number,{$field_pessoa_deficiente_name},{$field_escolaridade_name}",
            'opportunity' => API::EQ($opportunity->id),
            'status' => API::GTE(Registration::STATUS_DRAFT),
            '@order' => 'id ASC'
        ]);

        $result = $query->find();

        // Verificar que todas as inscrições foram retornadas
        $this->assertCount($number_of_registrations, $result, 'Certificando que todas as inscrições foram retornadas pela API');

        // Verificar que cada inscrição retornada contém os campos @ esperados com os valores preenchidos
        foreach ($result as $registration_data) {
            $registration_id = $registration_data['id'];
            
            $this->assertArrayHasKey($field_pessoa_deficiente_name, $registration_data, 'Certificando que o campo pessoaDeficiente está presente na resposta da API');
            $this->assertArrayHasKey($field_escolaridade_name, $registration_data, 'Certificando que o campo escolaridade está presente na resposta da API');

            // Verificar que os valores não estão vazios
            $this->assertNotEmpty($registration_data[$field_pessoa_deficiente_name], 'Certificando que pessoaDeficiente não está vazio');
            $this->assertNotEmpty($registration_data[$field_escolaridade_name], 'Certificando que escolaridade não está vazia');
            
            // Verificar que os valores foram preenchidos corretamente usando o ID da inscrição
            $this->assertArrayHasKey($registration_id, $expected_values_by_id, "Certificando que a inscrição {$registration_id} está nos valores esperados");
            
            $expected = $expected_values_by_id[$registration_id];
            
            $this->assertEquals($expected['pessoaDeficiente'], $registration_data[$field_pessoa_deficiente_name], "Certificando que o valor de pessoaDeficiente está correto na inscrição {$registration_id}");
            $this->assertEquals($expected['escolaridade'], $registration_data[$field_escolaridade_name], "Certificando que o valor de escolaridade está correto na inscrição {$registration_id}");
        }
    }

    function testAgentCollectiveFieldsApi() {
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
                                ->createCollectiveField('campo-pessoa-deficiente-coletivo', 'pessoaDeficiente', 'Pessoa com Deficiência (Coletivo)', required: false)
                                ->createCollectiveField('campo-escolaridade-coletivo', 'escolaridade', 'Escolaridade (Coletivo)', required: false)
                                ->createField('campo-texto', 'text', 'Campo Texto', required: false)
                                ->done()
                            ->save()
                            ->refresh()
                            ->getInstance();

        // Obter os nomes dos campos
        $field_pessoa_deficiente_name = $this->opportunityBuilder->getFieldName('campo-pessoa-deficiente-coletivo');
        $field_escolaridade_name = $this->opportunityBuilder->getFieldName('campo-escolaridade-coletivo');

        // Criar inscrições
        $number_of_registrations = 50;
        $registrations = $this->registrationDirector->createSentRegistrations(
            $opportunity,
            number_of_registrations: $number_of_registrations
        );

        // Preencher valores nos campos @ através do agente coletivo
        $pessoa_deficiente_options = ['Auditiva', 'Visual', 'Física-motora', 'Intelectual'];
        $escolaridade_options = [
            'Ensino Fundamental Completo',
            'Ensino Médio Completo',
            'Ensino Superior Completo',
            'Mestrado Completo'
        ];

        // Armazenar valores esperados por ID de inscrição para comparação posterior
        $expected_values_by_id = [];

        // Criar agentes coletivos e relacioná-los às inscrições, preenchendo valores
        $app = App::i();
        foreach ($registrations as $index => $registration) {
            // Criar agente coletivo
            $collective_agent = $this->agentDirector->createAgent(
                $registration->owner->user,
                type: 2,
                save: true,
                flush: false
            );

            // Preencher valores no agente coletivo
            $pessoa_deficiente_value = [$pessoa_deficiente_options[$index % count($pessoa_deficiente_options)]];
            $collective_agent->pessoaDeficiente = $pessoa_deficiente_value;
            
            $escolaridade_value = $escolaridade_options[$index % count($escolaridade_options)];
            $collective_agent->escolaridade = $escolaridade_value;
            
            $collective_agent->save(true);

            // Relacionar agente coletivo à inscrição
            $registration->createAgentRelation($collective_agent, 'coletivo');

            // Preencher valores diretamente nas inscrições usando os nomes dos campos
            $registration->$field_pessoa_deficiente_name = $pessoa_deficiente_value;
            $registration->$field_escolaridade_name = $escolaridade_value;
            
            // Armazenar valores esperados pelo ID da inscrição
            $expected_values_by_id[$registration->id] = [
                'pessoaDeficiente' => $pessoa_deficiente_value,
                'escolaridade' => $escolaridade_value
            ];
            
            $registration->save(true);
        }

        $opportunity->registerRegistrationMetadata();

        // Buscar inscrições via API, selecionando apenas campos do tipo @
        $query = new ApiQuery(Registration::class, [
            '@select' => "id,number,{$field_pessoa_deficiente_name},{$field_escolaridade_name}",
            'opportunity' => API::EQ($opportunity->id),
            'status' => API::GTE(Registration::STATUS_DRAFT),
            '@order' => 'id ASC'
        ]);

        $result = $query->find();

        // Verificar que todas as inscrições foram retornadas
        $this->assertCount($number_of_registrations, $result, 'Certificando que todas as inscrições foram retornadas pela API');

        // Verificar que cada inscrição retornada contém os campos @ esperados com os valores preenchidos
        foreach ($result as $registration_data) {
            $registration_id = $registration_data['id'];
            
            $this->assertArrayHasKey($field_pessoa_deficiente_name, $registration_data, 'Certificando que o campo pessoaDeficiente (coletivo) está presente na resposta da API');
            $this->assertArrayHasKey($field_escolaridade_name, $registration_data, 'Certificando que o campo escolaridade (coletivo) está presente na resposta da API');

            // Verificar que os valores não estão vazios
            $this->assertNotEmpty($registration_data[$field_pessoa_deficiente_name], 'Certificando que pessoaDeficiente (coletivo) não está vazio');
            $this->assertNotEmpty($registration_data[$field_escolaridade_name], 'Certificando que escolaridade (coletivo) não está vazia');
            
            // Verificar que os valores foram preenchidos corretamente usando o ID da inscrição
            $this->assertArrayHasKey($registration_id, $expected_values_by_id, "Certificando que a inscrição {$registration_id} está nos valores esperados");
            
            $expected = $expected_values_by_id[$registration_id];
            
            $this->assertEquals($expected['pessoaDeficiente'], $registration_data[$field_pessoa_deficiente_name], "Certificando que o valor de pessoaDeficiente (coletivo) está correto na inscrição {$registration_id}");
            $this->assertEquals($expected['escolaridade'], $registration_data[$field_escolaridade_name], "Certificando que o valor de escolaridade (coletivo) está correto na inscrição {$registration_id}");
        }
    }
}

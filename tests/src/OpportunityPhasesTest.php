<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\DateTime;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\After;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Builders\PhasePeriods\Past;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class OpportunityPhasesTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;


    function testFirstEvaluationPhaseDeletion() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        /** @var Opportunity */
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
                                ->addEvaluationPhase(EvaluationMethods::simple)
                                    ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                    ->setCommitteeValuersPerRegistration('committee 1', 1)
                                    ->save()
                                    ->addValuers(2, 'committee 1')
                                    ->done()
                                ->refresh()
                                ->getInstance();
        

        $opportunity->evaluationMethodConfiguration->delete(true);
        
        $opportunity = $opportunity->refreshed();

        $phases = $opportunity->phases;

        $this->assertEquals($opportunity->id, $phases[1]->opportunity->id, "Garantindo que uma segunda fase de avaliação, após a exclusão da primeira fase de avaliação, esteja vinculada a primeira fase de coletada de dados");
    
    }

    function testSecondEvaluationPhaseDeletion() {
        $app = $this->app;

        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $builder = $this->opportunityBuilder;
        
        /** @var Opportunity */
        $opportunity = $builder->reset(owner: $admin->profile, owner_entity: $admin->profile)
                                ->fillRequiredProperties()
                                ->firstPhase()
                                    ->setRegistrationPeriod(new Open)
                                    ->done()
                                ->save()
                                ->getInstance();

        $eval_phase1 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase2 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();

        $eval_phase3 = $builder->addEvaluationPhase(EvaluationMethods::simple)
                                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                                ->save()
                                ->getInstance();
        


        $this->assertFalse((bool) $eval_phase2->opportunity->isDataCollection, 'Garantindo que a  Opportunity vinculada a uma fase de avaliação que segue outra fase de avaliação não é uma coleta de dados');

        $hidden_opportunity_id = $eval_phase2->opportunity->id;
        
        $eval_phase2->delete(flush: true);

        $hidden_opportunity = $app->repo('Opportunity')->find($hidden_opportunity_id);

        $this->assertNull($hidden_opportunity, 'Garantindo que a exclusão de uma fase de avaliação exclua a Opportunity vinculada quando essa não é uma coleta de dados');
    
    }

    function processJobsNow($datetime) {
        $app = App::i();
        $app->disableAccessControl();
        DateTime::$datetime = $datetime;
        $this->processJobs('now');
        $app->enableAccessControl();
    }

    function testPhasesPublishedRegistrations() {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $app = App::i();

        // Definindo datas relativas ao momento da execução dos testes
        $data_inscricao_inicio = date('Y-m-d H:i:s', strtotime('+1 day 08:00'));
        $data_inscricao_fim = date('Y-m-d H:i:s', strtotime('+2 days 18:00'));
        $data_publicacao_inscricao = date('Y-m-d H:i:s', strtotime('+2 days 21:00'));
        
        $data_avaliacao_documental_inicio = date('Y-m-d H:i:s', strtotime('+3 days 08:00'));
        $data_avaliacao_documental_fim = date('Y-m-d H:i:s', strtotime('+3 days 18:00'));
        $data_publicacao_avaliacao_documental = date('Y-m-d H:i:s', strtotime('+3 days 21:00'));
        
        $data_avaliacao_tecnica_inicio = date('Y-m-d H:i:s', strtotime('+4 days 08:00'));
        $data_avaliacao_tecnica_fim = date('Y-m-d H:i:s', strtotime('+4 days 18:00'));
        $data_publicacao_avaliacao_tecnica = date('Y-m-d H:i:s', strtotime('+4 days 21:00'));
        
        $data_coleta_dados_1_inicio = date('Y-m-d H:i:s', strtotime('+5 days 08:00'));
        $data_coleta_dados_1_fim = date('Y-m-d H:i:s', strtotime('+5 days 18:00'));
        $data_publicacao_coleta_dados_1 = date('Y-m-d H:i:s', strtotime('+5 days 21:00'));
        
        $data_coleta_dados_2_inicio = date('Y-m-d H:i:s', strtotime('+6 days 08:00'));
        $data_coleta_dados_2_fim = date('Y-m-d H:i:s', strtotime('+6 days 18:00'));
        
        $data_publicacao_final = date('Y-m-d H:i:s', strtotime('+7 days 21:00'));

        /** @var Opportunity */
        $opportunity = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->firstPhase()
                ->setName('Primeira fase')
                ->setRegistrationFrom($data_inscricao_inicio)
                ->setRegistrationTo($data_inscricao_fim)
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::documentary)
                ->setEvaluationFrom($data_avaliacao_documental_inicio)
                ->setEvaluationTo($data_avaliacao_documental_fim)
                ->fillRequiredProperties()
                ->setName('Avaliação Documental')
                ->save()
                ->setPublishTimestamp($data_publicacao_avaliacao_documental)
                ->setAutoPublish(true)
                ->done()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationFrom($data_avaliacao_tecnica_inicio)
                ->setEvaluationTo($data_avaliacao_tecnica_fim)
                ->fillRequiredProperties()
                ->setName('Avaliação Técnica')
                ->save()
                ->setPublishTimestamp($data_publicacao_avaliacao_tecnica)
                ->setAutoPublish(true)
                ->done()
            ->addDataCollectionPhase()
                ->fillRequiredProperties()
                ->setName('Coleta de dados 1')
                ->setRegistrationFrom($data_coleta_dados_1_inicio)
                ->setRegistrationTo($data_coleta_dados_1_fim)
                ->setPublishTimestamp($data_publicacao_coleta_dados_1)
                ->setAutoPublish(true)
                ->save()
                ->done()
            ->addDataCollectionPhase()
                ->fillRequiredProperties()
                ->setName('Coleta de dados 2')
                ->setRegistrationFrom($data_coleta_dados_2_inicio)
                ->setRegistrationTo($data_coleta_dados_2_fim)
                ->save()
                ->done()
            ->lastPhase()
                ->fillRequiredProperties()
                ->setName('Publicação final')
                ->setPublishTimestamp($data_publicacao_final)
                ->setAutoPublish(true)
                ->save()
                ->done()
            ->save()
            ->refresh()
            ->getInstance();

        $all_phases = $opportunity->allPhases;

        // Teste 1: Verificar que todas as fases começam com publishedRegistrations = false
        foreach ($all_phases as $phase) {
            $this->assertFalse(
                (bool) $phase->publishedRegistrations,
                "Fase '{$phase->name}' deve começar com publishedRegistrations = false"
            );
        }
        
        // Teste 2: Após processar a primeira fase (avaliação documental)
        $this->processJobsNow($data_publicacao_avaliacao_documental);
        $opportunity = $opportunity->refreshed();
        $all_phases = $opportunity->allPhases;
        
        $this->assertTrue(
            (bool) $all_phases[0]->publishedRegistrations,
            'Primeira fase (avaliação documental) deve estar com publishedRegistrations = true após processJobs'
        );
        
        // Verificar que as fases seguintes continuam false
        for ($i = 1; $i < count($all_phases); $i++) {
            $this->assertFalse(
                (bool) $all_phases[$i]->publishedRegistrations,
                "Fase '{$all_phases[$i]->name}' (índice $i) deve continuar com publishedRegistrations = false"
            );
        }

        // Teste 3: Após processar a segunda fase (avaliação técnica)
        $all_phases[1]->save(true);
        $this->processJobsNow($data_publicacao_avaliacao_tecnica);
        $opportunity = $opportunity->refreshed();
        $all_phases = $opportunity->allPhases;

        $this->assertTrue(
            (bool) $all_phases[1]->publishedRegistrations,
            'Segunda fase (avaliação técnica) deve estar com publishedRegistrations = true após processJobs'
        );
        
        // Verificar que as fases seguintes continuam false
        for ($i = 2; $i < count($all_phases); $i++) {
            $this->assertFalse(
                (bool) $all_phases[$i]->publishedRegistrations,
                "Fase '{$all_phases[$i]->name}' (índice $i) deve continuar com publishedRegistrations = false"
            );
        }

        // Teste 4: Após processar a terceira fase (coleta de dados 1)
        $this->processJobsNow($data_publicacao_coleta_dados_1);
        $opportunity = $opportunity->refreshed();
        $all_phases = $opportunity->allPhases;
        
        $this->assertTrue(
            (bool) $all_phases[2]->publishedRegistrations,
            'Terceira fase (coleta de dados 1) deve estar com publishedRegistrations = true após processJobs'
        );
        
        // Verificar que as fases seguintes continuam false
        for ($i = 3; $i < count($all_phases); $i++) {
            $this->assertFalse(
                (bool) $all_phases[$i]->publishedRegistrations,
                "Fase '{$all_phases[$i]->name}' (índice $i) deve continuar com publishedRegistrations = false"
            );
        }

        // Teste 5: Após processar a fase final
        $opportunity->lastPhase->save(true);
        $this->processJobsNow($data_publicacao_final);
        $opportunity = $opportunity->refreshed();

        $this->assertTrue(
            (bool) $opportunity->lastPhase->publishedRegistrations,
            'Fase final deve estar com publishedRegistrations = true após processJobs'
        );

        // Teste 6: Verificar que a fase de coleta de dados 2 (sem publicação) continua false
        $this->assertFalse(
            (bool) $all_phases[3]->publishedRegistrations,
            'Fase de coleta de dados 2 (sem configuração de publicação) deve continuar com publishedRegistrations = false'
        );

        // Teste 7: Verificar que o job PublishResult não existe mais após todas as fases serem processadas
        foreach ($all_phases as $phase) {
            if ($phase->publishTimestamp && $phase->autoPublish) {
                $job_type = $app->getRegisteredJobType('PublishResult');
                $data = ['opportunity' => $phase];
                $job_id = $job_type->generateId($data, '', '', 1);
                
                $job = $app->repo('Job')->findOneBy(['id' => $job_id]);
                $this->assertNull(
                    $job,
                    "Job PublishResult para a fase '{$phase->name}' (ID: {$phase->id}) não deve existir mais após ser processado"
                );
            }
        }
    }
}

<?php

namespace Tests;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use ReflectionMethod;
use Tests\Abstract\TestCase;
use Tests\Builders\DataCollectionPhaseBuilder;
use Tests\Builders\EvaluationPhaseBuilder;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

class RegistrationPdfExportTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    function testPdfShowsCompleteHistoryWithTechnicalEvaluationBetweenDataCollectionPhases()
    {
        $scenario = $this->createPdfHistoryScenario(
            method: EvaluationMethods::technical,
            evaluationPhaseName: 'Avaliação técnica',
            configureEvaluationPhase: function (EvaluationPhaseBuilder $builder): void {
                $builder
                    ->config()
                        ->addSection('sec1', 'Seção técnica')
                        ->addCriterion('cri1', 'sec1', 'Critério técnico', 0, 10, 1)
                        ->done()
                    ->save();
            },
            evaluateRegistration: function (EvaluationPhaseBuilder $builder, Registration $evaluationRegistration): void {
                $builder->withValuer('Comissão', 'Parecerista 1')
                    ->evaluation($evaluationRegistration)
                        ->setCriterionScore('cri1', 8.0, 'Parecer técnico do avaliador')
                        ->save()
                        ->send()
                        ->done();

                $evaluationRegistration->consolidateResult();
                $evaluationRegistration->setStatusToApproved(true);
            }
        );

        $html = $scenario['html'];

        $this->assertStringContainsString('Campo inicial', $html);
        $this->assertStringContainsString('Valor inicial da inscrição', $html);
        $this->assertStringContainsString('Avaliação técnica', $html);
        $this->assertStringContainsString('Critério técnico', $html);
        $this->assertStringContainsString('Parecer técnico do avaliador', $html);
        $this->assertStringContainsString('Campo complementar', $html);
        $this->assertStringContainsString('Valor complementar da inscrição', $html);
        $this->assertStringNotContainsString('Pontuação máxima:', $html);

        $this->assertHtmlOrder(
            $html,
            'Valor inicial da inscrição',
            'Parecer técnico do avaliador',
            'Valor complementar da inscrição'
        );
    }

    function testPdfShowsCompleteHistoryWithQualificationEvaluationBetweenDataCollectionPhases()
    {
        $scenario = $this->createPdfHistoryScenario(
            method: EvaluationMethods::qualification,
            evaluationPhaseName: 'Avaliação de habilitação',
            configureEvaluationPhase: function (EvaluationPhaseBuilder $builder): void {
                $builder
                    ->config()
                        ->addSection('sec1', 'Seção de habilitação')
                        ->addCriterion('cri1', 'sec1', 'Critério de habilitação')
                        ->done()
                    ->save();
            },
            evaluateRegistration: function (EvaluationPhaseBuilder $builder, Registration $evaluationRegistration): void {
                $builder->withValuer('Comissão', 'Parecerista 1')
                    ->evaluation($evaluationRegistration)
                        ->setQualified('cri1', 'Parecer da habilitação')
                        ->save()
                        ->send()
                        ->done();

                $evaluationRegistration->consolidateResult();
                $evaluationRegistration->setStatusToApproved(true);
            }
        );

        $html = $scenario['html'];

        $this->assertStringContainsString('Avaliação de habilitação', $html);
        $this->assertStringContainsString('Critério de habilitação', $html);
        $this->assertStringContainsString('Parecer da habilitação', $html);
        $this->assertStringContainsString('Atende', $html);
        $this->assertStringContainsString('Valor complementar da inscrição', $html);

        $this->assertHtmlOrder(
            $html,
            'Valor inicial da inscrição',
            'Parecer da habilitação',
            'Valor complementar da inscrição'
        );
    }

    function testPdfShowsCompleteHistoryWithDocumentaryEvaluationBetweenDataCollectionPhases()
    {
        $scenario = $this->createPdfHistoryScenario(
            method: EvaluationMethods::documentary,
            evaluationPhaseName: 'Avaliação documental',
            configureFirstPhase: function (DataCollectionPhaseBuilder $builder): void {
                $builder->createField('documento-obrigatorio', 'text', 'Documento obrigatório');
            },
            evaluateRegistration: function (EvaluationPhaseBuilder $builder, Registration $evaluationRegistration, array $context): void {
                $builder->withValuer('Comissão', 'Parecerista 1')
                    ->evaluation($evaluationRegistration)
                        ->addValidField((string) $context['documentField']->id, 'Documento obrigatório', 'Parecer documental do avaliador')
                        ->save()
                        ->send()
                        ->done();

                $evaluationRegistration->consolidateResult();
                $evaluationRegistration->setStatusToApproved(true);
            },
            registrationData: function (array $context): array {
                return [
                    $context['documentFieldName'] => 'Documento apresentado',
                ];
            }
        );

        $html = $scenario['html'];

        $this->assertStringContainsString('Avaliação documental', $html);
        $this->assertStringContainsString('Documento obrigatório', $html);
        $this->assertStringContainsString('Parecer documental do avaliador', $html);
        $this->assertStringContainsString('Válido', $html);
        $this->assertStringContainsString('Valor complementar da inscrição', $html);

        $this->assertHtmlOrder(
            $html,
            'Valor inicial da inscrição',
            'Parecer documental do avaliador',
            'Valor complementar da inscrição'
        );
    }

    function testPdfShowsCompleteHistoryWithSimpleEvaluationBetweenDataCollectionPhases()
    {
        $scenario = $this->createPdfHistoryScenario(
            method: EvaluationMethods::simple,
            evaluationPhaseName: 'Avaliação simples',
            evaluateRegistration: function (EvaluationPhaseBuilder $builder, Registration $evaluationRegistration): void {
                $builder->withValuer('Comissão', 'Parecerista 1')
                    ->evaluation($evaluationRegistration)
                        ->setSelected('Justificativa da avaliação simples')
                        ->save()
                        ->send()
                        ->done();

                $evaluationRegistration->consolidateResult();
                $evaluationRegistration->setStatusToApproved(true);
            }
        );

        $html = $scenario['html'];

        $this->assertStringContainsString('Avaliação simples', $html);
        $this->assertStringContainsString('Justificativa da avaliação simples', $html);
        $this->assertStringContainsString('Justificativa', $html);
        $this->assertStringContainsString('Valor complementar da inscrição', $html);

        $this->assertHtmlOrder(
            $html,
            'Valor inicial da inscrição',
            'Justificativa da avaliação simples',
            'Valor complementar da inscrição'
        );
    }

    function testPdfShowsFullHistoryAcrossMultipleEvaluationTypesInTheSameOpportunity()
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunityBuilder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save();

        /** @var Opportunity $firstPhase */
        $firstPhase = $opportunityBuilder
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Inscrição')
                ->createField('campo-inicial', 'text', 'Campo inicial')
                ->done()
            ->save()
            ->getInstance();

        $technicalPhaseBuilder = $opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão técnica', 1)
                ->save()
                ->config()
                    ->addSection('sec-tecnica', 'Seção técnica')
                    ->addCriterion('cri-tecnica', 'sec-tecnica', 'Critério técnico', 0, 10, 1)
                    ->done()
                ->save();

        $technicalPhaseBuilder
            ->addValuer('Comissão técnica', 'Parecerista técnico')
            ->done();

        $technicalConfiguration = $technicalPhaseBuilder->getInstance();
        $technicalConfiguration->name = 'Avaliação técnica';
        $technicalConfiguration->publishEvaluationDetails = true;
        $technicalConfiguration->publishValuerNames = true;
        $technicalConfiguration->save(true);

        /** @var Opportunity $secondDataCollectionPhase */
        $secondDataCollectionPhase = $technicalPhaseBuilder
            ->done()
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Segunda coleta')
                ->createField('campo-segunda-coleta', 'text', 'Campo da segunda coleta')
                ->save()
            ->getInstance();

        $simplePhaseBuilder = $opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::simple)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão simples', 1)
                ->save();

        $simplePhaseBuilder
            ->addValuer('Comissão simples', 'Parecerista simples')
            ->done();

        $simpleConfiguration = $simplePhaseBuilder->getInstance();
        $simpleConfiguration->name = 'Avaliação simplificada';
        $simpleConfiguration->publishEvaluationDetails = true;
        $simpleConfiguration->publishValuerNames = true;
        $simpleConfiguration->save(true);

        /** @var Opportunity $thirdDataCollectionPhase */
        $thirdDataCollectionPhase = $simplePhaseBuilder
            ->done()
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Terceira coleta')
                ->createField('campo-terceira-coleta', 'text', 'Campo da terceira coleta')
                ->save()
            ->getInstance();

        $qualificationPhaseBuilder = $opportunityBuilder
            ->addEvaluationPhase(EvaluationMethods::qualification)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão de habilitação', 1)
                ->save()
                ->config()
                    ->addSection('sec-habilitacao', 'Seção de habilitação')
                    ->addCriterion('cri-habilitacao', 'sec-habilitacao', 'Critério de habilitação')
                    ->done()
                ->save();

        $qualificationPhaseBuilder
            ->addValuer('Comissão de habilitação', 'Parecerista de habilitação')
            ->done();

        $qualificationConfiguration = $qualificationPhaseBuilder->getInstance();
        $qualificationConfiguration->name = 'Habilitação final';
        $qualificationConfiguration->publishEvaluationDetails = true;
        $qualificationConfiguration->publishValuerNames = true;
        $qualificationConfiguration->save(true);

        $initialFieldName = $opportunityBuilder->getFieldName('campo-inicial', $firstPhase);
        $secondFieldName = $secondDataCollectionPhase->registrationFieldConfigurations[0]->fieldName ?? null;
        $thirdFieldName = $thirdDataCollectionPhase->registrationFieldConfigurations[0]->fieldName ?? null;

        $this->assertNotNull($secondFieldName);
        $this->assertNotNull($thirdFieldName);

        $registration = $this->registrationDirector->createSentRegistration(
            $firstPhase,
            [
                $initialFieldName => 'Valor inicial da inscrição',
            ]
        );

        $registration->consolidateResult();
        $registration->setStatusToApproved(true);
        $registration = $registration->refreshed();

        $technicalPhaseBuilder->withValuer('Comissão técnica', 'Parecerista técnico')
            ->evaluation($registration)
                ->setCriterionScore('cri-tecnica', 9.0, 'Parecer técnico encadeado')
                ->save()
                ->send()
                ->done();

        $registration->consolidateResult();
        $registration->setStatusToApproved(true);
        $registration = $registration->refreshed();

        $phasesModule = $this->app->modules['OpportunityPhases'];

        $secondDataCollectionPhase->registerRegistrationMetadata(true);
        /** @var Registration $secondPhaseRegistration */
        $secondPhaseRegistration = $phasesModule->createPhaseRegistration($secondDataCollectionPhase, $registration);
        $secondPhaseRegistration->$secondFieldName = 'Valor da segunda coleta';
        $secondPhaseRegistration->save(true);
        $secondPhaseRegistration->send(false);
        $secondPhaseRegistration = $secondPhaseRegistration->refreshed();

        $simplePhaseBuilder->withValuer('Comissão simples', 'Parecerista simples')
            ->evaluation($secondPhaseRegistration)
                ->setSelected('Justificativa da avaliação simplificada')
                ->save()
                ->send()
                ->done();

        $secondPhaseRegistration->consolidateResult();
        $secondPhaseRegistration->setStatusToApproved(true);
        $secondPhaseRegistration = $secondPhaseRegistration->refreshed();

        $thirdDataCollectionPhase->registerRegistrationMetadata(true);
        /** @var Registration $thirdPhaseRegistration */
        $thirdPhaseRegistration = $phasesModule->createPhaseRegistration($thirdDataCollectionPhase, $secondPhaseRegistration);
        $thirdPhaseRegistration->$thirdFieldName = 'Valor da terceira coleta';
        $thirdPhaseRegistration->save(true);
        $thirdPhaseRegistration->send(false);
        $thirdPhaseRegistration = $thirdPhaseRegistration->refreshed();

        $qualificationPhaseBuilder->withValuer('Comissão de habilitação', 'Parecerista de habilitação')
            ->evaluation($thirdPhaseRegistration)
                ->setQualified('cri-habilitacao', 'Parecer final da habilitação')
                ->save()
                ->send()
                ->done();

        $thirdPhaseRegistration->consolidateResult();
        $thirdPhaseRegistration->setStatusToApproved(true);
        $thirdPhaseRegistration = $thirdPhaseRegistration->refreshed();

        $controller = $this->app->controller('Registration');
        $controller->action = 'exportPDF';
        $controller->requestedEntity = $registration;
        $this->app->view->controller = $controller;

        $methodReflection = new ReflectionMethod($controller, 'getPDFHistoryItems');
        $methodReflection->setAccessible(true);
        $historyItems = $methodReflection->invoke($controller, $registration);

        $this->assertCount(6, $historyItems);
        $this->assertSame('data_collection', $historyItems[0]['type']);
        $this->assertSame('evaluation', $historyItems[1]['type']);
        $this->assertSame('data_collection', $historyItems[2]['type']);
        $this->assertSame('evaluation', $historyItems[3]['type']);
        $this->assertSame('data_collection', $historyItems[4]['type']);
        $this->assertSame('evaluation', $historyItems[5]['type']);

        $html = $this->app->view->partialRender(
            'registration/pdf',
            [
                'registration' => $registration,
                'historyItems' => $historyItems,
            ],
            false
        );

        $this->assertStringContainsString('Avaliação técnica', $html);
        $this->assertStringContainsString('Parecer técnico encadeado', $html);
        $this->assertStringContainsString('Campo da segunda coleta', $html);
        $this->assertStringContainsString('Valor da segunda coleta', $html);
        $this->assertStringContainsString('Avaliação simplificada', $html);
        $this->assertStringContainsString('Justificativa da avaliação simplificada', $html);
        $this->assertStringContainsString('Campo da terceira coleta', $html);
        $this->assertStringContainsString('Valor da terceira coleta', $html);
        $this->assertStringContainsString('Habilitação final', $html);
        $this->assertStringContainsString('Parecer final da habilitação', $html);
        $this->assertStringContainsString('Atende', $html);

        $this->assertHtmlOrder(
            $html,
            'Valor inicial da inscrição',
            'Parecer técnico encadeado',
            'Valor da segunda coleta'
        );

        $this->assertHtmlOrder(
            $html,
            'Valor da segunda coleta',
            'Justificativa da avaliação simplificada',
            'Valor da terceira coleta'
        );

        $this->assertTrue(
            strpos($html, 'Valor da terceira coleta') < strpos($html, 'Parecer final da habilitação'),
            'O parecer de habilitação deve aparecer depois da última coleta de dados.'
        );
    }

    private function createPdfHistoryScenario(
        EvaluationMethods $method,
        string $evaluationPhaseName,
        callable $evaluateRegistration,
        ?callable $configureEvaluationPhase = null,
        ?callable $configureFirstPhase = null,
        ?callable $registrationData = null
    ): array {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $opportunityBuilder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save();

        $firstPhaseBuilder = $opportunityBuilder
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Dados iniciais')
                ->createField('campo-inicial', 'text', 'Campo inicial');

        if ($configureFirstPhase) {
            $configureFirstPhase($firstPhaseBuilder);
        }

        /** @var Opportunity $firstPhase */
        $firstPhase = $firstPhaseBuilder
            ->done()
            ->save()
            ->getInstance();

        $initialFieldName = $opportunityBuilder->getFieldName('campo-inicial', $firstPhase);

        $evaluationPhaseBuilder = $opportunityBuilder
            ->addEvaluationPhase($method)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->save();

        if ($configureEvaluationPhase) {
            $configureEvaluationPhase($evaluationPhaseBuilder);
        }

        $evaluationPhaseBuilder
            ->addValuer('Comissão', 'Parecerista 1')
            ->done();

        $evaluationConfiguration = $evaluationPhaseBuilder->getInstance();
        $evaluationConfiguration->name = $evaluationPhaseName;
        $evaluationConfiguration->publishEvaluationDetails = true;
        $evaluationConfiguration->publishValuerNames = true;
        $evaluationConfiguration->save(true);

        $secondDataCollectionBuilder = $evaluationPhaseBuilder
            ->done()
            ->addDataCollectionPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Complementação')
                ->createField('campo-complementar', 'text', 'Campo complementar')
                ->save();

        /** @var Opportunity $secondDataCollectionPhase */
        $secondDataCollectionPhase = $secondDataCollectionBuilder->getInstance();
        $secondDataCollectionPhase->name = 'Complementação documental';
        $secondDataCollectionPhase->save(true);

        $complementaryFieldName = $secondDataCollectionPhase->registrationFieldConfigurations[0]->fieldName ?? null;
        $this->assertNotNull($complementaryFieldName, 'A fase complementar deve expor o fieldName do campo configurado.');

        $context = [
            'firstPhase' => $firstPhase,
            'initialFieldName' => $initialFieldName,
            'evaluationPhase' => $evaluationConfiguration->opportunity->refreshed(),
            'evaluationConfiguration' => $evaluationConfiguration->refreshed(),
            'secondDataCollectionPhase' => $secondDataCollectionPhase->refreshed(),
            'complementaryFieldName' => $complementaryFieldName,
            'documentField' => $opportunityBuilder->getField('documento-obrigatorio', $firstPhase),
            'documentFieldName' => $opportunityBuilder->getFieldName('documento-obrigatorio', $firstPhase),
        ];

        $data = [
            $initialFieldName => 'Valor inicial da inscrição',
        ];

        if ($registrationData) {
            $data += $registrationData($context);
        }

        $registration = $this->registrationDirector->createSentRegistration($firstPhase, $data);
        $registration->setStatusToApproved(true);
        $registration = $registration->refreshed();

        $phasesModule = $this->app->modules['OpportunityPhases'];

        /** @var Registration $evaluationRegistration */
        $evaluationRegistration = $phasesModule->createPhaseRegistration($context['evaluationPhase'], $registration);
        $evaluationRegistration->send(false);
        $evaluationRegistration = $evaluationRegistration->refreshed();

        $evaluateRegistration($evaluationPhaseBuilder, $evaluationRegistration, $context);
        $evaluationRegistration = $evaluationRegistration->refreshed();

        /** @var Registration $secondPhaseRegistration */
        $secondPhaseRegistration = $phasesModule->createPhaseRegistration($context['secondDataCollectionPhase'], $evaluationRegistration);
        $secondPhaseRegistration->$complementaryFieldName = 'Valor complementar da inscrição';
        $secondPhaseRegistration->save(true);
        $secondPhaseRegistration = $secondPhaseRegistration->refreshed();

        $controller = $this->app->controller('Registration');
        $controller->action = 'exportPDF';
        $controller->requestedEntity = $registration;
        $this->app->view->controller = $controller;

        $methodReflection = new ReflectionMethod($controller, 'getPDFHistoryItems');
        $methodReflection->setAccessible(true);
        $historyItems = $methodReflection->invoke($controller, $registration);

        $this->assertCount(3, $historyItems, 'O histórico do PDF deve conter coleta inicial, avaliação e coleta complementar.');
        $this->assertSame('data_collection', $historyItems[0]['type']);
        $this->assertSame('evaluation', $historyItems[1]['type']);
        $this->assertSame('data_collection', $historyItems[2]['type']);

        $html = $this->app->view->partialRender(
            'registration/pdf',
            [
                'registration' => $registration,
                'historyItems' => $historyItems,
            ],
            false
        );

        return [
            'registration' => $registration,
            'evaluationRegistration' => $evaluationRegistration,
            'secondPhaseRegistration' => $secondPhaseRegistration,
            'historyItems' => $historyItems,
            'html' => $html,
            'context' => $context,
        ];
    }

    private function assertHtmlOrder(string $html, string $first, string $second, string $third): void
    {
        $firstPosition = strpos($html, $first);
        $secondPosition = strpos($html, $second);
        $thirdPosition = strpos($html, $third);

        $this->assertNotFalse($firstPosition, sprintf('O trecho "%s" deve estar presente no HTML.', $first));
        $this->assertNotFalse($secondPosition, sprintf('O trecho "%s" deve estar presente no HTML.', $second));
        $this->assertNotFalse($thirdPosition, sprintf('O trecho "%s" deve estar presente no HTML.', $third));
        $this->assertTrue(
            $firstPosition < $secondPosition && $secondPosition < $thirdPosition,
            'O PDF deve manter a ordem cronológica entre coleta inicial, avaliação e coleta complementar.'
        );
    }
}

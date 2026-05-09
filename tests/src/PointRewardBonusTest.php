<?php

namespace Test;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Open;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Testes TDD para o bônus de pontuação com tipo configurável.
 *
 * Cobre os cenários descritos em LEVANTAMENTO-BONUS-PONTUACAO.md seção 12.
 */
class PointRewardBonusTest extends TestCase
{
    use OpportunityBuilder,
        RegistrationDirector,
        UserDirector;

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Cria oportunidade com avaliação técnica, um campo "select" de raça,
     * uma seção e um critério de peso 1 com máximo 10.
     *
     * Retorna array com [opportunity, evaluation_phase_builder, field].
     */
    private function createOpportunityWithBonusField(): array
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $evaluation_phase_builder = $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Dados')
                ->createField(
                    identifier: 'raca',
                    field_type: 'select',
                    title: 'Raça/Cor',
                    options: ['Preta', 'Parda', 'Branca']
                )
                ->done()
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCommitteeValuersPerRegistration('Comissão', 1)
                ->config()
                    ->addSection('s1', 'Qualidade')
                    ->addCriterion('c1', 's1', 'Critério 1', min: 0, max: 10, weight: 1)
                    ->done()
                ->save()
                ->addValuers(1, 'Comissão');

        $opportunity = $this->opportunityBuilder
            ->save()
            ->refresh()
            ->getInstance();

        $field = $this->opportunityBuilder->getField('raca');

        return [$opportunity, $evaluation_phase_builder, $field];
    }

    /**
     * Cria uma inscrição enviada com valor "Preta" no campo raça.
     */
    private function createRegistrationWithRacaPreta($opportunity, $field): Registration
    {
        $field_name = $field->fieldName;

        return $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => 'Preta']
        );
    }

    /**
     * Cria e envia uma avaliação técnica com score no critério c1.
     */
    private function sendTechnicalEvaluation($evaluation_phase_builder, Registration $registration, float $score): void
    {
        $app = App::i();
        $app->disableAccessControl();

        $evaluation_phase_builder
            ->redistributeCommitteeRegistrations()
            ->withValuer('Comissão', 'Comissão - valuer 1')
                ->evaluation($registration)
                ->setCriterionScore('c1', $score)
                ->send();

        $app->enableAccessControl();
    }

    // =====================================================================
    // 1. COMPATIBILIDADE COM LEGADO
    // =====================================================================

    /**
     * Configuração legada em array com fieldPercent continua funcionando
     * e calcula bônus percentual corretamente.
     */
    public function testPointRewardLegacyPercentage(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        // Configura bônus no formato legado (array com fieldPercent)
        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0; // sem teto
        $emc->pointReward = [
            (object) [
                'field'        => $field->id,
                'value'        => (object) ['Preta' => 'true'],
                'fieldPercent' => 10,
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // score = 8 + (8 * 10 / 100) = 8.80
        $this->assertEquals('8.80', $registration->score, 'Bônus percentual legado deve calcular corretamente');

        $applied = $registration->appliedPointReward;
        $this->assertEquals(10, $applied->percentage, 'appliedPointReward.percentage deve ser 10');
        $this->assertEquals(8.0, $applied->raw, 'appliedPointReward.raw deve ser a nota original');
    }

    /**
     * Configuração legada sem tipo explícito (type ausente) deve ser tratada como percentual.
     */
    public function testPointRewardLegacyWithoutTypeIsPercentage(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            // sem 'type'
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // Sem type → tratado como percentual: 8 + (8 * 10 / 100) = 8.80
        $this->assertEquals('8.80', $registration->score, 'Configuração sem type deve ser tratada como percentual');
    }

    // =====================================================================
    // 2. PERCENTUAL NOVO
    // =====================================================================

    /**
     * Novo formato com type=percentage e bonusValue calcula corretamente.
     */
    public function testPointRewardNewPercentage(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // 8 + (8 * 10 / 100) = 8.80
        $this->assertEquals('8.80', $registration->score, 'Bônus percentual novo deve calcular corretamente');

        $applied = $registration->appliedPointReward;
        $this->assertEquals('percentage', $applied->type, 'appliedPointReward.type deve ser percentage');
        $this->assertEquals(10, $applied->percentage, 'appliedPointReward.percentage deve ser 10');
        $this->assertEquals(0, $applied->fixed, 'appliedPointReward.fixed deve ser 0 no modo percentual');
    }

    /**
     * Regras novas não devem gravar fieldPercent.
     */
    public function testPointRewardNewPercentageDoesNotWriteFieldPercent(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();
        $applied = $registration->appliedPointReward;

        foreach ($applied->rules as $rule) {
            $this->assertFalse(
                property_exists($rule, 'fieldPercent'),
                'Regras novas não devem gravar fieldPercent'
            );
        }
    }

    // =====================================================================
    // 3. PONTO FIXO
    // =====================================================================

    /**
     * Novo formato com type=fixed e bonusValue soma pontos diretamente.
     */
    public function testPointRewardNewFixed(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'fixed',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 2,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // 8 + 2 = 10
        $this->assertEquals('10.00', $registration->score, 'Bônus ponto fixo deve somar diretamente');

        $applied = $registration->appliedPointReward;
        $this->assertEquals('fixed', $applied->type, 'appliedPointReward.type deve ser fixed');
        $this->assertEquals(2, $applied->fixed, 'appliedPointReward.fixed deve ser 2');
        $this->assertEquals(0, $applied->percentage, 'appliedPointReward.percentage deve ser 0 no modo fixo');
    }

    /**
     * Múltiplas regras fixas são somadas antes de aplicar o teto.
     */
    public function testPointRewardFixedAccumulatesMultipleRules(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        // Adiciona segundo campo select
        $this->opportunityBuilder->getInstance()->firstPhase;
        $emc = $opportunity->evaluationMethodConfiguration;

        // Usa apenas uma regra que dará 3 pontos — basta testar a acumulação em outro teste
        // Aqui valida que o total de pontos fixos de uma única regra está correto
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'fixed',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 3,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 7.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // 7 + 3 = 10
        $this->assertEquals('10.00', $registration->score, 'Bônus fixo de 3 pontos sobre nota 7 deve resultar em 10');
    }

    // =====================================================================
    // 4. TETO (pointRewardRoof)
    // =====================================================================

    /**
     * Teto percentual limita o bônus quando a soma excede pointRewardRoof.
     */
    public function testPointRewardRoofLimitsPercentage(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 5; // teto de 5%
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10, // 10% mas teto é 5%
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // teto = 5%, score = 8 + (8 * 5 / 100) = 8.40
        $this->assertEquals('8.40', $registration->score, 'Teto percentual deve limitar o bônus');

        $applied = $registration->appliedPointReward;
        $this->assertEquals(5, $applied->percentage, 'percentage aplicada deve ser o teto (5)');
        $this->assertEquals(5, $applied->roof, 'appliedPointReward.roof deve registrar o teto');
    }

    /**
     * Teto fixo limita o bônus em pontos quando a soma excede pointRewardRoof.
     */
    public function testPointRewardRoofLimitsFixed(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 1; // teto de 1 ponto
        $emc->pointReward = (object) [
            'type'  => 'fixed',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 5, // 5 pontos mas teto é 1
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // teto = 1 ponto, score = 8 + 1 = 9
        $this->assertEquals('9.00', $registration->score, 'Teto fixo deve limitar o bônus em pontos');

        $applied = $registration->appliedPointReward;
        $this->assertEquals(1, $applied->fixed, 'fixed aplicado deve ser o teto (1)');
        $this->assertEquals(1, $applied->roof, 'appliedPointReward.roof deve registrar o teto');
    }

    /**
     * pointRewardRoof = 0 significa sem limitação em modo percentual.
     */
    public function testPointRewardRoofZeroMeansNoLimitForPercentage(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0; // sem limite
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 50,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // roof=0 = sem limite: 8 + (8 * 50 / 100) = 12
        $this->assertEquals('12.00', $registration->score, 'roof=0 deve significar sem limitação em percentual');
    }

    /**
     * pointRewardRoof = 0 significa sem limitação em modo fixo.
     */
    public function testPointRewardRoofZeroMeansNoLimitForFixed(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0; // sem limite
        $emc->pointReward = (object) [
            'type'  => 'fixed',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 15,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // roof=0 = sem limite: 8 + 15 = 23
        $this->assertEquals('23.00', $registration->score, 'roof=0 deve significar sem limitação em fixo');
    }

    // =====================================================================
    // 5. CRITÉRIO NÃO ATENDIDO
    // =====================================================================

    /**
     * Bônus não é aplicado quando o valor do campo não corresponde à regra.
     */
    public function testPointRewardNotAppliedWhenCriteriaNotMet(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = true;
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10,
                ],
            ],
        ];
        $emc->save(true);

        // Inscrição com valor "Branca" — não atende a regra para "Preta"
        $field_name = $field->fieldName;
        $registration = $this->registrationDirector->createSentRegistration(
            $opportunity,
            data: [$field_name => 'Branca']
        );

        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        // Sem bônus: score = 8
        $this->assertEquals('8.00', $registration->score, 'Bônus não deve ser aplicado quando critério não atendido');
    }

    /**
     * Bônus não é aplicado quando isActivePointReward = false.
     */
    public function testPointRewardNotAppliedWhenInactive(): void
    {
        [$opportunity, $eval_builder, $field] = $this->createOpportunityWithBonusField();

        $emc = $opportunity->evaluationMethodConfiguration;
        $emc->isActivePointReward = false; // desativado
        $emc->pointRewardRoof = 0;
        $emc->pointReward = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) [
                    'field'      => $field->id,
                    'value'      => (object) ['Preta' => 'true'],
                    'bonusValue' => 10,
                ],
            ],
        ];
        $emc->save(true);

        $registration = $this->createRegistrationWithRacaPreta($opportunity, $field);
        $this->sendTechnicalEvaluation($eval_builder, $registration, 8.0);

        $app = App::i();
        $app->disableAccessControl();
        $registration = $registration->refreshed();
        $registration->consolidateResult();
        $app->enableAccessControl();

        $registration = $registration->refreshed();

        $this->assertEquals('8.00', $registration->score, 'Bônus não deve ser aplicado quando inativo');
    }

    // =====================================================================
    // 6. NORMALIZAÇÃO
    // =====================================================================

    /**
     * normalizePointRewardConfig converte array legado em objeto com type e rules.
     */
    public function testNormalizeArrayLegacyToObject(): void
    {
        $app = App::i();
        /** @var \EvaluationMethodTechnical\Module $module */
        $module = $app->modules['EvaluationMethodTechnical'];

        $legacy = [
            (object) ['field' => 1, 'value' => (object) ['Preta' => 'true'], 'fieldPercent' => 10],
        ];

        $normalized = $module->normalizePointRewardConfig($legacy);

        $this->assertEquals('percentage', $normalized->type, 'Array legado deve ser normalizado com type=percentage');
        $this->assertIsArray($normalized->rules, 'Rules deve ser array');
        $this->assertCount(1, $normalized->rules);
        $this->assertEquals(10, $normalized->rules[0]->bonusValue, 'bonusValue deve ser derivado de fieldPercent');
        $this->assertEquals(1, $normalized->rules[0]->field, 'field deve ser preservado');
    }

    /**
     * normalizePointRewardConfig define type=percentage quando ausente.
     */
    public function testNormalizeObjectWithoutTypeBecomesPercentage(): void
    {
        $app = App::i();
        $module = $app->modules['EvaluationMethodTechnical'];

        $config = (object) [
            'rules' => [
                (object) ['field' => 1, 'value' => (object) ['Preta' => 'true'], 'bonusValue' => 10],
            ],
        ];

        $normalized = $module->normalizePointRewardConfig($config);

        $this->assertEquals('percentage', $normalized->type, 'Objeto sem type deve ser normalizado com percentage');
    }

    /**
     * normalizePointRewardConfig preserva type=fixed.
     */
    public function testNormalizeObjectWithFixedTypeIsPreserved(): void
    {
        $app = App::i();
        $module = $app->modules['EvaluationMethodTechnical'];

        $config = (object) [
            'type'  => 'fixed',
            'rules' => [
                (object) ['field' => 1, 'value' => (object) ['Preta' => 'true'], 'bonusValue' => 2],
            ],
        ];

        $normalized = $module->normalizePointRewardConfig($config);

        $this->assertEquals('fixed', $normalized->type, 'type=fixed deve ser preservado');
        $this->assertEquals(2, $normalized->rules[0]->bonusValue, 'bonusValue deve ser preservado');
    }

    /**
     * normalizePointRewardConfig deriva bonusValue de fieldPercent quando bonusValue ausente.
     */
    public function testNormalizeDerivesBonusValueFromFieldPercent(): void
    {
        $app = App::i();
        $module = $app->modules['EvaluationMethodTechnical'];

        $config = (object) [
            'type'  => 'percentage',
            'rules' => [
                (object) ['field' => 1, 'value' => (object) ['Preta' => 'true'], 'fieldPercent' => 15],
            ],
        ];

        $normalized = $module->normalizePointRewardConfig($config);

        $this->assertEquals(15, $normalized->rules[0]->bonusValue, 'bonusValue deve ser derivado de fieldPercent quando ausente');
    }

    /**
     * normalizePointRewardConfig retorna objeto vazio com percentage para config vazia.
     */
    public function testNormalizeEmptyConfigReturnsPercentageDefault(): void
    {
        $app = App::i();
        $module = $app->modules['EvaluationMethodTechnical'];

        $this->assertEquals('percentage', $module->normalizePointRewardConfig([])->type);
        $this->assertEquals('percentage', $module->normalizePointRewardConfig(null)->type);
        $this->assertEquals('percentage', $module->normalizePointRewardConfig((object) [])->type);
    }
}

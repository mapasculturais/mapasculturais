<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\ConcurrentEndingAfter;
use Tests\Builders\PhasePeriods\Past;
use Tests\Doubles\InjectedQuotas;
use Tests\Doubles\LegacyQuotas;
use Tests\Enums\EvaluationMethods;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\UserDirector;

/**
 * Caracterização da classificação por cotas (ordenação @quota).
 *
 * Compara a implementação atual de Quotas com a referência anterior à
 * otimização de desempenho (LegacyQuotas) sobre as mesmas inscrições, em todas
 * as combinações de configuração encontradas em produção (cotas, distribuição
 * geográfica, critérios de desempate, faixas, nota de corte e vagas). A ordem
 * das inscrições e os campos calculados (quotas, usingQuota, region e
 * tiebreaker) precisam sair idênticos.
 */
class QuotasClassificationCharacterizationTest extends TestCase
{
    use OpportunityBuilder,
        UserDirector;

    private const REGION_CAPITAL = 'Capital';
    private const REGION_COASTAL = 'Litoral';
    private const REGION_INTERIOR = 'Interior';

    private const PROPONENT_PERSON = 'Pessoa Física';
    private const PROPONENT_COLLECTIVE = 'Coletivo';

    private const DISABILITIES = ['Auditiva', 'Física-motora', 'Intelectual', 'Múltipla', 'Transtorno do Espectro Autista', 'Visual', 'Outras'];

    private const SEEDS = [11 => 60, 23 => 150, 37 => 37];

    private static int $nextRegistrationId = 900000;

    private int $firstPhaseId;
    private int $phaseId;

    /** @var array<string, string> identificador => fieldName */
    private array $fields = [];

    private function createTechnicalOpportunity(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->setVacancies(20)
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Past)
                ->enableQuotaQuestion()
                ->save()
                ->createStep('Informações')
                ->createOwnerField('raca', 'raca', 'Raça/Cor', required: false)
                ->createOwnerField('pessoaDeficiente', 'pessoaDeficiente', 'Pessoa com Deficiência', required: false)
                ->createField('regiao', 'select', 'Região', required: false, options: [self::REGION_CAPITAL, self::REGION_COASTAL, self::REGION_INTERIOR])
                ->createField('prioridade', 'select', 'Prioridade', required: false, options: ['Alta', 'Media', 'Baixa'])
                ->createField('idade', 'number', 'Idade', required: false)
                ->createField('nascimento', 'date', 'Data de nascimento', required: false)
                ->createField('areas', 'checkboxes', 'Áreas', required: false, options: ['Música', 'Teatro', 'Dança'])
                ->createField('aceite', 'checkbox', 'Aceite', required: false)
                ->save()
                ->done();

        $this->opportunityBuilder
            ->save()
            ->addEvaluationPhase(EvaluationMethods::technical)
                ->setEvaluationPeriod(new ConcurrentEndingAfter)
                ->setCutoffScore(0)
                ->save()
                ->config()
                    ->addSection('s-1', 'Seção 1')
                    ->addSection('s-2', 'Seção 2')
                    ->addCriterion('c-1', 's-1', 'Critério 1', 0, 10, 1)
                    ->addCriterion('c-2', 's-1', 'Critério 2', 0, 10, 2)
                    ->addCriterion('c-3', 's-2', 'Critério 3', 0, 10, 1)
                    ->done()
                ->save()
                ->done()
            ->save();

        $opportunity = $this->opportunityBuilder->getInstance()->refreshed();
        $this->firstPhaseId = $opportunity->id;

        foreach ($opportunity->allPhases as $phase) {
            $evaluation_method = $phase->evaluationMethodConfiguration;
            if ($evaluation_method && $evaluation_method->type->id === 'technical') {
                $this->phaseId = $phase->id;
            }
        }

        foreach (['raca', 'pessoaDeficiente', 'regiao', 'prioridade', 'idade', 'nascimento', 'areas', 'aceite'] as $identifier) {
            $this->fields[$identifier] = $this->opportunityBuilder->getFieldName($identifier, $opportunity);
        }
    }

    /**
     * Combinações de configuração exercitadas.
     *
     * Chaves aceitas: vacancies, ranges ([label => limite]), cutoffScore,
     * considerQuotasInGeneralList, quotas ([título, vagas, campo, valores, tipo de proponente]),
     * geo ([divisão, campo, [região => vagas], tipo de proponente]), tiebreakers,
     * proponentTypes (gera inscrições com tipo de proponente).
     */
    private function cases(): array
    {
        $f = $this->fields;

        $black = ['Pessoas Negras', 5, 'raca', ['Preta', 'Parda']];
        $indigenous = ['Indígenas', 1, 'raca', ['Indígena']];
        $disability = ['PCD', 2, 'pessoaDeficiente', self::DISABILITIES];

        $geo_by_field = ['field', 'regiao', [self::REGION_CAPITAL => 8, self::REGION_COASTAL => 5, self::REGION_INTERIOR => 3]];
        $geo_by_agent = ['geoRegiao', 'geo', [self::REGION_CAPITAL => 6, self::REGION_COASTAL => 6, self::REGION_INTERIOR => 4]];

        $tb_select = ['criterionType' => $f['prioridade'], 'preferences' => ['Alta']];
        $tb_number = ['criterionType' => $f['idade'], 'preferences' => 'largest'];
        $tb_date = ['criterionType' => $f['nascimento'], 'preferences' => 'smallest'];
        $tb_checkboxes = ['criterionType' => $f['areas'], 'preferences' => ['Teatro']];
        $tb_checkbox = ['criterionType' => $f['aceite'], 'preferences' => 'marked'];
        $tb_submission = ['criterionType' => 'submissionDate', 'preferences' => 'smallest'];
        $tb_criterion = ['criterionType' => 'criterion', 'preferences' => 'c-2'];
        $tb_section = ['criterionType' => 'sectionCriteria', 'preferences' => 's-1'];
        $tb_agent_field = ['criterionType' => $f['raca'], 'preferences' => ['Preta']];

        $ranges = ['Longa Metragem' => 6, 'Curta Metragem' => 14];

        return [
            'sem configuração' => [],
            'desempate por campo de seleção' => ['tiebreakers' => [$tb_select]],
            'desempate por campo numérico' => ['tiebreakers' => [$tb_number]],
            'desempate por campo de data' => ['tiebreakers' => [$tb_date]],
            'desempate por múltipla escolha' => ['tiebreakers' => [$tb_checkboxes]],
            'desempate por checkbox' => ['tiebreakers' => [$tb_checkbox]],
            'desempate por data de envio' => ['tiebreakers' => [$tb_submission]],
            'desempate por critério' => ['tiebreakers' => [$tb_criterion]],
            'desempate por seção de critérios' => ['tiebreakers' => [$tb_section]],
            'desempate por campo do agente' => ['tiebreakers' => [$tb_agent_field]],
            'desempate com vários critérios' => ['tiebreakers' => [$tb_select, $tb_number, $tb_submission]],
            'cota com uma regra' => ['quotas' => [$black]],
            'cotas, considerando a ampla concorrência' => ['quotas' => [$black, $indigenous, $disability], 'considerQuotasInGeneralList' => true],
            'cotas, sem considerar a ampla concorrência' => ['quotas' => [$black, $indigenous, $disability], 'considerQuotasInGeneralList' => false],
            'cotas e desempate' => ['quotas' => [$black, $disability], 'tiebreakers' => [$tb_select, $tb_criterion]],
            'região por campo' => ['geo' => $geo_by_field],
            'região pelo agente' => ['geo' => $geo_by_agent],
            'cotas, região e desempate por tipo de proponente' => [
                'proponentTypes' => true,
                'quotas' => [[...$black, self::PROPONENT_PERSON], [...$disability, self::PROPONENT_PERSON]],
                'geo' => [...$geo_by_field, self::PROPONENT_PERSON],
                'tiebreakers' => [$tb_number],
            ],
            'faixas' => ['ranges' => $ranges],
            'faixas e cotas' => ['ranges' => $ranges, 'quotas' => [$black, $disability]],
            'faixas e região' => ['ranges' => $ranges, 'geo' => $geo_by_field],
            'faixas, cotas, região e desempate' => ['ranges' => $ranges, 'quotas' => [$black, $indigenous, $disability], 'geo' => $geo_by_field, 'tiebreakers' => [$tb_select, $tb_submission]],
            'nota de corte com cotas e região' => ['cutoffScore' => 60, 'quotas' => [$black, $disability], 'geo' => $geo_by_field],
            'vagas acima do número de inscrições' => ['vacancies' => 500, 'quotas' => [$black, $disability], 'geo' => $geo_by_field],
            'sem vagas' => ['vacancies' => 0, 'quotas' => [$black]],
        ];
    }

    private function applyConfiguration(array $case): void
    {
        $app = App::i();
        $repo = $app->repo('Opportunity');

        /** @var Opportunity $first_phase */
        $first_phase = $repo->find($this->firstPhaseId);
        $first_phase->vacancies = $case['vacancies'] ?? 20;
        $first_phase->registrationRanges = array_map(
            fn($label, $limit) => ['label' => $label, 'limit' => $limit, 'value' => 0],
            array_keys($case['ranges'] ?? []),
            array_values($case['ranges'] ?? [])
        );
        $first_phase->considerQuotasInGeneralList = $case['considerQuotasInGeneralList'] ?? null;
        $first_phase->save(true);

        $rules = [];
        foreach ($case['quotas'] ?? [] as $quota) {
            [$title, $vacancies, $field, $values, $proponent_type] = $quota + [4 => null];
            $rules[] = [
                'title' => $title,
                'vacancies' => $vacancies,
                'fields' => [($proponent_type ?? 'default') => ['fieldName' => $this->fields[$field], 'eligibleValues' => $values]],
            ];
        }

        $geo = ['distribution' => [], 'geoDivision' => null, 'fields' => []];
        if ($case['geo'] ?? false) {
            [$division, $field, $distribution, $proponent_type] = $case['geo'] + [3 => null];
            $geo = [
                'distribution' => $distribution,
                'geoDivision' => $division,
                'fields' => [($proponent_type ?? 'default') => $field === 'geo' ? 'geo' : $this->fields[$field]],
            ];
        }

        $phase = $repo->find($this->phaseId);
        $evaluation_method_configuration = $phase->evaluationMethodConfiguration;
        $evaluation_method_configuration->cutoffScore = $case['cutoffScore'] ?? 0;
        $evaluation_method_configuration->quotaConfiguration = json_decode(json_encode(['rules' => $rules]));
        $evaluation_method_configuration->geoQuotaConfiguration = json_decode(json_encode($geo));
        $evaluation_method_configuration->tiebreakerCriteriaConfiguration = json_decode(json_encode(array_map(
            fn($tiebreaker, $index) => ['id' => $index + 1, 'name' => 'Critério ' . ($index + 1)] + $tiebreaker,
            $case['tiebreakers'] ?? [],
            array_keys($case['tiebreakers'] ?? [])
        )));
        $evaluation_method_configuration->save(true);

        $app->em->clear();
    }

    /**
     * Gera inscrições sintéticas no formato de Quotas::loadRegistrationsForQuotaSorting,
     * com muitos empates de nota para exercitar os critérios de desempate.
     *
     * @return array{0: object[], 1: array} inscrições e médias por critério
     */
    private function generateRegistrations(int $seed, int $count, array $case): array
    {
        mt_srand($seed);

        $pick = fn(array $options) => $options[mt_rand(0, count($options) - 1)];
        $f = $this->fields;

        $range_options = array_merge(array_keys($case['ranges'] ?? []), ['', null, 'Faixa inexistente']);
        $region_options = [self::REGION_CAPITAL, self::REGION_CAPITAL, self::REGION_COASTAL, self::REGION_INTERIOR, '', 'Fora do estado'];

        $registrations = [];
        $evaluation_data = [];

        for ($i = 0; $i < $count; $i++) {
            $id = ++self::$nextRegistrationId;

            $score = mt_rand(0, 9) < 3
                ? mt_rand(0, 10000) / 100
                : $pick([null, 0.0, 10.0, 25.5, 40.0, 40.0, 55.25, 70.0, 70.0, 85.0, 100.0]);

            $race = $pick(['Preta', 'Parda', 'Indígena', 'Branca', 'Amarela', '']);
            $disability = $pick([[], ['Nenhuma'], [$pick(self::DISABILITIES)], [$pick(self::DISABILITIES), $pick(self::DISABILITIES)]]);
            $region = $pick($region_options);

            $registration = (object) [
                'id' => $id,
                'number' => "on-{$id}",
                'score' => $score,
                'range' => $range_options ? $pick($range_options) : null,
                'proponentType' => ($case['proponentTypes'] ?? false) ? $pick([self::PROPONENT_PERSON, self::PROPONENT_PERSON, self::PROPONENT_COLLECTIVE, null]) : null,
                'consolidatedResult' => is_null($score) ? '0' : (string) $score,
                'status' => $pick([1, 1, 1, 10, 3, 8, 2, 0]),
                'eligible' => mt_rand(0, 3) > 0,
                'sentTimestamp' => '2026-07-0' . mt_rand(1, 3) . ' 10:00:00',
                'agentsData' => [
                    'owner' => [
                        'raca' => $race,
                        'pessoaDeficiente' => $disability,
                        'geoRegiao' => $pick($region_options),
                    ],
                ],
                'appliedForQuota' => $pick([true, false, null]),
                '_firstPhaseEnriched' => true,
            ];

            // campos do agente às vezes chegam só pelo agentsData
            if (mt_rand(0, 1)) {
                $registration->{$f['raca']} = $race;
                $registration->{$f['pessoaDeficiente']} = $disability;
            }

            $registration->{$f['regiao']} = $region;
            $registration->{$f['prioridade']} = $pick(['Alta', 'Media', 'Baixa', null]);
            $registration->{$f['idade']} = $pick([18, 30, 30, 45, null]);
            $registration->{$f['nascimento']} = $pick(['1980-01-01', '1990-05-05', '1990-05-05', null]);
            $registration->{$f['areas']} = $pick([[], ['Música'], ['Teatro'], ['Música', 'Teatro'], ['Dança']]);
            $registration->{$f['aceite']} = $pick([true, false, null]);

            $registrations[] = $registration;

            if (mt_rand(0, 4)) {
                $evaluation_data[$id] = [
                    'c-1' => $pick([5, 7.5, 7.5, 10]),
                    'c-2' => $pick([2, 6, 6, 8]),
                    'c-3' => $pick([1, 9, 9]),
                ];
            }
        }

        return [$registrations, $evaluation_data];
    }

    /**
     * Resume a saída da classificação: ordem das inscrições, marcação de cotista
     * e os campos calculados por inscrição.
     */
    private function classify(string $class_name, array $registrations, array $evaluation_data): array
    {
        $app = App::i();
        $app->rcache->deleteAll();

        $quotas = (new $class_name($this->phaseId))->inject($registrations, $evaluation_data);
        $order = $quotas->getRegistrationsOrderByScoreConsideringQuotas();

        $fields = $quotas->registrationFields;
        ksort($fields);

        return [
            'order' => array_map(fn($registration) => $registration->id, $order),
            'usingQuota' => array_map(fn($registration) => $registration->usingQuota ?? null, $order),
            'registrationFields' => $fields,
        ];
    }

    function testClassificationMatchesLegacyImplementation()
    {
        $this->createTechnicalOpportunity();

        $computed = ['quotas' => 0, 'usingQuota' => 0, 'region' => 0, 'tiebreaker' => 0];

        foreach ($this->cases() as $case_name => $case) {
            $this->applyConfiguration($case);

            foreach (self::SEEDS as $seed => $count) {
                [$registrations, $evaluation_data] = $this->generateRegistrations($seed, $count, $case);

                $expected = $this->classify(LegacyQuotas::class, $registrations, $evaluation_data);
                $actual = $this->classify(InjectedQuotas::class, $registrations, $evaluation_data);

                $context = "Caso '{$case_name}', semente {$seed} ({$count} inscrições)";

                $this->assertCount($count, $actual['order'], "{$context}: certificando que todas as inscrições são classificadas");
                $this->assertSame($expected['order'], $actual['order'], "{$context}: certificando que a ordem de classificação não mudou");
                $this->assertSame($expected['usingQuota'], $actual['usingQuota'], "{$context}: certificando que as inscrições classificadas como cotistas não mudaram");
                $this->assertSame($expected['registrationFields'], $actual['registrationFields'], "{$context}: certificando que os campos calculados (cotas, região e desempate) não mudaram");

                foreach ($actual['registrationFields'] as $registration_fields) {
                    foreach (array_keys($computed) as $key) {
                        if (!empty($registration_fields[$key])) {
                            $computed[$key]++;
                        }
                    }
                }
            }
        }

        // garante que o cenário de fato exercitou cotas, região e desempate
        foreach ($computed as $key => $total) {
            $this->assertGreaterThan(0, $total, "Certificando que algum caso calculou o campo '{$key}'");
        }
    }
}

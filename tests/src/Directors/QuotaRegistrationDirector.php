<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\Director;
use Tests\Builders\OpportunityBuilder;
use Tests\Traits\RegistrationBuilder;

class QuotaRegistrationDirector extends Director
{
    use RegistrationBuilder;

    // Constantes do Domínio
    const RANGE_1 = 'Curta Metragem';
    const RANGE_2 = 'Longa Metragem';

    const REGION_CAPITAL = 'Região da Capital';
    const REGION_COASTAL = 'Região Litorânea';
    const REGION_INTERIOR = 'Região do Interior';

    const RACE_WHITE = 'Branca';
    const RACE_BLACK = 'Preta';
    const RACE_BROWN = 'Parda';
    const RACE_INDIGENOUS = 'Indígena';
    const RACE_OTHER = 'Outra';

    function __construct(
        protected OpportunityBuilder $opportunityBuilder,
        protected RegistrationDirector $registrationDirector
    ) {
        parent::__construct();
    }

    /**
     * Gera uma única inscrição com valores padrão ou sobrescritos.
     */
    private function generateRegistration(Opportunity $opportunity, array $overrides = [], bool $use_range = false, bool $use_quota = false, bool $use_region = false): array
    {
        $faker_ranges = [self::RANGE_1, self::RANGE_2];
        $faker_regions = [self::REGION_CAPITAL, self::REGION_COASTAL, self::REGION_INTERIOR];
        $faker_races = [self::RACE_WHITE, self::RACE_WHITE, self::RACE_WHITE, self::RACE_BLACK, self::RACE_BROWN, self::RACE_OTHER]; // Peso maior para branca para simular realidade estatística comum

        $default = [
            'score' => mt_rand(0, 1000) / 10, // Gera float ex: 85.5
        ];

        if ($use_range) {
            $default['range'] = $faker_ranges[array_rand($faker_ranges)];
        }

        if ($use_quota) {
            $default['raca'] = $faker_races[array_rand($faker_races)];
            $default['pessoaDeficiente'] = [];
        }

        if ($use_region) {
            $default['region'] = $faker_regions[array_rand($faker_regions)];
        }

        return array_merge($default, $overrides);
    }

    /**
     * Gera um lote de inscrições com base em configurações.
     */
    private function generateBatch(Opportunity $opportunity, int $quantity, array $rules, bool $use_range = false, bool $use_quota = false, bool $use_region = false): array
    {
        $batch = [];
        for ($i = 0; $i < $quantity; $i++) {
            $batch[] = $this->generateRegistration($opportunity, $rules, $use_range, $use_quota, $use_region);
        }
        return $batch;
    }

    /**
     * Gera "Ruído": inscritos aleatórios, muitos com nota baixa, para encher a lista.
     */
    private function generateNoise(Opportunity $opportunity, int $quantity, float $max_score = 50.0, bool $use_range = false, bool $use_quota = false, bool $use_region = false): array
    {
        $batch = [];
        for ($i = 0; $i < $quantity; $i++) {
            // Gera notas aleatórias, com boa chance de serem abaixo da nota de corte (40)
            // e limitando a nota máxima para não interferir nas inscrições principais
            $score = (mt_rand(0, 100) < 50) ? mt_rand(0, 399) / 10 : mt_rand(400, (int)($max_score * 10)) / 10;
            $batch[] = $this->generateRegistration($opportunity, ['score' => $score], $use_range, $use_quota, $use_region);
        }
        return $batch;
    }

    /**
     * Cria inscrições a partir de uma lista de dados
     */
    public function createRegistrationsFromData(array $registrations_data): array
    {
        $registrations = [];
        $opportunity = $this->opportunityBuilder->getInstance();

        // Obtém os field_names dos campos de cota
        $field_raca = $this->opportunityBuilder->getFieldName('raca', $opportunity);
        $field_pessoa_deficiente = $this->opportunityBuilder->getFieldName('pessoaDeficiente', $opportunity);
        $field_regiao = $this->opportunityBuilder->getFieldName('regiao', $opportunity);

        foreach ($registrations_data as $data) {
            $registration_data = $data;
            
            if (!empty($data['raca']) && $field_raca) {
                $registration_data[$field_raca] = $data['raca'];
            }

            if (!empty($data['pessoaDeficiente']) && $field_pessoa_deficiente) {
                $registration_data[$field_pessoa_deficiente] = $data['pessoaDeficiente'];
            }

            if (!empty($data['region']) && $field_regiao) {
                $registration_data[$field_regiao] = $data['region'];
            }

            $registration_data['appliedForQuota'] = true;

            $registration = $this->registrationDirector->createSentRegistration($opportunity, $registration_data);
            
            $registrations[] = $registration;
        }

        return $registrations;
    }

    /**
     * Cenário 1: "Caminho Feliz" - Faixas apenas
     * Há candidatos qualificados suficientes para preencher todas as faixas.
     * Escala reduzida (7 Curta + 3 Longa vagas) para testes mais rápidos.
     */
    public function idealRangesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // 1. Garante CURTA (7 vagas) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_1,
            'score' => 90.0
        ], use_range: true));

        // 2. Garante LONGA (3 vagas) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_2,
            'score' => 95.0  // Nota mais alta para garantir que sejam selecionadas
        ], use_range: true));

        // 3. Ruído (nota máxima 50, abaixo de corte 40 em parte) - não devem ser classificados
        $list = array_merge($list, $this->generateNoise($opportunity, 5, 50.0, use_range: true));
        
        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 2: Falha na Faixa (Orçamento preso)
     * Sobram candidatos em Curta, mas FALTAM candidatos qualificados em Longa.
     * Escala reduzida (7 Curta + 3 Longa vagas): 1 Longa qualificada, 9 Curta classificadas.
     */
    public function restrictedRangesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // CURTA: Superpopulação (15 inscritos para 7 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 15, [
            'range' => self::RANGE_1,
            'score' => 90.0
        ], use_range: true));

        // LONGA: Escassez (1 qualificado para 3 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'score' => 80.0
        ], use_range: true));

        // LONGA: Inscritos desclassificados (< 40) - não podem levar vaga
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'score' => 30.0
        ], use_range: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 3: "Caminho Feliz" - Cotas apenas (SEM faixas e SEM vagas por território)
     * Há candidatos qualificados suficientes para preencher todas as cotas.
     * Total esperado selecionado: 15 (5 cotistas + 10 ampla concorrência)
     */
    public function idealQuotasScenario(Opportunity $opportunity): array
    {
        $list = [];

        // 1. Garante Pessoas Negras (3 vagas) - Preta ou Parda com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_BLACK,
            'score' => 90.0
        ], use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_BROWN,
            'score' => 90.0
        ], use_quota: true));

        // 2. Garante Indígenas (1 vaga) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_INDIGENOUS,
            'score' => 85.0
        ], use_quota: true));

        // 3. Garante PCD (1 vaga) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'score' => 80.0
        ], use_quota: true));

        // 4. Adiciona candidatos de ampla concorrência (10 vagas restantes)
        $list = array_merge($list, $this->generateBatch($opportunity, 20, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'score' => 95.0
        ], use_quota: true));

        // 5. Adiciona Ruído (Gente reprovada e excedente)
        $list = array_merge($list, $this->generateNoise($opportunity, 10, 50.0, use_quota: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 4: Falha nas Cotas
     * FALTAM candidatos qualificados para algumas cotas.
     * Resultado esperado: As vagas remanescentes das cotas não preenchidas podem ser utilizadas em outras cotas
     * ou na ampla concorrência, dependendo da configuração.
     */
    public function restrictedQuotasScenario(Opportunity $opportunity): array
    {
        $list = [];

        // Pessoas Negras: Superpopulação (6 inscritos para 3 vagas) - Preta ou Parda
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_BLACK,
            'score' => 90.0
        ], use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_BROWN,
            'score' => 90.0
        ], use_quota: true));

        // Indígenas: Escassez (Apenas 0 qualificados para 1 vaga)
        // Não cria nenhum indígena qualificado

        // Indígenas: Existem outros inscritos, mas todos DESCLASSIFICADOS (< 40)
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_INDIGENOUS,
            'score' => 30.0
        ], use_quota: true));

        // PCD: Escassez (Apenas 0 qualificados para 1 vaga)
        // Não cria nenhum PCD qualificado

        // PCD: Existem outros inscritos, mas todos DESCLASSIFICADOS (< 40)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'pessoaDeficiente' => ['Visual'], // Array com deficiência = é PCD
            'score' => 25.0
        ], use_quota: true));

        // Ampla concorrência: Superpopulação
        $list = array_merge($list, $this->generateBatch($opportunity, 25, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [], // Array vazio = não é PCD
            'score' => 95.0
        ], use_quota: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 5: "Caminho Feliz" - Vagas por território apenas (SEM faixas e SEM cotas)
     * Há candidatos qualificados suficientes para preencher todas as regiões.
     * Total esperado selecionado: 60 (30 Capital + 18 Litoral + 12 Interior)
     */
    public function idealTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // 1. Garante Capital (30 vagas) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 35, [
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_region: true));

        // 2. Garante Litoral (18 vagas) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 22, [
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_region: true));

        // 3. Garante Interior (12 vagas) com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 15, [
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_region: true));

        // 4. Adiciona Ruído (Gente reprovada e excedente)
        $list = array_merge($list, $this->generateNoise($opportunity, 28, 50.0, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 6: Falha Regional (Vagas por território)
     * O Interior não tem inscritos suficientes.
     * As vagas do Interior devem migrar para Capital/Litoral (Prioridade 3 é flexível).
     * Total esperado selecionado: 60 (30 Capital + 18 Litoral + 12 Interior, mas Interior não preenche)
     */
    public function restrictedTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // CAPITAL e LITORAL: Superlotados com notas altas
        $list = array_merge($list, $this->generateBatch($opportunity, 40, [
            'region' => self::REGION_CAPITAL,
            'score' => 95.0
        ], use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 25, [
            'region' => self::REGION_COASTAL,
            'score' => 90.0
        ], use_region: true));

        // INTERIOR: Apenas 3 inscritos qualificados (meta era 12 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'region' => self::REGION_INTERIOR,
            'score' => 60.0
        ], use_region: true));

        // INTERIOR: Existem outros inscritos, mas todos DESCLASSIFICADOS (< 40)
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'region' => self::REGION_INTERIOR,
            'score' => 30.0
        ], use_region: true));

        // Adiciona ruído geral APENAS de Capital e Litoral para evitar mais inscrições do Interior
        // Garante que o ruído não gere mais inscrições do Interior qualificadas
        $list = array_merge($list, $this->generateBatch($opportunity, 12, [
            'region' => self::REGION_CAPITAL,
            'score' => 35.0
        ], use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 12, [
            'region' => self::REGION_COASTAL,
            'score' => 35.0
        ], use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 7: "Caminho Feliz" - Faixas e Cotas (SEM vagas por território)
     * Há candidatos qualificados suficientes para preencher todas as faixas e todas as cotas.
     * Total esperado selecionado: 100 (30 Longa + 70 Curta)
     * Cotas: 20 Negras (20%), 5 Indígenas (5%), 2 PCD (2%)
     *
     * Escala reduzida (7 Curta + 3 Longa, cotas 2+1+1) para testes mais rápidos.
     * - Longa (3): ~1 Negras, ~0.5 Indígenas, ~0.3 PCD
     * - Curta (7): ~1.4 Negras, ~0.35 Indígenas, ~0.14 PCD
     */
    public function idealRangesAndQuotasScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (7 vagas) =====
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'score' => 85.0
        ], use_range: true, use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'score' => 85.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'score' => 80.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Física-motora'],
            'score' => 75.0
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'score' => 90.0
        ], use_range: true, use_quota: true));

        // ===== LONGA METRAGEM (3 vagas) =====
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'score' => 88.0
        ], use_range: true, use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'score' => 88.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'score' => 82.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'score' => 78.0
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'score' => 92.0
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateNoise($opportunity, 5, 50.0, use_range: true, use_quota: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 8: Falha em Faixas e Cotas combinadas
     * FALTAM candidatos qualificados para algumas cotas em algumas faixas.
     * 
     * Regras de priorização:
     * 1. Faixas não podem variar (30 Longa + 70 Curta é fixo)
     * 2. Cotas têm prioridade e podem usar vagas de outras cotas se necessário
     * 
     * Escala reduzida (7 Curta + 3 Longa): Curta completa, Longa com escassez de Indígenas e PCD.
     */
    public function restrictedRangesAndQuotasScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (7 vagas) - COMPLETA =====
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'score' => 85.0
        ], use_range: true, use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'score' => 85.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'score' => 80.0
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'score' => 75.0
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'score' => 90.0
        ], use_range: true, use_quota: true));

        // ===== LONGA METRAGEM (3 vagas) - COM ESCASSEZ =====
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'score' => 88.0
        ], use_range: true, use_quota: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'score' => 88.0
        ], use_range: true, use_quota: true));
        
        // ESCASSEZ: 0 Indígenas e 0 PCD qualificados para LONGA
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'score' => 30.0  // Abaixo da nota de corte
        ], use_range: true, use_quota: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'score' => 25.0  // Abaixo da nota de corte
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'score' => 92.0
        ], use_range: true, use_quota: true));

        $list = array_merge($list, $this->generateNoise($opportunity, 4, 50.0, use_range: true, use_quota: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 9: "Caminho Feliz" - Faixas e Vagas por Território (SEM cotas)
     * Escala reduzida: 10 vagas (3 Longa + 7 Curta), regiões 5 Capital, 3 Litoral, 2 Interior.
     */
    public function idealRangesAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (7 vagas) =====
        // Notas: Capital 90, Litoral 88 (2) e 86 (1), Interior 87 — garante 4 Capital + 2 Litoral + 1 Interior nos 7
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_1,
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'region' => self::REGION_COASTAL,
            'score' => 86.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'region' => self::REGION_INTERIOR,
            'score' => 87.0
        ], use_range: true, use_region: true));

        // ===== LONGA METRAGEM (3 vagas) =====
        // Capital e Litoral altos; Interior 90 — garante 1 Litoral em Longa (2 Capital + 1 Interior ou 2 Capital + 1 Litoral)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'region' => self::REGION_CAPITAL,
            'score' => 92.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'region' => self::REGION_COASTAL,
            'score' => 89.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'region' => self::REGION_INTERIOR,
            'score' => 90.0
        ], use_range: true, use_region: true));

        $list = array_merge($list, $this->generateNoise($opportunity, 5, 50.0, use_range: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 10: Falha em Faixas e Vagas por Território combinadas
     * Escala reduzida (3 Longa + 7 Curta): Longa com escassez de candidatos do Interior.
     */
    public function restrictedRangesAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (7 vagas) - COMPLETA =====
        // Mesmas notas do ideal: 2 Litoral 88, 1 Litoral 86, Interior 87 — garante 2 Litoral nos 7 de Curta
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_1,
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'region' => self::REGION_COASTAL,
            'score' => 86.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'region' => self::REGION_INTERIOR,
            'score' => 87.0
        ], use_range: true, use_region: true));

        // ===== LONGA METRAGEM (3 vagas) - COM ESCASSEZ REGIONAL =====
        // Litoral 89 para garantir 1 Litoral em Longa (2 Capital + 1 Litoral = 3) → total 3 Litoral
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'region' => self::REGION_CAPITAL,
            'score' => 92.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'region' => self::REGION_COASTAL,
            'score' => 89.0
        ], use_range: true, use_region: true));
        
        // ESCASSEZ: 1 candidato do Interior para LONGA (meta era 2)
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'region' => self::REGION_INTERIOR,
            'score' => 60.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'region' => self::REGION_INTERIOR,
            'score' => 30.0  // Abaixo da nota de corte
        ], use_range: true, use_region: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_region: true));
        
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'region' => self::REGION_COASTAL,
            'score' => 82.0
        ], use_range: true, use_region: true));

        $list = array_merge($list, $this->generateNoise($opportunity, 4, 50.0, use_range: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 11: "Caminho Feliz" - Cotas e Vagas por Território (SEM faixas)
     * Escala reduzida: 10 vagas, cotas 2+1+1, regiões 5 Capital, 3 Litoral, 2 Interior.
     */
    public function idealQuotasAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // 1. Capital (5 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 80.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Física-motora'],
            'region' => self::REGION_CAPITAL,
            'score' => 75.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_quota: true, use_region: true));

        // 2. Litoral (3 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'region' => self::REGION_COASTAL,
            'score' => 75.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_quota: true, use_region: true));

        // 3. Interior (2 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_quota: true, use_region: true));

        $list = array_merge($list, $this->generateNoise($opportunity, 5, 50.0, use_quota: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 12: Falha em Cotas e Vagas por Território combinadas (SEM faixas)
     * Escala reduzida (10 vagas): Capital e Litoral completos, Interior com escassez.
     */
    public function restrictedQuotasAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CAPITAL (5 vagas) - COMPLETA =====
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 80.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'region' => self::REGION_CAPITAL,
            'score' => 75.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_quota: true, use_region: true));

        // ===== LITORAL (3 vagas) - COMPLETA =====
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'region' => self::REGION_COASTAL,
            'score' => 75.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_quota: true, use_region: true));

        // ===== INTERIOR (2 vagas) - COM ESCASSEZ (apenas 1 qualificado) =====
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_INTERIOR,
            'score' => 30.0  // Abaixo da nota de corte
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'region' => self::REGION_INTERIOR,
            'score' => 25.0  // Abaixo da nota de corte
        ], use_quota: true, use_region: true));

        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 35.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 35.0
        ], use_quota: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 13: "Caminho Feliz" - Faixas, Cotas e Vagas por Território simultaneamente
     * Há candidatos qualificados suficientes para preencher todas as faixas, 
     * todas as cotas e todas as regiões.
     * Total esperado selecionado: 100
     * 
     * Distribuição esperada:
     * - Faixas: 30 Longa + 70 Curta (fixo)
     * - Cotas: 20 Negras (20%), 5 Indígenas (5%), 2 PCD (2%)
     * - Território: 50 Capital, 30 Litoral, 20 Interior
     */
    public function idealRangesQuotasAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (70 vagas) =====
        
        // 1. Garante Cotas e Regiões para CURTA - Capital (proporcionalmente ~35 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Física-motora'],
            'region' => self::REGION_CAPITAL,
            'score' => 75.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 20, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_range: true, use_quota: true, use_region: true));

        // 2. Garante Cotas e Regiões para CURTA - Litoral (proporcionalmente ~21 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_COASTAL,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'region' => self::REGION_COASTAL,
            'score' => 75.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 15, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));

        // 3. Garante Cotas e Regiões para CURTA - Interior (proporcionalmente ~14 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_INTERIOR,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 10, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));

        // ===== LONGA METRAGEM (30 vagas) =====
        
        // 4. Garante Cotas e Regiões para LONGA - Capital (proporcionalmente ~15 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 92.0
        ], use_range: true, use_quota: true, use_region: true));

        // 5. Garante Cotas e Regiões para LONGA - Litoral (proporcionalmente ~9 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_COASTAL,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 6, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 90.0
        ], use_range: true, use_quota: true, use_region: true));

        // 6. Garante Cotas e Regiões para LONGA - Interior (proporcionalmente ~6 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_INTERIOR,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'region' => self::REGION_INTERIOR,
            'score' => 78.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));

        // 7. Adiciona Ruído (Gente reprovada e excedente)
        $list = array_merge($list, $this->generateNoise($opportunity, 50, 50.0, use_range: true, use_quota: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }

    /**
     * Cenário 14: Falha em Faixas, Cotas e Vagas por Território combinadas
     * FALTAM candidatos qualificados para algumas cotas em algumas faixas e regiões.
     * 
     * Regras de priorização:
     * 1. Faixas não podem variar (30 Longa + 70 Curta é fixo)
     * 2. Cotas têm prioridade sobre vagas por território e devem tentar ser garantidas dentro de cada faixa
     * 3. Vagas por território têm menor prioridade e podem ser redistribuídas
     * 
     * Neste cenário:
     * - Curta tem candidatos suficientes para todas as cotas e regiões
     * - Longa tem escassez de Indígenas e PCD qualificados no Interior
     * - As vagas de cota não preenchidas em Longa podem ser redistribuídas entre outras cotas
     * - As vagas do Interior em Longa podem ser redistribuídas para outras regiões
     */
    public function restrictedRangesQuotasAndTerritoryVacanciesScenario(Opportunity $opportunity): array
    {
        $list = [];

        // ===== CURTA METRAGEM (70 vagas) - COMPLETA =====
        
        // 1. Garante todas as cotas e regiões para CURTA - Capital (~35 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Física-motora'],
            'region' => self::REGION_CAPITAL,
            'score' => 75.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 20, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 90.0
        ], use_range: true, use_quota: true, use_region: true));

        // 2. Garante todas as cotas e regiões para CURTA - Litoral (~21 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_COASTAL,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Auditiva'],
            'region' => self::REGION_COASTAL,
            'score' => 75.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 15, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));

        // 3. Garante cotas e região para CURTA - Interior (~14 vagas)
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 3, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_INTERIOR,
            'score' => 80.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 10, [
            'range' => self::RANGE_1,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_INTERIOR,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));

        // ===== LONGA METRAGEM (30 vagas) - COM ESCASSEZ =====
        
        // 4. Garante Cotas e Regiões para LONGA - Capital (há candidatos suficientes)
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_CAPITAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 4, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_CAPITAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_CAPITAL,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 10, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 92.0
        ], use_range: true, use_quota: true, use_region: true));

        // 5. Garante Cotas e Regiões para LONGA - Litoral (há candidatos suficientes)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_COASTAL,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 1, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_COASTAL,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 6, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 90.0
        ], use_range: true, use_quota: true, use_region: true));

        // 6. ESCASSEZ: Apenas alguns candidatos do Interior para LONGA (meta era ~6)
        // Negros para Interior em Longa (há alguns)
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BLACK,
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_BROWN,
            'region' => self::REGION_INTERIOR,
            'score' => 88.0
        ], use_range: true, use_quota: true, use_region: true));
        
        // 7. ESCASSEZ: Apenas 0 Indígenas qualificados para LONGA no Interior
        // Não cria nenhum indígena qualificado para Longa no Interior
        
        // 8. ESCASSEZ: Apenas 0 PCD qualificados para LONGA no Interior
        // Não cria nenhum PCD qualificado para Longa no Interior
        
        // 9. Indígenas DESCLASSIFICADOS para LONGA no Interior
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_INDIGENOUS,
            'region' => self::REGION_INTERIOR,
            'score' => 30.0  // Abaixo da nota de corte
        ], use_range: true, use_quota: true, use_region: true));
        
        // 10. PCD DESCLASSIFICADOS para LONGA no Interior
        $list = array_merge($list, $this->generateBatch($opportunity, 2, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => ['Visual'],
            'region' => self::REGION_INTERIOR,
            'score' => 25.0  // Abaixo da nota de corte
        ], use_range: true, use_quota: true, use_region: true));

        // 11. Mais candidatos de Capital e Litoral para LONGA (para preencher as vagas do Interior)
        $list = array_merge($list, $this->generateBatch($opportunity, 8, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 85.0
        ], use_range: true, use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 5, [
            'range' => self::RANGE_2,
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 82.0
        ], use_range: true, use_quota: true, use_region: true));

        // 12. Adiciona Ruído (somente de Capital e Litoral para evitar mais candidatos do Interior)
        $list = array_merge($list, $this->generateBatch($opportunity, 30, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_CAPITAL,
            'score' => 35.0
        ], use_quota: true, use_region: true));
        $list = array_merge($list, $this->generateBatch($opportunity, 20, [
            'raca' => self::RACE_WHITE,
            'pessoaDeficiente' => [],
            'region' => self::REGION_COASTAL,
            'score' => 35.0
        ], use_quota: true, use_region: true));

        shuffle($list);
        return $this->createRegistrationsFromData($list);
    }
}

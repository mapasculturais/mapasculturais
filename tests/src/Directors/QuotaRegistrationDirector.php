<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use Tests\Abstract\Director;
use Tests\Traits\RegistrationBuilder;

class QuotaRegistrationDirector extends Director
{
    use RegistrationBuilder;

    // Constantes do Domínio
    const FAIXA_CURTA = 'Curta Metragem';
    const FAIXA_LONGA = 'Longa Metragem';

    const REGIAO_CAPITAL = 'Região da Capital';
    const REGIAO_LITORAL = 'Região Litorânea';
    const REGIAO_INTERIOR = 'Região do Interior';

    const RACA_BRANCA = 'Branca';
    const RACA_NEGRA = 'Negra';
    const RACA_INDIGENA = 'Indígena';
    const RACA_OUTRA = 'Outra';

    /**
     * Gera uma única inscrição com valores padrão ou sobrescritos.
     */
    private function gerarInscricao(Opportunity $opportunity, array $overrides = []): array
    {
        $fakerFaixas = [self::FAIXA_CURTA, self::FAIXA_LONGA];
        $fakerRegioes = [self::REGIAO_CAPITAL, self::REGIAO_LITORAL, self::REGIAO_INTERIOR];
        $fakerRacas = [self::RACA_BRANCA, self::RACA_BRANCA, self::RACA_BRANCA, self::RACA_NEGRA, self::RACA_OUTRA]; // Peso maior para branca para simular realidade estatística comum

        $padrao = [
            'score' => mt_rand(0, 1000) / 10, // Gera float ex: 85.5
            'range' => $fakerFaixas[array_rand($fakerFaixas)],
        ];

        return array_merge($padrao, $overrides);
    }

    /**
     * Gera um lote de inscrições com base em configurações.
     */
    private function gerarLote(Opportunity $opportunity, int $quantidade, array $regras): array
    {
        $lote = [];
        for ($i = 0; $i < $quantidade; $i++) {
            $lote[] = $this->gerarInscricao($opportunity, $regras);
        }
        return $lote;
    }

    /**
     * Gera "Ruído": inscritos aleatórios, muitos com nota baixa, para encher a lista.
     */
    private function gerarRuido(Opportunity $opportunity, int $quantidade, float $nota_maxima = 50.0): array
    {
        $fakerFaixas = [self::FAIXA_CURTA, self::FAIXA_LONGA];
        $lote = [];
        for ($i = 0; $i < $quantidade; $i++) {
            // Gera notas aleatórias, com boa chance de serem abaixo da nota de corte (40)
            // e limitando a nota máxima para não interferir nas inscrições principais
            $nota = (mt_rand(0, 100) < 50) ? mt_rand(0, 399) / 10 : mt_rand(400, (int)($nota_maxima * 10)) / 10;
            // Garante que o ruído também tenha uma faixa definida
            $faixa = $fakerFaixas[array_rand($fakerFaixas)];
            $lote[] = $this->gerarInscricao($opportunity, ['score' => $nota, 'range' => $faixa]);
        }
        return $lote;
    }

    /**
     * Cria inscrições a partir de uma lista de dados
     */
    public function createRegistrationsFromData(Opportunity $opportunity, array $inscricoes_data): array
    {
        $registrations = [];
        
        foreach ($inscricoes_data as $data) {
            $registration = $this->registrationBuilder
                ->reset($opportunity)
                ->setRange($data['range'] ?? null)
                ->fillRequiredProperties()
                ->save()
                ->send()
                ->getInstance();
            
            $this->setRegistrationData($registration, $data, save: true);
            
            $registrations[] = $registration;
        }
        
        return $registrations;
    }

    /**
     * Cenário 1: "Caminho Feliz" - Faixas apenas
     * Há candidatos qualificados suficientes para preencher todas as faixas.
     */
    public function cenarioIdealFaixas(Opportunity $opportunity): array
    {
        $lista = [];

        // 1. Garante CURTA (70 vagas) com notas altas
        $lista = array_merge($lista, $this->gerarLote($opportunity, 80, [
            'range' => self::FAIXA_CURTA,
            'score' => 90.0
        ]));

        // 2. Garante LONGA (30 vagas) com notas altas. Criados mais do que o necessário para garantir que temos candidatos suficientes
        $lista = array_merge($lista, $this->gerarLote($opportunity, 50, [
            'range' => self::FAIXA_LONGA,
            'score' => 95.0  // Nota mais alta para garantir que sejam selecionadas
        ]));

        // 3. Adiciona Ruído (Gente reprovada e excedente) - mas com notas mais baixas para garantir que não sejam classificadas em vez das de Longa (nota máxima 50)
        $lista = array_merge($lista, $this->gerarRuido($opportunity, 50, 50.0));

        shuffle($lista);
        return $this->createRegistrationsFromData($opportunity, $lista);
    }

    /**
     * Cenário 2: Falha na Faixa (Orçamento preso)
     * Sobram candidatos em Curta, mas FALTAM candidatos qualificados em Longa.
     * Resultado esperado: 70 Curtas selecionados + (X < 30) Longas selecionados.
     * As vagas de Longa NÃO podem ser preenchidas por Curtas.
     */
    public function cenarioRestritoFaixas(Opportunity $opportunity): array
    {
        $lista = [];

        // CURTA: Superpopulação (150 inscritos para 70 vagas)
        $lista = array_merge($lista, $this->gerarLote($opportunity, 150, [
            'range' => self::FAIXA_CURTA,
            'score' => 90.0
        ]));

        // LONGA: Escassez (Apenas 10 qualificados para 30 vagas)
        $lista = array_merge($lista, $this->gerarLote($opportunity, 10, [
            'range' => self::FAIXA_LONGA,
            'score' => 80.0
        ]));
        
        // LONGA: Existem outros inscritos, mas todos DESCLASSIFICADOS (< 40)
        // Eles existem, mas não podem levar a vaga.
        $lista = array_merge($lista, $this->gerarLote($opportunity, 20, [
            'range' => self::FAIXA_LONGA,
            'score' => 30.0
        ]));

        shuffle($lista);
        return $this->createRegistrationsFromData($opportunity, $lista);
    }

    protected function setRegistrationData($registration, array $data, bool $save = false, $flush = true): void
    {
        foreach($data as $key => $value) {
            if(in_array($key, ['sentTimestamp', 'createTimestamp', 'updateTimestamp']) && is_string($value)) {
                $value = new \DateTime($value);
            }
            $registration->$key = $value;
        }

        if($save) {
            $registration->save($flush);
        }

        $field_to_column = [
            'score' => 'score', 
            'consolidatedResult' => 'consolidated_result'
        ];

        $app = App::i();
        $conn = $app->em->getConnection();
        foreach($field_to_column as $field => $column) {
            if(isset($data[$field])) {
                $value = $data[$field];
                $conn->executeQuery("UPDATE registration SET $column = :val WHERE id = :id", [
                    'id' => $registration->id, 
                    'val' => $value
                ]);
            }
        }
    }
}


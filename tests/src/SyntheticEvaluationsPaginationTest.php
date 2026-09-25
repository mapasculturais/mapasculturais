<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Regressão: isenções sintéticas (selo) devem ocupar o restante da página
 * atual depois das avaliações reais — nunca reaparecer em páginas seguintes.
 * Causava loop infinito no export de planilha de avaliações (SpreadsheetJob).
 */
class SyntheticEvaluationsPaginationTest extends TestCase
{
    /**
     * Replica a lógica de recorte usada em Opportunity::apiFindEvaluations.
     *
     * @param list<array> $real
     * @param list<array> $synthetic
     * @return list<array>
     */
    private function mergeForPage(array $real, array $synthetic, int $limit, int $page): array
    {
        $real_total = count($real);
        // Simula API: página só devolve o slice real correspondente
        $offset = ($page - 1) * $limit;
        $evaluations = array_slice($real, $offset, $limit);
        $remaining = $limit - count($evaluations);

        if ($remaining > 0) {
            $synth_start = max(0, $offset - $real_total);
            if ($synth_start < count($synthetic)) {
                $evaluations = array_merge(
                    $evaluations,
                    array_slice($synthetic, $synth_start, $remaining)
                );
            }
        }

        return $evaluations;
    }

    public function testSyntheticsOnlyOnFirstPageWhenRealsFit(): void
    {
        $real = [['id' => 1], ['id' => 2]];
        $synthetic = [['id' => 's1']];

        $p1 = $this->mergeForPage($real, $synthetic, 50, 1);
        $p2 = $this->mergeForPage($real, $synthetic, 50, 2);

        $this->assertCount(3, $p1);
        $this->assertSame('s1', $p1[2]['id']);
        $this->assertCount(0, $p2);
    }

    public function testSyntheticsSpillToNextPage(): void
    {
        $real = [['id' => 1], ['id' => 2]];
        $synthetic = [['id' => 's1'], ['id' => 's2'], ['id' => 's3']];

        // limit 3: página 1 = 2 reais + 1 sintética; página 2 = 2 sintéticas
        $p1 = $this->mergeForPage($real, $synthetic, 3, 1);
        $p2 = $this->mergeForPage($real, $synthetic, 3, 2);
        $p3 = $this->mergeForPage($real, $synthetic, 3, 3);

        $this->assertSame([1, 2, 's1'], array_column($p1, 'id'));
        $this->assertSame(['s2', 's3'], array_column($p2, 'id'));
        $this->assertCount(0, $p3);
    }

    public function testOldBugWouldRepeatSyntheticsOnEveryPage(): void
    {
        $real = [['id' => 1]];
        $synthetic = [['id' => 's1']];
        $limit = 50;

        // Bug antigo: merge(real_page, ALL_synthetics) em toda página
        $buggy_pages = [];
        for ($page = 1; $page <= 3; $page++) {
            $offset = ($page - 1) * $limit;
            $evaluations = array_slice($real, $offset, $limit);
            $evaluations = array_merge($evaluations, $synthetic);
            $buggy_pages[] = count($evaluations);
        }

        $this->assertSame([2, 1, 1], $buggy_pages, 'sem o fix, páginas >1 nunca esvaziam');

        $fixed_p2 = $this->mergeForPage($real, $synthetic, $limit, 2);
        $this->assertCount(0, $fixed_p2);
    }
}

<?php

namespace Tests;

use DomainException;
use OpportunityAppealPhase\Services\AppealTechnicalCorrectionCalculator;

class AppealTechnicalCorrectionCalculatorTest extends Abstract\TestCase
{
    private array $criteria = [
        ['id' => 'criterion-1', 'title' => 'Critério 1', 'min' => 0, 'max' => 10, 'weight' => 2],
        ['id' => 'criterion-2', 'title' => 'Critério 2', 'min' => 0, 'max' => 5, 'weight' => 1],
    ];

    public function testBuildCorrectedDataPreservesUnrecognizedFields(): void
    {
        $calculator = new AppealTechnicalCorrectionCalculator();

        $result = $calculator->buildCorrectedEvaluationData(
            $this->criteria,
            ['criterion-1' => 4.0, 'criterion-2' => 3.0, 'obs' => 'parecer', 'viability' => 'valid'],
            ['criterion-1' => 8.5]
        );

        $this->assertSame(8.5, $result['after']['criterion-1']);
        $this->assertSame(3.0, $result['after']['criterion-2']);
        $this->assertSame('parecer', $result['after']['obs']);
        $this->assertSame('valid', $result['after']['viability']);
        $this->assertSame([
            'criterion-1' => ['before' => 4.0, 'after' => 8.5],
        ], $result['changedCriteria']);
        $this->assertSame(11.0, $result['beforeResult']);
        $this->assertSame(20.0, $result['afterResult']);
    }

    public function testRejectsRemovedCriterion(): void
    {
        $calculator = new AppealTechnicalCorrectionCalculator();

        $this->expectException(DomainException::class);
        $calculator->buildCorrectedEvaluationData(
            $this->criteria,
            ['criterion-1' => 4.0, 'criterion-2' => 3.0],
            ['removed-criterion' => 8.0]
        );
    }

    public function testRejectsScoreOutsideCurrentCriterionRange(): void
    {
        $calculator = new AppealTechnicalCorrectionCalculator();

        $this->expectException(DomainException::class);
        $calculator->buildCorrectedEvaluationData(
            $this->criteria,
            ['criterion-1' => 4.0, 'criterion-2' => 3.0],
            ['criterion-2' => 5.1]
        );
    }

    public function testCalculatesConsolidatedPreviewUsingAllSentEvaluations(): void
    {
        $calculator = new AppealTechnicalCorrectionCalculator();

        $result = $calculator->calculateConsolidatedResult($this->criteria, [
            ['criterion-1' => 8.0, 'criterion-2' => 4.0],
            ['criterion-1' => 6.0, 'criterion-2' => 2.0],
        ]);

        $this->assertSame(17.0, $result);
    }

    public function testScorePreviewReappliesExistingPercentageBonusWithoutMutation(): void
    {
        $calculator = new AppealTechnicalCorrectionCalculator();

        $preview = $calculator->calculateScorePreview(17.0, (object) [
            'type' => 'percentage',
            'percentage' => 10,
            'fixed' => 0,
            'roof' => 20,
            'rules' => [['field' => ['id' => 1]]],
        ], true);

        $this->assertSame(18.7, $preview['score']);
        $this->assertSame(17.0, $preview['appliedPointReward']['raw']);
        $this->assertSame(10.0, $preview['appliedPointReward']['percentage']);
        $this->assertTrue($preview['eligible']);
    }
}

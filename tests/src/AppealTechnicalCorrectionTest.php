<?php

namespace Tests;

use DomainException;
use OpportunityAppealPhase\Entities\AppealTechnicalCorrection;
use OpportunityAppealPhase\Entities\AppealTechnicalCorrectionItem;

class AppealTechnicalCorrectionTest extends Abstract\TestCase
{
    public function testAppliedCorrectionBecomesImmutable(): void
    {
        $correction = new AppealTechnicalCorrection();
        $correction->replaceDraft(
            'Critério reconhecido no recurso',
            ['criteria' => [['id' => 'criterion-1', 'min' => 0, 'max' => 10]]]
        );

        $correction->markApplied([
            'consolidatedResult' => 7.5,
            'score' => 8.0,
            'eligible' => true,
        ], [
            'consolidatedResult' => 8.5,
            'score' => 9.0,
            'eligible' => true,
        ]);

        $this->assertSame(AppealTechnicalCorrection::STATUS_APPLIED, $correction->status);

        $this->expectException(DomainException::class);
        $correction->replaceDraft('Tentativa posterior', []);
    }

    public function testNoChangeConfirmationIsFinalAndRequiresReason(): void
    {
        $correction = new AppealTechnicalCorrection();

        $this->expectException(DomainException::class);
        $correction->markConfirmedNoChange('   ', [
            'consolidatedResult' => 7.5,
            'score' => 8.0,
            'eligible' => true,
        ]);
    }

    public function testCorrectionItemKeepsFullSnapshotsAndOnlyNumericDeltas(): void
    {
        $item = new AppealTechnicalCorrectionItem();
        $item->captureChange(
            ['criterion-1' => 6.0, 'criterion-2' => 7.0, 'obs' => 'original'],
            ['criterion-1' => 8.0, 'criterion-2' => 7.0, 'obs' => 'original'],
            6.5,
            7.5,
            ['criterion-1' => ['before' => 6.0, 'after' => 8.0]]
        );

        $this->assertSame(6.0, $item->beforeEvaluationData['criterion-1']);
        $this->assertSame(8.0, $item->afterEvaluationData['criterion-1']);
        $this->assertSame('original', $item->afterEvaluationData['obs']);
        $this->assertSame(
            ['criterion-1' => ['before' => 6.0, 'after' => 8.0]],
            $item->changedCriteria
        );
    }
}

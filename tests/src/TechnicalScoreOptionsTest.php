<?php

namespace Tests;

use EvaluationMethodTechnical\ScoreOptions;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

$score_options_file = __DIR__ . '/../../src/modules/EvaluationMethodTechnical/ScoreOptions.php';
if (is_file($score_options_file)) {
    require_once $score_options_file;
}

class TechnicalScoreOptionsTest extends TestCase
{
    protected function setUp(): void
    {
        $this->assertTrue(class_exists(ScoreOptions::class), 'A escala de notas fixas deve existir');
    }

    public function testParsesAndSortsCommaSeparatedScores(): void
    {
        $this->assertSame([0.0, 5.0, 10.0], ScoreOptions::parse('10, 0,5'));
    }

    public function testAcceptsZeroAndExplicitDecimals(): void
    {
        $this->assertSame([0.0], ScoreOptions::parse('0'));
        $this->assertSame([0.0, 2.5, 5.0], ScoreOptions::parse([0, '2.5', 5]));
    }

    /** @dataProvider invalidOptions */
    public function testRejectsInvalidScales($options): void
    {
        $this->expectException(InvalidArgumentException::class);
        ScoreOptions::parse($options);
    }

    public static function invalidOptions(): array
    {
        return array_map(fn($value) => [$value], [
            '', [], '0,,5', '0,5,', '0,-1,5', '0,cinco,10', '0,5,5.0',
            [0, INF], [0, NAN], [false, 5], null, ['0', []], '0,1e2',
        ]);
    }

    public function testNormalizesLimitsWithoutChangingWeight(): void
    {
        $criteria = ScoreOptions::normalizeCriteria('fixed', [
            (object) ['id' => 'a', 'scoreOptions' => '10,5,0', 'max' => 999, 'weight' => 2],
            (object) ['id' => 'b', 'scoreOptions' => [2.5, 7.5], 'weight' => 0.5],
        ]);
        $this->assertSame([0.0, 5.0, 10.0], $criteria[0]->scoreOptions);
        $this->assertSame(0.0, $criteria[0]->min);
        $this->assertSame(10.0, $criteria[0]->max);
        $this->assertSame(2, $criteria[0]->weight);
        $this->assertSame(2.5, $criteria[1]->min);
        $this->assertSame(7.5, $criteria[1]->max);
    }

    public function testPreservesLegacyTypedCriteria(): void
    {
        $criteria = [(object) ['id' => 'a', 'min' => 0, 'max' => 10, 'weight' => 1]];
        $this->assertSame($criteria, ScoreOptions::normalizeCriteria(null, $criteria));
        $this->assertSame($criteria, ScoreOptions::normalizeCriteria('typed', $criteria));
    }

    /** @dataProvider invalidModes */
    public function testRejectsUnknownMode($mode): void
    {
        $this->expectException(InvalidArgumentException::class);
        ScoreOptions::normalizeCriteria($mode, []);
    }

    public static function invalidModes(): array
    {
        return [['slider'], [0], [false], ['0']];
    }

    public function testChecksMembershipWithoutRounding(): void
    {
        $options = [0, 2.5, 5, 10];
        $this->assertTrue(ScoreOptions::contains($options, 0));
        $this->assertTrue(ScoreOptions::contains($options, '5.0'));
        $this->assertTrue(ScoreOptions::contains($options, 2.5));
        foreach ([null, '', false, 'cinco', 7.5, 5.00001, INF] as $value) {
            $this->assertFalse(ScoreOptions::contains($options, $value));
        }
    }

    public function testComparesScalesByCriterionRegardlessOfOrdering(): void
    {
        $old = [(object) ['id' => 'a', 'scoreOptions' => [0, 5, 10], 'title' => 'Antes']];
        $same = [(object) ['id' => 'a', 'scoreOptions' => '10,5,0', 'title' => 'Depois']];
        $changed = [(object) ['id' => 'a', 'scoreOptions' => [0, 10]]];
        $this->assertFalse(ScoreOptions::hasChanged('fixed', $old, 'fixed', $same));
        $this->assertTrue(ScoreOptions::hasChanged('fixed', $old, 'fixed', $changed));
        $this->assertTrue(ScoreOptions::hasChanged('fixed', $old, 'typed', $old));
        $this->assertTrue(ScoreOptions::hasChanged(null, [], 'fixed', $old));
        $this->assertFalse(ScoreOptions::hasChanged(null, [], 'typed', $old));
        $this->assertTrue(ScoreOptions::hasChanged('fixed', $old, 'fixed', []));
    }
}

<?php

namespace Tests;

use EvaluationMethodTechnical\Module;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

if (!defined('LANGUAGES_PATH')) {
    define('LANGUAGES_PATH', __DIR__ . '/../../src/translations/');
}

// Permite executar os testes de domínio sem inicializar banco ou aplicação.
spl_autoload_register(function(string $class) {
    $root = __DIR__ . '/../../src/';
    foreach (['MapasCulturais\\' => 'core/', 'EvaluationMethodTechnical\\' => 'modules/EvaluationMethodTechnical/'] as $prefix => $path) {
        if (str_starts_with($class, $prefix)) {
            $file = $root . $path . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
        }
    }
});

class TechnicalFixedScoresConfiguration extends EvaluationMethodConfiguration
{
    public $scoreMode = 'fixed';
    public $criteria = [];
    public $sections = [];
    public $enableViability = 'false';
    public $pointReward;
    public $isActivePointReward;
    public $pointRewardRoof;
    public $quota;
    public $geoQuotaConfiguration;
    public $tiebreakerCriteriaConfiguration;
    public $quotaConfiguration;
    public $cutoffScore;
    public $opportunity;
    public $evaluationMethod;
    public bool $started = false;
    protected $__enableMagicGetterHook = false;
    protected $__enableMagicSetterHook = false;

    public function __construct()
    {
        $this->_type = 'technical';
        $this->opportunity = (object) ['firstPhase' => (object) ['enableQuotasQuestion' => false, 'considerQuotasInGeneralList' => false]];
        $this->criteria = [(object) [
            'id' => 'a', 'sid' => 's', 'title' => 'Critério', 'min' => 0, 'max' => 10,
            'weight' => 2, 'scoreOptions' => [0, 5, 10],
        ]];
        $this->sections = [(object) ['id' => 's', 'name' => 'Seção']];
    }

    public function isNew() { return false; }
    public function hasStartedEvaluations(): bool { return $this->started; }
}

class TechnicalFixedScoresTest extends TestCase
{
    private Module $module;
    private TechnicalFixedScoresConfiguration $configuration;

    protected function setUp(): void
    {
        $this->module = (new ReflectionClass(Module::class))->newInstanceWithoutConstructor();
        $this->configuration = new TechnicalFixedScoresConfiguration();
        $this->configuration->evaluationMethod = $this->module;
        // A tradução não é o objeto destes testes de domínio.
        $GLOBALS['i18n']['default'] ??= new class {
            public function translate($text) { return $text; }
        };
    }

    public function testRejectsScoreWithinLimitsButOutsideOptions(): void
    {
        $this->assertNotEmpty($this->module->getValidationErrors($this->configuration, ['a' => 7.5, 'obs' => 'Parecer']));
    }

    public function testRejectsMissingFixedCriterion(): void
    {
        $this->assertNotEmpty($this->module->getValidationErrors($this->configuration, ['obs' => 'Parecer']));
    }

    public function testAcceptsEveryConfiguredScoreIncludingZero(): void
    {
        foreach ([0, 5, 10, '5.0'] as $score) {
            $this->assertSame([], $this->module->getValidationErrors($this->configuration, ['a' => $score, 'obs' => 'Parecer']));
        }
    }

    public function testPreservesTypedDecimalScores(): void
    {
        $this->configuration->scoreMode = null;
        $this->assertSame([], $this->module->getValidationErrors($this->configuration, ['a' => 7.5, 'obs' => 'Parecer']));
    }

    public function testPreservesLegacyJsonCriteriaDuringNormalization(): void
    {
        $this->configuration->scoreMode = 'typed';
        $criteria = json_encode($this->configuration->criteria);
        $this->configuration->criteria = $criteria;
        $this->module->normalizeScoreCriteria($this->configuration);
        $this->assertSame($criteria, $this->configuration->criteria);
    }

    public function testUsesEachCriterionOwnScale(): void
    {
        $this->configuration->criteria[] = (object) [
            'id' => 'b', 'title' => 'Outro', 'min' => 0, 'max' => 5, 'weight' => 1, 'scoreOptions' => [0, 2.5, 5],
        ];
        $this->assertSame([], $this->module->getValidationErrors($this->configuration, ['a' => 5, 'b' => 2.5, 'obs' => 'Parecer']));
        $this->assertNotEmpty($this->module->getValidationErrors($this->configuration, ['a' => 2.5, 'b' => 5, 'obs' => 'Parecer']));
    }

    public function testRejectsInvalidFixedScaleConfiguration(): void
    {
        $this->configuration->criteria[0]->scoreOptions = [0, 5, 5];
        $this->assertFalse($this->module->validateScoreConfiguration($this->configuration));
    }

    public function testDerivesLimitsBeforeSanitizingCriteria(): void
    {
        unset($this->configuration->criteria[0]->max);
        $this->assertTrue($this->configuration->validateCriteriaSectionsIntegrity());
        $this->configuration->sanitizeCriteriaSectionsDraft();
        $this->assertCount(1, $this->configuration->criteria);
        $this->assertSame(10.0, $this->configuration->criteria[0]->max);
    }

    public function testExportsModeAndOptionsAndImportsLegacyAsTyped(): void
    {
        $export = new \ReflectionMethod(Module::class, '_export');
        $payload = $export->invoke($this->module, $this->configuration);
        $this->assertSame('fixed', $payload['scoreMode'] ?? null);
        $this->assertSame([0, 5, 10], $payload['criteria'][0]->scoreOptions);
        $import = new \ReflectionMethod(Module::class, '_import');
        $import->invoke($this->module, $this->configuration, $payload);
        $this->assertSame('fixed', $this->configuration->scoreMode);
        unset($payload['scoreMode']);
        $import->invoke($this->module, $this->configuration, $payload);
        $this->assertSame('typed', $this->configuration->scoreMode);
    }

    public function testBlocksChangingScaleAfterEvaluationsStart(): void
    {
        $module = $this->getMockBuilder(Module::class)->disableOriginalConstructor()->onlyMethods(['persistedScoreConfiguration'])->getMock();
        $module->expects($this->once())->method('persistedScoreConfiguration')->willReturn(['fixed', $this->configuration->criteria]);
        $this->configuration->started = true;
        $this->configuration->criteria = [(object) [
            'id' => 'a', 'title' => 'Critério', 'scoreOptions' => [0, 10], 'weight' => 2,
        ]];
        $this->expectException(\MapasCulturais\Exceptions\PermissionDenied::class);
        $module->prepareScoreConfiguration($this->configuration);
    }

    public function testBlocksChangingModeAfterEvaluationsStart(): void
    {
        $module = $this->getMockBuilder(Module::class)->disableOriginalConstructor()->onlyMethods(['persistedScoreConfiguration'])->getMock();
        $module->method('persistedScoreConfiguration')->willReturn(['fixed', $this->configuration->criteria]);
        $this->configuration->started = true;
        $this->configuration->scoreMode = 'typed';
        $this->expectException(\MapasCulturais\Exceptions\PermissionDenied::class);
        $module->prepareScoreConfiguration($this->configuration);
    }

    public function testAllowsSavingSameScaleAfterEvaluationsStart(): void
    {
        $module = $this->getMockBuilder(Module::class)->disableOriginalConstructor()->onlyMethods(['persistedScoreConfiguration'])->getMock();
        $module->expects($this->once())->method('persistedScoreConfiguration')->willReturn(['fixed', $this->configuration->criteria]);
        $this->configuration->started = true;
        $module->prepareScoreConfiguration($this->configuration);
        $this->assertSame([0.0, 5.0, 10.0], $this->configuration->criteria[0]->scoreOptions);
    }

    public function testDraftRejectsFilledScoreOutsideOptions(): void
    {
        $this->assertNotEmpty($this->module->getFixedScoreValidationErrors($this->configuration, ['a' => 7.5], false));
    }

    public function testDraftAllowsMissingScoresAndZero(): void
    {
        $this->assertSame([], $this->module->getFixedScoreValidationErrors($this->configuration, [], false));
        $this->assertSame([], $this->module->getFixedScoreValidationErrors($this->configuration, ['a' => ''], false));
        $this->assertSame([], $this->module->getFixedScoreValidationErrors($this->configuration, ['a' => 0], false));
    }

    public function testSavingDraftRejectsInvalidFilledScore(): void
    {
        $evaluation = $this->getMockBuilder(\MapasCulturais\Entities\RegistrationEvaluation::class)
            ->disableOriginalConstructor()->onlyMethods(['getEvaluationMethodConfiguration', '__get'])->getMock();
        $evaluation->method('getEvaluationMethodConfiguration')->willReturn($this->configuration);
        $evaluation->method('__get')->willReturnCallback(fn($field) => $field === 'evaluationData' ? (object) ['a' => 7.5] : 0);
        $this->expectException(\MapasCulturais\Exceptions\BadRequest::class);
        $this->module->assertFixedScoreEvaluation($evaluation);
    }
}

<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Opportunity;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spreadsheets\JobTypes\Registrations;

/**
 * Regressão: campo bankFields ("Dados bancários") saía como uma única célula
 * com valores crus unidos por vírgula (ex.: "1, 380, 0001, -, 140349444, 4")
 * em vez de uma coluna por subcampo com rótulos legíveis.
 */
class SpreadsheetBankFieldsExportTest extends TestCase
{
    private array $appInstances;

    protected function setUp(): void
    {
        parent::setUp();
        $modules = dirname((new ReflectionClass(App::class))->getFileName(), 2) . '/modules/';
        require_once $modules . 'EvaluationMethodTechnical/Module.php';
        require_once $modules . 'Spreadsheets/SpreadsheetJob.php';
        require_once $modules . 'Spreadsheets/FieldParser.php';
        require_once $modules . 'Spreadsheets/JobTypes/Registrations.php';

        $this->appInstances = (new ReflectionProperty(App::class, '_instances'))->getValue();
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(App::class, '_instances'))->setValue(null, $this->appInstances);
        parent::tearDown();
    }

    public function testBatchExpandsBankFieldsWithReadableLabels(): void
    {
        $field = (object) [
            'fieldName' => 'field_283',
            'fieldType' => 'bankFields',
            'title' => 'Dados bancários',
            'config' => [],
            'displayOrder' => 2,
        ];

        $opportunity = $this->getMockBuilder(Opportunity::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegistrationFieldConfigurations', 'getSpecializedClassName', '__get'])
            ->getMock();
        $opportunity->method('getRegistrationFieldConfigurations')->willReturn([$field]);
        $opportunity->method('__get')->with('previousPhase')->willReturn(null);

        $controller = $this->getMockBuilder(OpportunityController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['apiFindRegistrations'])
            ->getMock();
        $controller->method('apiFindRegistrations')->willReturn((object) [
            'registrations' => [[
                'number' => 'mc-91105729',
                'field_283' => (object) [
                    'account_type' => '1',
                    'number' => '380',
                    'branch' => '0001',
                    'dv_branch' => '-',
                    'account_number' => '140349444',
                    'dv_account_number' => '4',
                ],
            ]],
            'count' => 1,
        ]);

        $config = [
            'module.registrationFieldTypes' => [
                'account_types' => [
                    '1' => 'Conta corrente',
                    '2' => 'Conta Poupança',
                ],
                'bank_types' => [
                    '380' => '380 - PicPay Bank',
                ],
            ],
        ];

        $app = $this->getMockBuilder(App::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['controller', 'getRegisteredMetadataByMetakey'])
            ->getMock();
        $app->method('controller')->with('opportunity')->willReturn($controller);
        $app->method('getRegisteredMetadataByMetakey')->willReturn(null);
        // App::$config é propriedade pública tipada — __get não intercepta o acesso.
        $app->config = $config;
        (new ReflectionProperty(App::class, '_instances'))->setValue(null, ['web' => $app]);

        $job = $this->getMockBuilder(Job::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $job->method('__get')->willReturnMap([
            ['owner', $opportunity],
            ['query', ['@select' => 'number,field_283']],
        ]);

        $jobType = (new ReflectionClass(Registrations::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($jobType, 'limit'))->setValue($jobType, 50);

        $batch = (new ReflectionMethod(Registrations::class, '_getBatch'))->invoke($jobType, $job);
        $row = $batch[0];

        $this->assertSame('mc-91105729', $row['number']);
        $this->assertArrayNotHasKey('field_283', $row, 'Objeto bankFields não deve permanecer na linha');
        $this->assertSame('Conta corrente', $row['field_283_account_type']);
        $this->assertSame('380 - PicPay Bank', $row['field_283_number']);
        $this->assertSame('0001', $row['field_283_branch']);
        $this->assertSame('-', $row['field_283_dv_branch']);
        $this->assertSame('140349444', $row['field_283_account_number']);
        $this->assertSame('4', $row['field_283_dv_account_number']);
    }
}

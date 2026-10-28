<?php

namespace Tests;

use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as OpportunityController;
use MapasCulturais\Entities\Job;
use MapasCulturais\Entities\Opportunity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spreadsheets\JobTypes\Registrations;

class SpreadsheetCustomTableFieldExportTest extends TestCase
{
    private array $appInstances;

    protected function setUp(): void
    {
        parent::setUp();
        // Os namespaces dos módulos são registrados pela aplicação, não pelo Composer.
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

    #[DataProvider('tableValues')]
    public function testExportsCustomTableAsReadableRows(array $columns, mixed $value, string $expected): void
    {
        $field = (object) [
            'fieldName' => 'field_100',
            'fieldType' => 'custom-table',
            'config' => ['columns' => $columns],
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
            'registrations' => [['number' => 'on-100', 'field_100' => $value]],
            'count' => 1,
        ]);

        $app = $this->getMockBuilder(App::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['controller', 'getRegisteredMetadataByMetakey', '__get'])
            ->getMock();
        $app->method('controller')->with('opportunity')->willReturn($controller);
        $app->method('getRegisteredMetadataByMetakey')->willReturn(null);
        $app->method('__get')->with('config')->willReturn([]);
        (new ReflectionProperty(App::class, '_instances'))->setValue(null, ['web' => $app]);

        $job = $this->getMockBuilder(Job::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $job->method('__get')->willReturnMap([
            ['owner', $opportunity],
            ['query', ['@select' => 'number,field_100']],
        ]);

        $jobType = (new ReflectionClass(Registrations::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty($jobType, 'limit'))->setValue($jobType, 50);
        $batch = (new ReflectionMethod(Registrations::class, '_getBatch'))->invoke($jobType, $job);

        $this->assertSame('on-100', $batch[0]['number']);
        $this->assertSame($expected, $batch[0]['field_100']);
    }

    public static function tableValues(): iterable
    {
        $columns = [
            ['name' => 'Nome', 'type' => 'text'],
            ['name' => 'Quantidade', 'type' => 'number'],
        ];

        yield 'multiple rows and numeric zero' => [$columns, [
            ['col0' => 'Maria', 'col1' => 0],
            ['col0' => 'João', 'col1' => 2],
        ], 'Nome: Maria | Quantidade: 0 || Nome: João | Quantidade: 2'];

        yield 'object rows and string zero' => [$columns, [
            (object) ['col0' => 'Maria', 'col1' => '0'],
        ], 'Nome: Maria | Quantidade: 0'];

        yield 'configured order and missing cells' => [$columns, [
            ['col1' => 3, 'col0' => 'João', '$$hashKey' => 'internal'],
            ['col1' => 0],
        ], 'Nome: João | Quantidade: 3 || Nome:  | Quantidade: 0'];

        yield 'empty table' => [$columns, [], ''];
        yield 'unanswered field' => [$columns, null, ''];
        yield 'empty placeholder rows' => [$columns, [[], ['col0' => '', 'col1' => null]], ''];

        yield 'all column types' => [[
            ['name' => 'Nome', 'type' => 'text'],
            ['name' => 'Valor', 'type' => 'number'],
            ['name' => 'E-mail', 'type' => 'email'],
            ['name' => 'CPF', 'type' => 'cpf'],
            ['name' => 'Data', 'type' => 'date'],
            ['name' => 'Categoria', 'type' => 'select'],
        ], [[
            'col0' => 'Ação cultural',
            'col1' => 12.5,
            'col2' => 'maria@example.org',
            'col3' => '001.002.003-04',
            'col4' => '2026-10-28',
            'col5' => 'Música',
        ]], 'Nome: Ação cultural | Valor: 12.5 | E-mail: maria@example.org | CPF: 001.002.003-04 | Data: 2026-10-28 | Categoria: Música'];

        yield 'existing text cleaning' => [$columns, [
            ['col0' => '<b>Maria</b>', 'col1' => 1],
        ], 'Nome: Maria | Quantidade: 1'];
    }
}

<?php

namespace Test;

use DateTime;
use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\User;
use OpportunityWorkplan\Entities\Delivery;
use OpportunityWorkplan\Entities\Goal;
use OpportunityWorkplan\Entities\Workplan;
use Spreadsheets\Module;
use Tests\Abstract\TestCase;
use Tests\Builders\PhasePeriods\Open;
use Tests\Mailer\TestTransport;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RegistrationDirector;
use Tests\Traits\UserDirector;

/**
 * Exportação de planilhas — ordenação e guarda do fluxo com plano de metas.
 *
 * Regressão produção: edital com enableWorkplan quebrava no File->save(true) do
 * SpreadsheetJob (ORMInvalidArgumentException por metadata transitória no UnitOfWork).
 * A correção limpa o EM antes de persistir o arquivo.
 *
 * Execução:
 * docker compose -f tests/docker-compose.yml exec -w /var/www mapas \
 *   /var/www/vendor/bin/phpunit /var/www/tests/SpreadsheetExportsTest.php
 */
class SpreadsheetExportsTest extends TestCase
{
    use OpportunityBuilder;
    use RegistrationDirector;
    use UserDirector;

    protected function setUp(): void
    {
        parent::setUp();

        $app = App::i();

        TestTransport::reset();
        $app->clearHooks('mailer.transport');
        $app->hook('mailer.transport', function (&$transport) {
            $transport = new TestTransport();
        });

        $app->config['mailer.from'] = 'test@mapasculturais.org';
    }

    protected function tearDown(): void
    {
        TestTransport::reset();
        parent::tearDown();
    }

    function testExportedFilesAreSortedByNewestFirst(): void
    {
        $older = (object) [
            'id' => 10,
            'createTimestamp' => new DateTime('2026-04-30 01:41:10'),
        ];
        $newer = (object) [
            'id' => 20,
            'createTimestamp' => new DateTime('2026-06-03 15:45:50'),
        ];
        $sameTimestampHigherId = (object) [
            'id' => 30,
            'createTimestamp' => new DateTime('2026-06-03 15:45:50'),
        ];

        $sorted = Module::sortExportedFilesByNewestFirst([
            $older,
            $newer,
            $sameTimestampHigherId,
        ]);

        $this->assertSame(
            [30, 20, 10],
            array_map(fn($file) => $file->id, $sorted),
            'Garantindo que os arquivos exportados sejam listados do mais recente para o mais antigo'
        );
    }

    /**
     * Garante que o padrão da correção permanece no SpreadsheetJob:
     * limpar o EntityManager antes de salvar o File da planilha.
     */
    function testSpreadsheetJobClearsEntityManagerBeforeSavingExportFile(): void
    {
        $source = file_get_contents(APPLICATION_PATH . '../src/modules/Spreadsheets/SpreadsheetJob.php');
        if ($source === false) {
            $source = file_get_contents('/var/www/src/modules/Spreadsheets/SpreadsheetJob.php');
        }

        $this->assertNotFalse($source);
        $this->assertMatchesRegularExpression(
            '/\$writer->save\(\$path\);.*?\$app->em->clear\(\);.*?\$file->save\(true\);/s',
            $source,
            'SpreadsheetJob deve limpar o EM entre writer->save e File->save para evitar flush com metadata transitória'
        );
    }

    /**
     * Guarda do fluxo de produção: oportunidade com plano de metas + job
     * registrations-spreadsheets deve gerar arquivo e e-mail de sucesso.
     */
    function testRegistrationsSpreadsheetJobSucceedsWithWorkplanMetadata(): void
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);
        $this->setUserEmail($admin, 'admin-export@example.org');

        $opportunity = $this->createOpportunityWithWorkplan($admin);
        $registration = $this->createSentRegistrationWithWorkplan($opportunity, $admin);
        $opportunity_id = $opportunity->id;

        $this->assertNotNull(
            $this->app->repo(Workplan::class)->findOneBy(['registration' => $registration->id]),
            'Inscrição de teste precisa ter plano de metas'
        );

        $owner_properties = implode(',', $this->app->config['registration.reportOwnerProperties'] ?? ['name']);

        $this->app->enqueueOrReplaceJob('registrations-spreadsheets', [
            'owner' => $opportunity,
            'authenticatedUser' => $admin,
            'extension' => 'xlsx',
            'entityClassName' => Registration::class,
            'query' => [
                '@select' => 'id,number,status,owner.{name}',
            ],
            'owner_properties' => $owner_properties,
        ]);

        $this->processJobsSafely(max_jobs: 10);

        // SpreadsheetJob faz em->clear(); recarregar o owner para ler os files.
        /** @var Opportunity $opportunity */
        $opportunity = $this->app->repo(Opportunity::class)->find($opportunity_id);
        $this->assertNotNull($opportunity);

        $files = $opportunity->getFiles('registrations-spreadsheets');
        $this->assertNotEmpty(
            $files,
            'Job de exportação com workplan deve salvar o arquivo da planilha'
        );

        $file = is_array($files) ? end($files) : $files;
        $this->assertTrue(file_exists($file->path), 'Arquivo exportado deve existir no filesystem');
        $this->assertNotNull(
            TestTransport::getLastMessage(),
            'E-mail de sucesso da exportação deve ser disparado'
        );
    }

    /**
     * executeJob() faz (int) no id md5; ids que começam com [a-f] viram 0 e quebram
     * o while ($app->executeJob()) do processJobs padrão.
     */
    private function processJobsSafely(int $max_jobs = 10, string $as_date = '2100-01-01 00:00'): void
    {
        $app = App::i();
        $current_loggedin_user = $app->user;
        $processed = 0;

        while ($processed < $max_jobs) {
            $result = $app->executeJob($as_date);
            if ($result === false) {
                break;
            }
            $processed++;
        }

        if ($current_loggedin_user && !$current_loggedin_user->is('guest')) {
            $this->login($current_loggedin_user);
        }
    }

    private function setUserEmail(User $user, string $email): void
    {
        $app = App::i();
        $app->disableAccessControl();
        $user->email = $email;
        $user->save(true);
        $app->enableAccessControl();
    }

    private function createOpportunityWithWorkplan(User $admin): Opportunity
    {
        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save()
            ->firstPhase()
                ->setRegistrationPeriod(new Open)
                ->createStep('Informações')
                ->createField(
                    identifier: 'biografia',
                    field_type: 'textarea',
                    title: 'Biografia',
                    required: false
                )
                ->done()
            ->save();

        $opportunity = $this->opportunityBuilder->getInstance();
        $opportunity->enableWorkplan = true;
        $opportunity->workplan_deliveryReportTheDeliveriesLinkedToTheGoals = true;
        $opportunity->save(true);

        return $opportunity;
    }

    private function createSentRegistrationWithWorkplan(Opportunity $opportunity, User $user): Registration
    {
        $registration = $this->registrationDirector->createSentRegistrations($opportunity, 1)[0];

        $workplan = new Workplan;
        $workplan->registration = $registration;
        $workplan->owner = $user->profile;
        $workplan->projectDuration = 12;
        $workplan->culturalArtisticSegment = 'Música';
        $workplan->save(true);

        $goal = new Goal;
        $goal->workplan = $workplan;
        $goal->owner = $user->profile;
        $goal->monthInitial = 1;
        $goal->monthEnd = 6;
        $goal->title = 'Meta exportação';
        $goal->description = 'Descrição da meta';
        $goal->save(true);
        $workplan->goals->add($goal);

        $delivery = new Delivery;
        $delivery->goal = $goal;
        $delivery->owner = $user->profile;
        $delivery->name = 'Entrega exportação';
        $delivery->description = 'Descrição da entrega';
        $delivery->typeDelivery = 'Outro';
        $delivery->save(true);
        $goal->deliveries->add($delivery);

        return $registration;
    }
}

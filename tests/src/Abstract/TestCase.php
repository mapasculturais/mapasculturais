<?php

namespace Tests\Abstract;

use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Entities\User;
use PHPUnit\Framework\Constraint\TraversableContainsEqual;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Message\ServerRequestInterface;

require_once __DIR__ . '/../bootstrap.php';

class TestCase extends PHPUnitTestCase
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PATCH = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';

    protected App $app;

    function __construct(string $name)
    {
        // chama os inicializadores das classes ou traits
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (str_starts_with($method, '__init')) {
                $this->$method();
            }
        }

        parent::__construct($name);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $app = App::i();
        $this->app = $app;
        $app->reset();

        $app->em->clear();
        $app->em->beginTransaction();

        $app->cache->deleteAll();
        $app->mscache->deleteAll();
        $app->rcache->deleteAll();

        $app->components->templates = [];

        $this->logout();
    }

    protected function tearDown(): void
    {
        $app = App::i();
        $app->em->rollback();
        $app->em->clear();
        $this->logout();
        parent::tearDown();
    }

    // =================== AUTENTICAÇÃO ===================

    protected function login(User $user)
    {
        $app = App::i();
        $app->reset();
        $app->auth->authenticatedUser = $user;
    }

    protected function logout(): void
    {
        $app = App::i();
        $app->auth->authenticatedUser = null;
    }

    // =================== PCACHE E JOBS ===================

    protected function processJobs(string $as_date = '2100-01-01 00:00', ?int $number_of_jobs = null): void
    {
        $app = App::i();
        $current_loggedin_user = $app->user;

        $processed_jobs_count = 0;
        while ($app->executeJob($as_date)) {
            $processed_jobs_count++;
            if ($number_of_jobs && $processed_jobs_count >= $number_of_jobs) {
                break;
            }
        }

        $this->login($current_loggedin_user);
    }

    protected function processPCache(): void
    {
        $app = App::i();
        $app->clearRecreatedPermissionCacheList();

        /** @var Connection */
        $conn = $app->em->getConnection();

        while ($conn->fetchScalar("SELECT count(*) FROM permission_cache_pending")) {
            $app->recreatePermissionsCache();
        }
    }

    // =================== ASSERTIONS ===================

    protected function assertException($exception_class, callable $callable, string $message = "Certificando que a exception %s é disparada")
    {
        $exception = null;
        try {
            $callable = \Closure::bind($callable, $this);
            $callable();
        } catch (\Exception $ex) {
            $exception = $ex;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $exception, sprintf($message, $exception_class));
    }

    protected function assertHttpStatusCode(ServerRequestInterface $request, int $code, string $message = ''): void
    {
        $app = App::i();
        $app->reset();

        $app->run($request, false);

        $this->assertEquals($code, $app->response->getStatusCode(), $message);
    }

    protected function assertStatus200(ServerRequestInterface $request, string $message = '')
    {
        $this->assertHttpStatusCode($request, 200, $message);
    }

    protected function assertStatus400(ServerRequestInterface $request, string $message = '')
    {
        $this->assertHttpStatusCode($request, 400, $message);
    }

    protected function assertStatus401(ServerRequestInterface $request, string $message = '')
    {
        $this->assertHttpStatusCode($request, 401, $message);
    }

    protected function assertStatus403(ServerRequestInterface $request, string $message = '')
    {
        $this->assertHttpStatusCode($request, 403, $message);
    }

    protected function assertStatus404(ServerRequestInterface $request, string $message = '')
    {
        $this->assertHttpStatusCode($request, 404, $message);
    }

    public function assertContainsOneOf(array $expected, iterable $actual, $message = '')
    {
        $constraints = [];
        foreach ($expected as $expected_value) {
            $constraints[] = new TraversableContainsEqual($expected_value);
        }
        $constraint = $this->logicalOr(...$constraints);
        $this->assertThat($actual, $constraint, $message);
    }
}

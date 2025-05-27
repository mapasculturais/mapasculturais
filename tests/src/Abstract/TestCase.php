<?php

namespace Tests\Abstract;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use Tests\Builders;
use Tests\Directors;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;


class TestCase extends PHPUnitTestCase
{
    protected App $app;

    protected Builders\AgentBuilder $agentBuilder;
    protected Builders\UserBuilder $userBuilder;

    protected Directors\UserDirector $userDirector;

    function __construct(string $name)
    {
        $this->agentBuilder = new Builders\AgentBuilder;
        $this->userBuilder = new Builders\UserBuilder;

        $this->userDirector = new Directors\UserDirector;

        parent::__construct($name);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $app = App::i();
        $app->em->clear();
        $app->em->beginTransaction();
        $app->auth->authenticatedUser = null;
        $this->app = $app;
    }

    protected function tearDown(): void
    {
        $app = App::i();
        $app->em->rollback();
        $app->em->clear();
        parent::tearDown();
    }

    protected function login(User $user) {
        $app = App::i();

        $app->auth->authenticatedUser = $user;
    }

    protected function assertException($exception_class, callable $callable, string $message = "Certificando que a exception %s é disparada") {
        $exception = null;
        try{
            $callable = \Closure::bind($callable, $this);
            $callable();
        } catch (\Exception $ex) {
            $exception = $ex;
        }

        $this->assertInstanceOf('MapasCulturais\Exceptions\PermissionDenied', $exception, sprintf($message, $exception_class));
    }
}

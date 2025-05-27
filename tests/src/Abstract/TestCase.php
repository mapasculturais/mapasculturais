<?php

namespace Tests\Abstract;

use MapasCulturais\App;
use Tests\Builders;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;


class TestCase extends PHPUnitTestCase
{
    protected App $app;

    protected Builders\AgentBuilder $agentBuilder;
    protected Builders\UserBuilder $userBuilder;

    function __construct()
    {
        $this->userBuilder = new Builders\UserBuilder;
        $this->agentBuilder = new Builders\AgentBuilder;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $app = App::i();
        $app->em->beginTransaction();
        $this->app = $app;
    }

    protected function tearDown(): void
    {
        $app = App::i();
        $app->em->rollback();

        parent::tearDown();
    }
}

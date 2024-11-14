<?php
namespace MapasCulturais\Tests;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset the static instances between tests
        $reflection = new \ReflectionClass(\MapasCulturais\App::class);
        $property = $reflection->getProperty('_instances');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function testCanCreateInstance()
    {
        $app = \MapasCulturais\App::i();
        $this->assertInstanceOf(\MapasCulturais\App::class, $app);
    }

    public function testReturnsSameInstance()
    {
        $app1 = \MapasCulturais\App::i();
        $app2 = \MapasCulturais\App::i();
        
        $this->assertSame($app1, $app2);
    }

    public function testDifferentIdsCreateDifferentInstances()
    {
        $app1 = \MapasCulturais\App::i('web');
        $app2 = \MapasCulturais\App::i('api');
        
        $this->assertNotSame($app1, $app2);
    }

    public function testSameIdReturnsSameInstance()
    {
        $app1 = \MapasCulturais\App::i('web');
        $app2 = \MapasCulturais\App::i('web');
        
        $this->assertSame($app1, $app2);
    }
}

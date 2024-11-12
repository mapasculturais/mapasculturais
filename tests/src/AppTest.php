<?php
namespace MapasCulturais;

require_once __DIR__.'/bootstrap.php';

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    protected function tearDown(): void
    {
        // Reset the static instances between tests
        $reflection = new \ReflectionClass(App::class);
        $property = $reflection->getProperty('_instances');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    public function testCanCreateInstance()
    {
        $app = App::i();
        $this->assertInstanceOf(App::class, $app);
    }

    public function testReturnsSameInstance()
    {
        $app1 = App::i();
        $app2 = App::i();
        
        $this->assertSame($app1, $app2);
    }

    public function testDifferentIdsCreateDifferentInstances()
    {
        $app1 = App::i('web');
        $app2 = App::i('api');
        
        $this->assertNotSame($app1, $app2);
    }

    public function testSameIdReturnsSameInstance()
    {
        $app1 = App::i('web');
        $app2 = App::i('web');
        
        $this->assertSame($app1, $app2);
    }
}

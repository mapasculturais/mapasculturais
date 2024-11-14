<?php
require_once 'bootstrap.php';

use MapasCulturais\Entities\Project;

class ProjectTests extends \MapasCulturais\Tests\TestCase{
    function testRegistrationIsOpen(){
        $project = new Project;

        $project->startsOn = date('Y-m-d', time() + 3600 * 24);
        $project->endsOn = date('Y-m-d H:i', time() + 3600 * 48);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no futuro deve ser false');
        
        $project->startsOn = date('Y-m-d', time() - 3600 * 48);
        $project->endsOn = date('Y-m-d H:i', time() - 3600 * 24);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no passado deve ser false');
        
        $project->startsOn = date('Y-m-d', time() - 3600 * 48);
        $project->endsOn = date('Y-m-d H:i', time() - 3600);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no passado deve ser false');
        
        $project->startsOn = date('Y-m-d', time() - 3600 * 48);
        $project->endsOn = date('Y-m-d H:i', time() + 3600 * 48);
        $this->assertTrue($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto com data inicial no passado e data final no futuro deve ser true');
        
        $project->startsOn = date('Y-m-d', time() - 3600 * 48);
        $project->endsOn = date('Y-m-d H:i', time() + 3600);
        $this->assertTrue($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto com data inicial no passado e data final no futuro deve ser true');
    }

    function testDatesValidations(){
        $project = new Project;
        $project->name = "Test1";
        $project->shortDescription = "A short description";
        $project->type = 1;

        $project->startsOn = date('Y-m-d', time() - 3600 * 24);
        $project->endsOn = date('Y-m-d H:i', time() + 3600 * 24);

        $this->assertEmpty($project->validationErrors);

        $project->startsOn = date('Y-m-d', time() + 3600 * 24);
        $project->endsOn = date('Y-m-d H:i', time() - 3600 * 24);

        $this->assertArrayHasKey('endsOn', $project->validationErrors);

    }
}
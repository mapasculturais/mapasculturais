<?php
require_once 'bootstrap.php';

use MapasCulturais\Entities\Project;

class ProjectTests extends MapasCulturais_TestCase{
    function testRegistrationIsOpen(){
        $project = new Project;

        $project->registrationFrom = date('Y-m-d', time() + 3600 * 24);
        $project->registrationTo = date('Y-m-d H:i', time() + 3600 * 48);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no futuro deve ser false');
        
        $project->registrationFrom = date('Y-m-d', time() - 3600 * 48);
        $project->registrationTo = date('Y-m-d H:i', time() - 3600 * 24);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no passado deve ser false');
        
        $project->registrationFrom = date('Y-m-d', time() - 3600 * 48);
        $project->registrationTo = date('Y-m-d H:i', time() - 3600);
        $this->assertFalse($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto no com datas no passado deve ser false');
        
        $project->registrationFrom = date('Y-m-d', time() - 3600 * 48);
        $project->registrationTo = date('Y-m-d H:i', time() + 3600 * 48);
        $this->assertTrue($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto com data inicial no passado e data final no futuro deve ser true');
        
        $project->registrationFrom = date('Y-m-d', time() - 3600 * 48);
        $project->registrationTo = date('Y-m-d H:i', time() + 3600);
        $this->assertTrue($project->isRegistrationOpen(), 'isRegistrationOpen() de um projeto com data inicial no passado e data final no futuro deve ser true');
    }

    function testDatesValidations(){
        $project = new Project;
        $project->name = "Test1";
        $project->shortDescription = "A short description";
        $project->type = 1;

        $project->registrationFrom = date('Y-m-d', time() - 3600 * 24);
        $project->registrationTo = date('Y-m-d H:i', time() + 3600 * 24);

        $this->assertEmpty($project->validationErrors);

        $project->registrationFrom = date('Y-m-d', time() + 3600 * 24);
        $project->registrationTo = date('Y-m-d H:i', time() - 3600 * 24);

        $this->assertArrayHasKey('registrationTo', $project->validationErrors);

    }
}
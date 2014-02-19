<?php
require_once 'bootstrap.php';

use MapasCulturais\Entities\Project;

class ProjectTests extends MapasCulturais_TestCase{
    function testRegistrationIsOpen(){
        $project = new Project;

        $cdata = new DateTime;

        $project->registrationFrom = date('Y-m-d', time() - 3600 * 24);
        $project->registrationTo = date('Y-m-d', time() + 3600 * 24);
        $this->assertEquals(true, $project->isRegistrationOpen());

        $project->registrationFrom = date('Y-m-d', time() - 3600);
        $project->registrationTo = date('Y-m-d', time() + 3600);
        $this->assertEquals(true, $project->isRegistrationOpen());

        $project->registrationFrom = date('Y-m-d', time() + 3600 * 24);
        $project->registrationTo = date('Y-m-d', time() + 3600 * 48);
        $this->assertEquals(false, $project->isRegistrationOpen());
    }

    function testDatesValidations(){
        $project = new Project;
        $project->name = "Test1";
        $project->type = 1;

        $project->registrationFrom = date('Y-m-d', time() - 3600 * 24);
        $project->registrationTo = date('Y-m-d', time() + 3600 * 24);

        $this->assertEmpty($project->validationErrors);


        $project->registrationFrom = date('Y-m-d', time() + 3600 * 24);
        $project->registrationTo = date('Y-m-d', time() - 3600 * 24);

        $this->assertArrayHasKey('registrationTo', $project->validationErrors);

    }
}
<?php
namespace Tests;

use MapasCulturais\Entities\Agent;
use Tests\Abstract\TestCase;
use Tests\Traits\UserDirector;
use Tests\Traits\SpaceDirector;

class EntityMetadataTest extends TestCase {
    use UserDirector;

    function testMetadataNameCaseInsensitivity() {
        $this->expectNotToPerformAssertions();

        $app = $this->app;

        $user = $this->userDirector->createUser();
        $profile = $user->profile;

        $this->login($user);
        
        $agent = $app->repo('Agent')->find($profile->id);

        $agent->pessoaDeficiente = ['Auditiva'];
                
        $agent->save(true);

        $app->em->clear();

        $user = $user->refreshed();
        $profile = $user->profile;
        
        $profile->pessoaDeficiente = ['Auditiva'];
        $profile->save(true);

    }


    function testMetadataUpdate() {
        $app = $this->app;

        $user = $this->userDirector->createUser();
        $profile = $user->profile;

        $this->login($user);
        
        $agent = $app->repo('Agent')->find($profile->id);
        $agent->pessoaDeficiente = ['Auditiva'];
        $agent->save(true);

        $agent = $agent->refreshed();
        $this->assertEquals(['Auditiva'], $agent->pessoaDeficiente, "Certificando que o metadado foi criado");

        $agent->pessoaDeficiente = ['Visual'];
        $agent->save(true);

        $agent = $agent->refreshed();
        $this->assertEquals(['Visual'], $agent->pessoaDeficiente, "Certificando que o metadado foi modificado");

    }
    
}
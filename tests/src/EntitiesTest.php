<?php

namespace Tests;

use DateTime;
use Tests\Abstract\TestCase;
use Tests\Traits\AgentDirector;
use Tests\Traits\UserDirector;
use Tests\Traits\SpaceDirector;

class EntitiesTest extends TestCase
{
    use UserDirector,
        SpaceDirector,
        AgentDirector;

    function testRefreshedFunction()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $profile = $user->profile;

        $new_name = 'Nome Modificado';

        $this->app->conn->executeQuery("UPDATE agent SET name = '{$new_name}' WHERE id = {$profile->id}");

        $profile = $profile->refreshed();
        
        $this->assertEquals($new_name, $profile->name, '[refreshed] Garantindo que o name modificado diretamente no banco está correto após o refreshed');
    }

    function testAutoUpdateTimestamp()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $space = $this->spaceDirector->createSpace($user->profile);
        $collective = $this->agentDirector->createCollective($user->profile);

        $this->assertNotNull($user->profile->updateTimestamp, 'Garantindo que o updateTimestamp de um USER PROFILE recém criado não está vazio ');
        $this->assertNotNull($space->updateTimestamp, 'Garantindo que o updateTimestamp de um ESPAÇO recém criado não está vazio');
        $this->assertNotNull($collective->updateTimestamp, 'Garantindo que o updateTimestamp de um ESPAÇO recém criado não está vazio');

        sleep(1);

        $initial_space_timestamp = $space->updateTimestamp;
        $space->name = "nome do espaço modificado";
        $space->save(true);

        $this->assertGreaterThan($initial_space_timestamp, $space->updateTimestamp, 'Garantindo que a data de atualização do espaço foi atualizada');

        $update_timestmap = new DateTime($space->updateTimestamp->format('Y-m-d H:i:s'));
        $space = $space->refreshed();

        $this->assertEquals($update_timestmap, $space->updateTimestamp, 'Garantindo que a data de atualização do espaço foi salva no banco de dados');

        $initial_collective_timestamp = new DateTime($collective->updateTimestamp->format('Y-m-d H:i:s'));
        $collective->name = "nome do coletivo modificado";
        $collective->save(true);

        $this->assertGreaterThan($initial_collective_timestamp, $collective->updateTimestamp, 'Garantindo que a data de atualização do coletivo foi atualizada');

        $update_timestmap = new DateTime($collective->updateTimestamp->format('Y-m-d H:i:s'));
        $collective = $collective->refreshed();

        $this->assertEquals($update_timestmap, $collective->updateTimestamp, 'Garantindo que a data de atualização do coletivo foi salva no banco de dados');

    }

    function testDisableAutoUpdateTimestampFlag() {
        $user = $this->userDirector->createUser();
        $this->login($user);
        $space = $this->spaceDirector->createSpace($user->profile);

        $this->assertTrue($space->isUpdateTimestampEnabled(), 
            'Garantindo que a flag autoUpdateTimestamp inicia ativa');

        $space->disableUpdateTimestamp();
        $this->assertFalse($space->isUpdateTimestampEnabled(), 
            'Garantindo que a flag autoUpdateTimestamp fica inativa após chamar o método disableUpdateTimestamp()');

        
        $space->enableUpdateTimestamp();
        $this->assertTrue($space->isUpdateTimestampEnabled(), 
            'Garantindo que a flag autoUpdateTimestamp fica ativa após chamar o método enableUpdateTimestamp()');


    }

    function testDisableAutoUpdateTimestamp()
    {
        $user = $this->userDirector->createUser();
        $this->login($user);

        $space = $this->spaceDirector->createSpace($user->profile);
        $collective = $this->agentDirector->createCollective($user->profile);

        $this->assertNotNull($user->profile->updateTimestamp, 'Garantindo que o updateTimestamp de um USER PROFILE recém criado não está vazio ');
        $this->assertNotNull($space->updateTimestamp, 'Garantindo que o updateTimestamp de um ESPAÇO recém criado não está vazio');
        $this->assertNotNull($collective->updateTimestamp, 'Garantindo que o updateTimestamp de um ESPAÇO recém criado não está vazio');

        sleep(1);

        // testa com entidade Space
        $initial_space_timestamp = $space->updateTimestamp;
        $space->disableUpdateTimestamp();
        $space->name = "nome do espaço modificado";
        $space->save(true);

        $this->assertEquals($initial_space_timestamp, $space->updateTimestamp, 'Garantindo que a data de atualização do espaço NÂO foi atualizada após salvamento com a flag disableUpdateTimestamp');

        // testa com entidade Agent
        $initial_collective_timestamp = $collective->updateTimestamp;
        $collective->disableUpdateTimestamp();
        $collective->name = "nome do coletivo modificado";
        $collective->save(true);

        $this->assertEquals($initial_collective_timestamp, $collective->updateTimestamp, 'Garantindo que a data de atualização do coletivo foi atualizada');
    }
}

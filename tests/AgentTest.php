<?php

require_once __DIR__.'/bootstrap.php';

class AgentTest extends MapasCulturais_TestCase{
    function testAgentProfileChange(){
        $user = $this->getUser('normal');
        $this->user = $user;
        $agent1 = $user->profile;
        
        $agent2 = $this->getNewEntity('Agent');
        $agent2->user = $user;
        $agent2->save(true);
        
        $this->assertTrue($agent1->isUserProfile, 'Asserting that the agent 1 is the user profile before change the the user profile to agent 2');
        $this->assertFalse($agent2->isUserProfile, 'Asserting that the agent 2 is not the user profile before change the the user profile to agent 2');
        
        $agent2->setAsUserProfile();
        
        $this->app->em->refresh($agent1);
        $this->app->em->refresh($agent2);
        
        $this->assertFalse($agent1->isUserProfile, 'Asserting that the agent 2 is not the user profile after change the the user profile to agent 2');
        $this->assertTrue($agent2->isUserProfile, 'Asserting that the agent 2 is the user profile after change the the user profile to agent 2');

        $agent1->setAsUserProfile();
        
        $this->app->em->refresh($agent1);
        $this->app->em->refresh($agent2);
        
        $this->assertTrue($agent1->isUserProfile, 'Asserting that the agent 2 is the user profile after change the the user profile back to agent 1');
        $this->assertFalse($agent2->isUserProfile, 'Asserting that the agent 2 is not the user profile after change the the user profile back to agent 1');

    }
}
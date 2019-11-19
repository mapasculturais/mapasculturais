<?php

require_once __DIR__.'/bootstrap.php';

class SealTest extends MapasCulturais_TestCase{
    function testCreateSealRealation(){
        $this->resetTransactions();
        $app = $this->app;
        $this->user = 'admin';
        $exception = null;
        
        try{
             // pega um agente sem selo
            $agent = $app->repo('Agent')->find(2);
            
            $seal = $app->repo('Seal')->find(1);
            // e tenta atribuir um selo
            
            $agent->createSealRelation($seal);
            
        } catch (Exception $ex) {
            $exception = $ex;
        }
        
        $this->assertNull($exception);
    }
    
    function testSealRelationPermissions(){
        $this->resetTransactions();
        $app = $this->app;
        
        // verifica que o guest user não pode atribuir selos
        $this->user = null;
        $this->assertPermissionDenied(function() use($app){
            // pega um agente sem selo
            $agent = $app->repo('Agent')->find(2);
            $seal = $app->repo('Seal')->find(1);

            // e tenta atribuir um selo
            $agent->createSealRelation($seal);
        });        
        
        // verifica que o admin user pode atribuir selos
        $this->user = 'admin';
        $this->assertPermissionGranted(function() use($app){
            // pega um agente sem selo
            $agent = $app->repo('Agent')->find(2);
            $seal = $app->repo('Seal')->find(1);

            // e tenta atribuir um selo
            $agent->createSealRelation($seal);
        });
        
        // testa se um usuário com controle de um selo pode atribuir selos
        $this->resetTransactions();
        $this->user = 'admin';
        
        $normal_user = $this->getUser('normal');
        $seal = $app->repo('Seal')->find(1);
        $seal->createAgentRelation($normal_user->profile, 'admin', true);

        $this->user = $normal_user;
        $this->assertAuthorizationRequestCreated(function() use($app){
            // pega um espaço sem selo
            $space = $app->repo('Space')->find(2);
            $seal = $app->repo('Seal')->find(1);

            // e tenta atribuir um selo
            $space->createSealRelation($seal);
        }, 'olá mundo do teste');
    }

    /**
     * Testa a funcionalidade dos selos em validar a data de expiração
     *
     * @return void
     */
    function testSealRelationExpirationDate(){
        $this->resetTransactions();

        //admin cria o seal relation espaço/selo
        $app = $this->app;
        $this->user = 'admin';
        $seal = $app->repo('Seal')->find(1);
        $space = $app->repo('Space')->find(2);
        $sealRelation = $space->createSealRelation($seal);

        //criação das datas teste
        $format = 'Y-m-d H:i:s';
        $testDate = DateTime::createFromFormat($format, '2017-05-26 10:00:00');
        $expirationDate = DateTime::createFromFormat($format, '2017-08-26 00:00:00');

        $sealRelation->validateDate = $testDate;
        $this->assertTrue($sealRelation->validPeriod < $expirationDate);
    }
}
<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of TestAuthentication
 *
 * @author rafael
 */
class AuthTest extends MapasCulturais_TestCase{
    function testLogin(){
        $app = $this->app;

        $this->assertTrue($app->user->is('guest'), 'user must be guest');

//        $this->assertfalse($app->auth->isUserAuthenticated()), 'user must )';
    }

    function testRequireAuthentication(){
        $app = $this->app;
        $this->assertEquals('200', $app->response->status(), ' [' . __LINE__ . '] response status code must be 200');

        try{
            $app->auth->requireAuthentication();
        }  catch (\Exception $e){}

        $this->assertEquals('401', $app->response->status(), ' [' . __LINE__ . '] response status code must be 401');


        $app->response->status(200);
        $this->assertEquals('200', $app->response->status(), ' [' . __LINE__ . '] response status code must be 200');

        try{
            $app->controller("space")->requireAuthentication();
        }  catch (\Exception $e){}


        $this->assertEquals('401', $app->response->status(), ' [' . __LINE__ . '] response status code must be 401');

        $app->response->status(200);
        $this->assertEquals('200', $app->response->status(), ' [' . __LINE__ . '] response status code must be 200');


    }
}

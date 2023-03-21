<?php
require_once __DIR__.'/bootstrap.php';
/**
 * Description of TestAuthentication
 *
 * @author rafael
 */
class AuthTest extends MapasCulturais_TestCase{
    function testAuthentication(){
        $this->user = null;

        $app = $this->app;

        // guest user
        $this->assertFalse($app->auth->isUserAuthenticated(), 'Asserting that the user is not authenticated.');
        $this->assertTrue($app->user->is('guest'), 'Asserting that the user is guest.');

        // superadmin

        $this->user = 'superAdmin';
        
        $this->assertTrue($app->auth->isUserAuthenticated(), 'Asserting that the user is authenticated.');
        $this->assertFalse($app->user->is('guest'), 'Asserting that the user is not guest.');
        $this->assertTrue($app->user->is('admin'), 'Asserting that the user is admin.');
        $this->assertTrue($app->user->is('superAdmin'), 'Asserting that the user is superAdmin.');

        $app->auth->logout();

        // guest user
        $this->assertFalse($app->auth->isUserAuthenticated(), 'Asserting that the user is not authenticated.');
        $this->assertTrue($app->user->is('guest'), 'Asserting that the user is guest.');
    }

    function testSuperAdminAuthentication(){
        $this->user = 'superAdmin';
        $this->assertTrue($this->app->user->is('superAdmin'), 'Asserting that the user is super admin.');
    }

    function testAdminAuthentication(){
        $this->user = 'admin';
        $this->assertTrue($this->app->user->is('admin'), 'Asserting that the user is admin.');
        $this->assertFalse($this->app->user->is('superAdmin'), 'Asserting that the user is not super admin.');
    }

    function testNormalAuthentication(){
        $this->user = 'normal';
        $this->assertFalse($this->app->user->is('guest'), 'Asserting that the user is not guest.');
        $this->assertFalse($this->app->user->is('admin'), 'Asserting that the user is not admin.');
        $this->assertFalse($this->app->user->is('superAdmin'), 'Asserting that the user is not super admin.');
    }

    function testRequireAuthentication(){
        $this->user = null;

        $app = $this->app;
        $this->assertEquals('200', $app->response->status(), 'Asserting response status code is 200');

        try{
            $app->auth->requireAuthentication();
        }  catch (\Exception $e){}

        $this->assertEquals('401', $app->response->status(), 'Asserting response status code is 401');


        $app->response->status(200);
        $this->assertEquals('200', $app->response->status(), 'Asserting response status code is 200');

        try{
            $app->controller("space")->requireAuthentication();
        }  catch (\Exception $e){}


        $this->assertEquals('401', $app->response->status(), 'Asserting response status code is 401');

        $app->response->status(200);
        $this->assertEquals('200', $app->response->status(), 'Asserting response status code is 200');

    }
}

<?php
namespace MapasCulturais;

/**
 * Fake Authentication Manager to use in development enviroments.
 *
 * This component render a form with a select box to the developer choose an user to login.
 *
 */
class AuthDev extends Auth{
    protected $userId = null;

    protected $loggedIn = true;

    public function __construct() {

        if(!key_exists('auth.devLoggedInUserId', $_SESSION))
            $_SESSION['auth.devLoggedInUserId'] = 0;

        if(isset($_GET['auth_dev_user_id']))
            $_SESSION['auth.devLoggedInUserId']= $_GET['auth_dev_user_id'];

        $this->userId = $_SESSION['auth.devLoggedInUserId'];

        App::i()->hook('controller.requireAuthentication', function(){
            $app = \MapasCulturais\App::i();
            $users = $app->repo('User')->findAll();
            $this->render('../auth/fake-authentication', array('users' => $users, 'form_action' => $app->request->getPathInfo()));
            $app->stop();
        });
    }


    public function isAuthenticated() {
        return !!$this->userId;
    }

    public function getAuthenticatedUser() {
        return App::i()->repo('User')->find($this->userId);
    }

    public function logout() {
        $_SESSION['auth.devLoggedInUserId'] = 0;
        App::i()->applyHook('auth.logout');
    }

    public function run(){

    }

    public function setRedirectPath($redirect_path = null) { }
}

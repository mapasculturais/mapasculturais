<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

class Fake extends \MapasCulturais\AuthProvider{
    protected function _init() {
        $app = App::i();

        // add actions to auth controller
        $app->hook('GET(auth.index)', function () use($app){
            $users = $app->repo('User')->findBy(array(), array('id' => 'ASC'));
            $this->render('../auth/fake-authentication', array('users' => $users, 'form_action' => $app->createUrl('auth', 'login')));
        });

        $app->hook('GET(auth.login)', function () use($app){
            $app->auth->processResponse();

            if($app->auth->isUserAuthenticated()){
                $app->redirect ($app->auth->getRedirectPath());
            }else{
                $app->redirect ($this->createUrl(''));
            }
        });
    }

    public function _cleanUserSession() {
        unset($_SESSION['auth.fakeAuthenticationUserId']);
    }

    public function _requireAuthentication() {
        $app = App::i();

        if($app->request->isAjax()){
            $app->halt(401, $app->txt('This action requires authentication'));
        }else{
            $this->_setRedirectPath($app->request->getPathInfo());
            $app->redirect($app->controller('auth')->createUrl(''), 401);
        }
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){
        $_SESSION['mapasculturais.auth.redirect_path'] = $redirect_path;
    }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        $path = key_exists('mapasculturais.auth.redirect_path', $_SESSION) ?
                    $_SESSION['mapasculturais.auth.redirect_path'] : App::i()->createUrl('site','');

        unset($_SESSION['mapasculturais.auth.redirect_path']);

        return $path;
    }


    public function _getAuthenticatedUser() {
        $user = null;
        if(key_exists('auth.fakeAuthenticationUserId', $_SESSION)){
            $user_id = $_SESSION['auth.fakeAuthenticationUserId'];
            $user = App::i()->repo("User")->find($user_id);
            return $user;
        }else{
            return null;
        }
    }


    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){
        if(key_exists('fake_authentication_user_id', $_GET)){
            $_SESSION['auth.fakeAuthenticationUserId'] = $_GET['fake_authentication_user_id'];
            $this->_setAuthenticatedUser($this->_getAuthenticatedUser());
        }
    }
}
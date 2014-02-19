<?php
namespace MapasCulturais;

/**
 * Authentication Controller
 */
class Auth extends \Opauth{

    /**
     * The logged in user
     * @var \MapasCulturais\Entities\User
     */
    protected $logged_in_user = null;

    /**
     * Authentication providers
     * @var array
     */
    protected $providers = array(
        'OpenID' => 1,
    );
    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    public function setRedirectPath($redirect_path){
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

    /**
     * Logout
     */
    public function logout(){
        unset($_SESSION['opauth']);
        App::i()->applyHook('auth.logout');
    }

    /**
     * Returns the Opauth authentication response or null if the user not tried to authenticate
     * @return array|null
     */
    protected function getResponse(){
        $app = App::i();
        /**
        * Fetch auth response, based on transport configuration for callback
        */
        $response = null;

        switch($this->env['callback_transport']) {
            case 'session':
                $response = key_exists('opauth', $_SESSION) ? $_SESSION['opauth'] : null;
                break;
            case 'post':
                $response = unserialize(base64_decode( $_POST['opauth'] ));
                break;
            case 'get':
                $response = unserialize(base64_decode( $_GET['opauth'] ));
                break;
            default:
                $app->log->error('Opauth Error: Unsupported callback_transport.');
                break;
        }
        return $response;
    }

    /**
     * Check if the Opauth response is valid. If it is valid, the user is authenticated.
     * @return boolean
     */
    protected function validateResponse(){
        $app = App::i();

        $response = $this->getResponse();

        $valid = false;

        // o usuário ainda não tentou se autenticar
        if(!is_array($response))
            return false;

        // verifica se a resposta é um erro
        if (array_key_exists('error', $response)) {
            $app->flash('auth error', 'Opauth returns error auth response');
        } else {
            /**
            * Auth response validation
            *
            * To validate that the auth response received is unaltered, especially auth response that
            * is sent through GET or POST.
            */
            if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid'])) {
                $app->flash('auth error', 'Invalid auth response: Missing key auth response components.');
            } elseif (!$this->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason)) {
                $app->flash('auth error', "Invalid auth response: {$reason}");
            } else {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){
        // se autenticou
        if($this->validateResponse()){
            // e ainda não existe um usuário no sistema
            if(!$this->getAuthenticatedUser()){
                $response = $this->getResponse();

                App::i()->repo('user')->createByAuthResponse($response);
            }
            App::i()->applyHook('auth.successful');
            return true;
        } else {
            App::i()->applyHook('auth.failed');
            return false;
        }
    }

    /**
     * Checks if the user is athenticated
     * @return boolean
     */
    function isAuthenticated(){
        return $this->validateResponse();
    }

    /**
     * Returns the authenticated user or null if the user is not authenticated
     * @return \MapasCulturais\Entities\User|null
     */
    function getAuthenticatedUser(){
        if($this->isAuthenticated()){
            if(!$this->logged_in_user){
                $app = App::i();
                $response = $this->getResponse();
                $auth_uid = $response['auth']['uid'];
                $auth_provider = $this->providers[$response['auth']['provider']];
                $user = $app->repo('User')->getByAuth($auth_provider, $auth_uid);

                if($user)
                    $this->logged_in_user = $user;
            }
        }else{
            $this->logged_in_user = null;
        }

        return $this->logged_in_user ;
    }

    function setCookies(){
        $user_id = App::i()->user ? App::i()->user->id : 0;
        $user_is_adm = App::i()->user ? App::i()->user->is('admin') : false;

        setcookie('mapasculturais.uid', $user_id, 0, '/');
        setcookie('mapasculturais.adm', $user_is_adm, 0, '/');

    }
}

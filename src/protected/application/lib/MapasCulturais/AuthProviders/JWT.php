<?php

namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use \Firebase\JWT\JWT as FireJWT; 
class JWT extends \MapasCulturais\AuthProvider {

    protected $__user = null;
    protected $__userApp = null;

    protected function _init() {
        $app = App::i();
        $token = $this->_config['token'];

        try {
            $exploded = array_map(function($item) {
                return json_decode(base64_decode($item));
            }, explode('.', $token));
            $jwt_data = $exploded[1] ?? null;
            if (isset($jwt_data->pk)) {
                $pk = $jwt_data->pk;

                $userapp = $app->repo('UserApp')->find($pk); // pegar da tabela de apps

                if(!$userapp){
                    http_response_code(401);
                    die;
                }

                FireJWT::decode($token, $userapp->privateKey, ['HS512', 'HS384', 'HS256', 'RS256']);
                $user = $userapp->user;
                $this->__user = $user;
                $this->__userApp = $userapp;
                return true;
            }
        } catch (\Exception $e) {
            http_response_code(401);
        }

        http_response_code(401);
        die;
    }

    public function _cleanUserSession()
    {
        $this->__user = null;
        $this->__userApp = null;
    }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath() {
        return null;
    }

    public function _getAuthenticatedUser() {
        return $this->__user;
    }

    public function getUserApp()
    {
        return $this->__userApp;
    }

    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse() {

    }

    protected function _createUser($data) {
        return null;
    }

}

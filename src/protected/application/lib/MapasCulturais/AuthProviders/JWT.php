<?php

namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

class JWT extends \MapasCulturais\AuthProvider {

    protected $__user = null;

    protected function _init() {
        $app = App::i();
        $token = $this->_config['token'];

        try {
            $jwt_data = \JWT::decode($token);
            if (isset($jwt_data->pk)) {
                $pk = $jwt_data->pk;

                $userapp = $app->repo('UserApp')->find($pk); // pegar da tabela de apps

                if(!$userapp){
                    http_response_code(401);
                    die;
                }

                \JWT::decode($token, $userapp->privateKey, ['HS512', 'HS256', 'HS1']);
                $user = $userapp->user;
                $this->__user = $user;
                return true;
            }
        } catch (\Exception $e) {
            http_response_code(401);
        }

        http_response_code(401);
        die;
    }

    public function _cleanUserSession() {
        $this->__user = null;
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

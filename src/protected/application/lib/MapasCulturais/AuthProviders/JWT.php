<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;

class JWT extends \MapasCulturais\AuthProvider{
    protected $__user = null;

    protected function _init() {

        /*
         * Testing
         *
         */
            $jwt_apps = [
                // eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJwayI6ImNoYXZlLXB1YmxpY2EtMSJ9.wSifWwJHVaj7RGPHDq71MigeGlFyKDHR9looJhIy-Ta80ePc8rJIBa42HbHGCSq-V3m6jwUk_KGpksximDaxBw
                'chave-publica-1'       => ['pri-key' => 'chave-privada-1', 'user_id' => 3062],

                // eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJwayI6ImNoYXZlLXB1YmxpY2EtMiJ9.ryM10vXBWPS2fS35Aoqu_IgW77rCt4oj-XJdZVGTyJcUFUdH4_HSe6tMKuMI2Lxy5bi2ZO2o76KBh1yuVcSdlw
                'chave-publica-2'       => ['pri-key' => 'chave-privada-2', 'user_id' => 259],

                // eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJwayI6InN1cGVyLWNoYXZlLXB1YmxpY2EifQ.S6ExDIAgh6yfVmwDYZLoJw4NYU1TWwqqWvVwonqTqtBqZBnIvooGCfEIVvSgrdn4poS-ujwZBWoa39xZQR-e7Q
                'super-chave-publica'   => ['pri-key' => 'chave-privada-3', 'user_id' => 1]
            ];
//
//            $data = ['pk' => 'super-chave-publica'];
//            $jwt = \JWT::encode(
//                $data,      //Data to be encoded in the JWT
//                jwt_key, // The signing key
//                'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
//            );
        /*
         * End Testing
         *
         */

        $app = App::i();
        $token = $this->_config['token'];

        try {
            $jwt_data = \JWT::decode($token);
            if(isset($jwt_data->pk)){
                $pk = $jwt_data->pk;

                $userapp = $jwt_apps[$pk]; // pegar da tabela de apps

                \JWT::decode($token, $userapp['pri-key'], ['HS512', 'HS256', 'HS1']);
                $user_id = $userapp['user_id'];
                $user = $app->repo('User')->find($user_id);
                $this->__user = $user;
                return true;
            }
        } catch (\Exception $e){
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
    public function getRedirectPath(){
        return null;
    }


    public function _getAuthenticatedUser() {
        return $this->__user;
    }


    /**
     * Process the Opauth authentication response and creates the user if it not exists
     * @return boolean true if the response is valid or false if the response is not valid
     */
    public function processResponse(){

    }

    protected function _createUser($data) {
        return null;
    }
}
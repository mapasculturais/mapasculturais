<?php
namespace Apps;

use Apps\Entities\UserApp;
use MapasCulturais\App;
use \Firebase\JWT\JWT as FireJWT;
use \Firebase\JWT\Key as FireKey;

class JWTAuthProvider extends \MapasCulturais\AuthProvider {

    protected $__user = null;
    protected $__userApp = null;

    protected function _init() {
        $app = App::i();
        $token = $this->_config['token'];

        try {
            $parts = explode('.', (string) $token);
            if (count($parts) !== 3) {
                http_response_code(401);
                die;
            }

            $payload = json_decode(self::base64UrlDecode($parts[1]));
            if (isset($payload->pk)) {
                $pk = $payload->pk;

                $userapp = $app->repo(UserApp::class)->find($pk); // pegar da tabela de apps

                if(!$userapp){
                    http_response_code(401);
                    die;
                }

                // Detect algorithm from header (default HS256)
                $header = json_decode(self::base64UrlDecode($parts[0]));
                $alg = isset($header->alg) ? (string) $header->alg : 'HS256';

                // Decode and validate token using firebase/php-jwt v6 API
                FireJWT::decode($token, new FireKey($userapp->privateKey, $alg));

                $user = $userapp->user;
                $this->__user = $user;
                $this->__userApp = $userapp;
                return true;
            }
        } catch (\Throwable $e) {
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

    private static function base64UrlDecode(string $data): string
    {
        $replaced = strtr($data, '-_', '+/');
        $padded = str_pad($replaced, strlen($replaced) % 4 === 0 ? strlen($replaced) : strlen($replaced) + 4 - (strlen($replaced) % 4), '=', STR_PAD_RIGHT);
        return base64_decode($padded) ?: '';
    }

}

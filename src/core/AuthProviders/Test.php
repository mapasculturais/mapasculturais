<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use MapasCulturais\GuestUser;
use MapasCulturais\i;

class Test extends \MapasCulturais\AuthProvider{
    protected $_user = null;
    
    protected $filename = '';

    protected function _init() {
        $tmp_dir = sys_get_temp_dir();
        $this->filename = isset($this->_config['filename']) ? 
                $this->_config['filename'] : $tmp_dir . '/mapasculturais-tests-authenticated-user.id';
    }

    public function _cleanUserSession() {
        if(file_exists($this->filename)){
            unlink($this->filename);
        }
        $this->_user = null;
    }

    public function _requireAuthentication() {
        $app = App::i();
        $app->halt(401, i::__('Esta ação requer autenticação.'));
    }

    /**
     * Defines the URL to redirect after authentication
     * @param string $redirect_path
     */
    protected function _setRedirectPath($redirect_path){ }

    /**
     * Returns the URL to redirect after authentication
     * @return string
     */
    public function getRedirectPath(){
        return '';
    }


    public function _getAuthenticatedUser() {
        
        if(file_exists($this->filename)){
            $app = App::i();
            $id = file_get_contents($this->filename);
            if($id) {
                $user = $app->repo('User')->find($id);
            } else {
                $user = GuestUser::i();
            }
            $this->_user = $user;
        }
        
        return $this->_user;
    }

    protected function setAuthenticatedUser(User|null $user = null){
        file_put_contents($this->filename, $user ? $user->id : '');
        $this->_setAuthenticatedUser($user);
    }
    
    protected function _createUser($data) {
        ;
    }
}

<?php
namespace MapasCulturais\AuthProviders;

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
        $app = \MapasCulturais\App::i();
        $app->halt(401, \MapasCulturais\i::__('This action requires authentication.'));
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
            $id = file_get_contents($this->filename);
            $this->_user = \MapasCulturais\App::i()->repo('User')->find($id);
        }
        
        return $this->_user;
    }

    public function setAuthenticatedUser(\MapasCulturais\Entities\User $user){
        file_put_contents($this->filename, $user->id);
        $this->_setAuthenticatedUser($user);
    }
    
    protected function _createUser($data) {
        ;
    }
}

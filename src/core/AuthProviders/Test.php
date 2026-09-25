<?php
namespace MapasCulturais\AuthProviders;

use MapasCulturais\App;
use MapasCulturais\Entities\User;
use MapasCulturais\GuestUser;
use MapasCulturais\i;

/**
 * Provedor de autenticação para testes
 * 
 * Implementa um provedor de autenticação simples para uso em testes
 * Armazena o ID do usuário autenticado em um arquivo temporário
 * 
 * @package MapasCulturais\AuthProviders
 */
class Test extends \MapasCulturais\AuthProvider{
    /**
     * Usuário autenticado
     * @var \MapasCulturais\Entities\User|null
     */
    protected $_user = null;
    
    /**
     * Nome do arquivo para armazenar o ID do usuário autenticado
     * @var string
     */
    protected $filename = '';

    /**
     * Inicializa o provedor de autenticação
     * 
     * Configura o arquivo para armazenamento do ID do usuário
     * 
     * @return void
     */
    protected function _init() {
        $tmp_dir = sys_get_temp_dir();
        $this->filename = isset($this->_config['filename']) ? 
                $this->_config['filename'] : $tmp_dir . '/mapasculturais-tests-authenticated-user.id';
    }

    /**
     * Limpa a sessão do usuário
     * 
     * Remove o arquivo de armazenamento do ID do usuário
     * 
     * @return void
     */
    public function _cleanUserSession() {
        if(file_exists($this->filename)){
            unlink($this->filename);
        }
        $this->_user = null;
    }

    /**
     * Requer autenticação do usuário
     * 
     * @return void
     * @throws \Exception Retorna erro 401
     */
    public function _requireAuthentication() {
        $app = App::i();
        $app->halt(401, i::__('Esta ação requer autenticação.'));
    }

    /**
     * Define a URL para redirecionamento após autenticação
     * 
     * @param string $redirect_path Caminho para redirecionamento
     * @return void
     */
    protected function _setRedirectPath($redirect_path){ }

    /**
     * Retorna a URL para redirecionamento após autenticação
     * 
     * @return string
     */
    public function getRedirectPath(){
        return '';
    }


    /**
     * Obtém o usuário autenticado
     * 
     * Lê o ID do usuário do arquivo de armazenamento
     * 
     * @return \MapasCulturais\Entities\User|\MapasCulturais\GuestUser|null
     */
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

    /**
     * Define o usuário autenticado
     * 
     * Armazena o ID do usuário no arquivo de armazenamento
     * 
     * @param \MapasCulturais\Entities\User|null $user Usuário a ser autenticado
     * @return void
     */
    protected function setAuthenticatedUser(User|null $user = null){
        file_put_contents($this->filename, $user ? $user->id : '');
        $this->_setAuthenticatedUser($user);
    }
    
    /**
     * Cria um novo usuário
     * 
     * @param mixed $data Dados do usuário
     * @return void
     */
    protected function _createUser($data) {
        ;
    }
}

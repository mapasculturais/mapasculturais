<?php
namespace MapasCulturais;

use \MapasCulturais\App;

/**
 * Classe base para todos os controladores.
 *
 * Esta classe abstrata fornece a estrutura fundamental para todos os controladores
 * do sistema Mapas Culturais. Ela implementa o padrão de roteamento baseado em métodos
 * HTTP e hooks, permitindo a criação de ações RESTful de forma consistente.
 *
 * Para criar um controlador você deve estender esta classe e registrar a nova classe de controlador
 * na aplicação com o método \MapasCulturais\App::registerController(). Dentro desta classe você pode criar ações.
 *
 * As ações dos controladores são métodos com nomes começando com o tipo de requisição (GET_, POST_, PUT_ ou DELETE_)
 * ou a palavra ALL_ seguida do nome da ação. Se você quiser uma ação que responda a todos os métodos de requisição,
 * use a palavra ALL, caso contrário coloque o tipo de requisição no início do nome do método.
 *
 * Dentro do método de ação você pode acessar os dados passados para a ação através de
 * $this->data, $this->urlData, $this->requestData, $this->getData, $this->postData, $this->putData e $this->deleteData
 *
 * Se você quiser que apenas usuários autenticados possam acessar uma ação, chame o método $this->requireAuthentication();
 * na primeira linha da ação. Este método redireciona o usuário, se ele não estiver logado, para a página de login
 * e redireciona de volta para a ação após o login bem-sucedido.
 *
 * Para renderizar JSON, chame $this->json($dados_para_codificar_em_json).
 *
 * Para renderizar um template com o layout, chame $this->render('nome-do-template', $array_de_dados_para_passar_ao_template).
 *
 * Para renderizar um template sem o layout, chame $this->partial('nome-do-template', $array_de_dados_para_passar_ao_template).
 *
 * Os arquivos de template para este controlador estão localizados na pasta themes/active/views/{$controller_id}/
 *
 * @property string $layout Layout utilizado para renderização
 * @property-read Theme $view Instância do tema
 * @property-read string $templatePrefix Prefixo para templates do controlador
 * @property-read array $urlData Variáveis baseadas em URL passadas na URL após o nome da ação
 *
 * @see \MapasCulturais\App::registerController()
 *
 * @hook **{$method}({$controller_id}.{$action_name})** *($arguments)* - executado se os métodos {$method}_{$action_name} e ALL_{$action_name} não existirem.
 * @hook **ALL({$controller_id}.{$action_name})** *($arguments)* - executado se os métodos {$method}_{$action_name} e ALL_{$action_name} e o hook anterior não existirem.
 *
 * @hook **{$method}:before** *($arguments)* - executado antes da execução de todas as ações de todos os controladores.
 * @hook **{$method}({$controller_id}):before** *($arguments)* - executado antes da execução de todas as ações do controlador.
 * @hook **{$method}({$controller_id}.{$action_name}):before** *($arguments)* - executado antes da execução da ação.
 * @hook **ALL:before** *($arguments)* - executado antes da execução de todas as ações de todos os controladores.
 * @hook **ALL({$controller_id}):before** *($arguments)* - executado antes da execução de todas as ações do controlador.
 * @hook **ALL({$controller_id}.{$action_name}):before** *($arguments)* - executado antes da execução da ação.
 *
 * @hook **{$method}:after** *($arguments)* - executado após a execução de todas as ações de todos os controladores
 * @hook **{$method}({$controller_id}):after** *($arguments)* - executado após a execução de todas as ações do controlador.
 * @hook **{$method}({$controller_id}.{$action_name}):after** *($arguments)* - executado após a execução da ação.
 * @hook **ALL:after** *($arguments)* - executado após a execução de todas as ações de todos os controladores.
 * @hook **ALL({$controller_id}):after** *($arguments)* - executado após a execução de todas as ações do controlador.
 * @hook **ALL({$controller_id}.{$action_name}):after** *($arguments)* - executado após a execução da ação.
 * 
 * @package MapasCulturais
 */

abstract class Controller{
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\MagicCallers;
        


    /**
     * Variáveis baseadas em URL passadas na URL após o nome da ação (não por GET).
     *
     * @example para a URL **http://mapasculturais/controller/action/id:11/name:Fulanano** esta propriedade será ['id' => 11, 'name' => 'Fulano']
     *
     * @var array Dados da URL
     */
    protected $_urlData = [];

    /**
     * Array com os dados da requisição.
     *
     * Este array é a combinação das variáveis baseadas em URL com $_REQUEST
     *
     * @example para a URL .../actionname/id:1/a-data/name:Fulano?age=33 o array resultante será [id=>1, 0=>a-data, name=>Fulano, age=>33]
     * @var array
     */
    public array $data = [];

    /**
     * @var array Dados da requisição GET
     */
    public array $getData = [];
    
    /**
     * @var array Dados da requisição POST
     */
    public array $postData = [];
    
    /**
     * @var array Dados da requisição PUT
     */
    public array $putData = [];
    
    /**
     * @var array Dados da requisição PATCH
     */
    public array $patchData = [];
    
    /**
     * @var array Dados da requisição DELETE
     */
    public array $deleteData = [];
    
    /**
     * @var array Dados da requisição (todos os métodos)
     */
    public array $requestData = [];

    /**
     * @var string|null Nome da ação atual
     */
    public $action = null;
    
    /**
     * @var string|null Método HTTP da requisição atual
     */
    public $method = null;

    /**
     * @var string Layout utilizado para renderização
     */
    protected $_layout = 'default';

    
    /**
     * Array de instâncias desta classe e todas as subclasses.
     * @var array
     */
    protected static $_singletonInstances = [];

    /**
     * @var string|null ID do controlador
     */
    public $id = null;

    /**
     * Retorna a instância singleton. Este método cria a instância quando chamado pela primeira vez.
     * 
     * @param string $controller_id ID do controlador
     * @return Controller Instância do controlador
     */
    static public function i(string $controller_id): Controller {
        $class = get_called_class();

        $id = "{$class}:{$controller_id}";

        if (!key_exists($id, self::$_singletonInstances)) {
            self::$_singletonInstances[$id] = new $class;
            self::$_singletonInstances[$id]->id = $controller_id;
        }

        return self::$_singletonInstances[$id];
    }

    /**
     * Indica se esta classe usa o padrão Singleton.
     * 
     * @return bool Sempre retorna true
     */
    public static function usesSingleton(){
        return true;
    }

    /**
     * Verifica se a requisição atual é uma requisição AJAX.
     *
     * @return bool True se for uma requisição AJAX, false caso contrário
     */
    public function isAjax(){
        $app = App::i();
        return $app->request->isAjax();
    }
    
    // =================== GETTERS ================== //

    /**
     * Retorna o layout do controlador.
     * 
     * @return string Layout atual
     */
    public function getLayout() {
        return $this->_layout;
    }

    /**
     * Retorna as variáveis baseadas em URL passadas na URL após o nome da ação (não por GET).
     *
     * @return array Dados da URL
     */
    public function getUrlData(){
        return $this->_urlData;
    }

    // =================== SETTERS ===================== //

    /**
     * Define o layout a ser usado para renderizar o template.
     *
     * @param string $layout Nome do layout
     */
    public function setLayout($layout){
        $this->_layout = $layout;
    }

    /**
     * Define os dados da requisição a serem usados nas ações.
     *
     * @param array $args Argumentos da URL
     */
    public function setRequestData(array $args){
        $this->_urlData = $args;
        $request = App::i()->request;

        $this->data = $args + $request->params();
        $this->getData = $request->params();

        if ($request->psr7request->getMethod() != 'GET') {
            $parsed_body = $request->psr7request->getParsedBody() ?: $_POST;
            
            $this->postData = $parsed_body;
            $this->putData = $parsed_body;
            $this->patchData = $parsed_body;
            $this->deleteData = $parsed_body;

            $this->data += $parsed_body;
        }        
    }


    /**
     * Chama uma ação deste controlador.
     *
     * A ação é um método nomeado {$method}_actionName (ex: GET_list) ou um hook como GET(controllerId.actionName).
     *
     * Este método primeiro tenta chamar um método começando com o tipo de requisição (como GET_), depois tenta chamar
     * um método começando com a palavra ALL_, depois tenta chamar os hooks.
     * Se nenhum desses métodos existir, a requisição é passada chamando App::i()->pass().
     *
     * Para ações da API, o nome do método de ação deve começar com API_ (ex: API_actionName)
     *
     * @param string $method (GET, PUT, POST, DELETE ou ALL)
     * @param string $action_name Nome da ação
     * @param array $arguments Argumentos para passar para a ação
     *
     * @example Para uma requisição POST para ..../controller_id/actionName, primeiro tenta Controller::POST_actionName,
     *          depois Controller::ALL_actionName, depois o hook com nome POST(controller_id.actionName),
     *          depois o hook ALL(controller_id.actionName)
     *
     * @hook **{$method}({$controller_id}.{$action_name})** *($arguments)* - executado se os métodos {$method}_{$action_name} e ALL_{$action_name} não existirem.
     * @hook **ALL({$controller_id}.{$action_name})** *($arguments)* - executado se os métodos {$method}_{$action_name} e ALL_{$action_name} e o hook anterior não existirem.
     *
     * @hook **{$method}:before** *($arguments)* - executado antes da execução de todas as ações de todos os controladores.
     * @hook **{$method}({$controller_id}):before** *($arguments)* - executado antes da execução de todas as ações do controlador.
     * @hook **{$method}({$controller_id}.{$action_name}):before** *($arguments)* - executado antes da execução da ação.
     * @hook **ALL:before** *($arguments)* - executado antes da execução de todas as ações de todos os controladores.
     * @hook **ALL({$controller_id}):before** *($arguments)* - executado antes da execução de todas as ações do controlador.
     * @hook **ALL({$controller_id}.{$action_name}):before** *($arguments)* - executado antes da execução da ação.
     *
     * @hook **{$method}:after** *($arguments)* - executado após a execução de todas as ações de todos os controladores
     * @hook **{$method}({$controller_id}):after** *($arguments)* - executado após a execução de todas as ações do controlador.
     * @hook **{$method}({$controller_id}.{$action_name}):after** *($arguments)* - executado após a execução da ação.
     * @hook **ALL:after** *($arguments)* - executado após a execução de todas as ações de todos os controladores.
     * @hook **ALL({$controller_id}):after** *($arguments)* - executado após a execução de todas as ações do controlador.
     * @hook **ALL({$controller_id}.{$action_name}):after** *($arguments)* - executado após a execução da ação.
     *
     */
    public function callAction($method, $action_name, $arguments) {
        $app = App::i();
        if($app->config['app.log.requestData']){
            $app->log->debug('===== POST DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->postData,true));

            $app->log->debug('===== GET DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->getData,true));

            $app->log->debug('===== URL DATA >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
            $app->log->debug(print_r($this->urlData,true));
        }

        $this->action = $action_name;

        $method = strtoupper($method);
        
        $this->method = $method;

        foreach($app->config['ini.set'] as $pattern => $configs) {
            $pattern = str_replace('*', '.*', $pattern);
            if(preg_match("#{$pattern}#i", "{$this->method} {$this->id}/{$this->action}")) {
                foreach($configs as $varname => $newvalue) {
                    ini_set($varname, $newvalue);
                }
            }
        }

        // hook like GET(user.teste)
        if($method == 'API'){
            $hooks = ["API({$this->id}.{$action_name})"];
        } else {
            $hooks = ["ALL({$this->id}.{$action_name})", "{$method}({$this->id}.{$action_name})"];
        }

        $has_hook = false;
        foreach($hooks as $hook){
            if($app->getHooks($hook)){
                $has_hook = true;
                break;
            }
        }


        $call_method = null;
        // first try to call an action defined inside the controller
        if(method_exists($this, $method . '_' . $action_name)){
            $call_method = [$this, $method . '_' . $action_name];

        }elseif($method !== 'API' && method_exists($this, 'ALL_' . $action_name)){
            $call_method = [$this, 'ALL_' . $action_name];

        }
        
        if($call_method || $has_hook){
            foreach($hooks as $call_hook){
                $app->applyHookBoundTo($this, $call_hook . ':before', $arguments);
            }

            if ($has_hook) {
                foreach($hooks as $call_hook){
                    $app->applyHookBoundTo($this, $call_hook, $arguments);
                }
            }

            if($call_method) {
                $call_method();
            }

            foreach($hooks as $call_hook){
                $app->applyHookBoundTo($this, $call_hook . ':after', $arguments);
            }
        }else{
            // 404
            $app->pass();
        }

    }

    /**
     * Retorna instância do tema.
     * 
     * @return Theme Instância do tema
     */
    function getView() {
        $app = App::i();
        return $app->view;
    }

    /**
     * Retorna o prefixo para templates do controlador.
     * 
     * @return string Prefixo do template (ID do controlador)
     */
    function getTemplatePrefix()
    {
        return $this->id;
    }

    /**
     * Renderiza um template na pasta com o nome do ID do controlador.
     *
     * @param string $template Nome do template
     * @param array $data Array com dados para passar ao template
     */
    public function render($template, $data=[])
    {
        $app = App::i();
        $app->applyHookBoundTo($this, "controller({$this->id}).render($template)", [
            "template" => &$template,
            "data" => &$data
        ]);
        $template = "{$this->templatePrefix}/$template";

        $app->view->render($template, (array) $data);
    }

    /**
     * Renderiza um template sem o layout, na pasta com o nome do ID do controlador.
     *
     * @param string $template Nome do template
     * @param array $data Array com dados para passar ao template
     */
    public function partial($template, $data=[])
    {
        $app = App::i();
        $app->applyHookBoundTo($this, "controller({$this->id}).partial($template)", [
            "template" => &$template,
            "data" => &$data
        ]);
        $template = "{$this->templatePrefix}/$template";
        $app->view->partial = true;
        $app->view->render($template, $data);
    }

    /**
     * Define o tipo de conteúdo da resposta como application/json e imprime os $data codificados em JSON.
     *
     * @param mixed $data Dados a serem codificados em JSON
     * @param int $status Código de status HTTP (padrão: 200)
     */
    public function json($data, $status = 200){
        $app = App::i();
        $app->response = $app->response->withHeader('Content-Type', 'application/json');
        $app->halt($status, json_encode($data));
    }

    /**
     * Define o tipo de conteúdo da resposta como application/json e imprime um JSON de ['error' => true, 'data' => $data].
     *
     * @param mixed $data Dados a serem incluídos na resposta de erro
     * @param int $status Código de status HTTP (padrão: 400)
     *
     * @TODO Alterar o status padrão para 400. será necessário alterar os js para esperar este retorno.
     */
    public function errorJson($data, $status = 400){
        $app = App::i();
        $app->response = $app->response->withHeader('Content-Type', 'application/json');
        $app->halt($status, json_encode(['error' => true, 'data' => $data]));
    }

    /**
     * Cria uma URL para o nome da ação e dados fornecidos.
     * 
     * @param string $actionName Nome da ação
     * @param array $data Dados para a URL
     * @return string URL gerada
     */
    public function createUrl($actionName, array $data = []){
        return App::i()->routesManager->createUrl($this->id, $actionName, $data);
    }

    /**
     * Este método redireciona a requisição para a página de autenticação se o usuário não estiver logado.
     *
     * Chame este método no início de ações que requerem autenticação.
     * 
     * @param string|null $redirect_url URL para redirecionar após o login (opcional)
     */
    public function requireAuthentication(string $redirect_url = null){
        $app = App::i();

        if($app->user->is('guest')){
            $app->applyHookBoundTo($this, "controller({$this->id}).requireAuthentication");

            $app->auth->requireAuthentication($redirect_url);
        }
    }
}

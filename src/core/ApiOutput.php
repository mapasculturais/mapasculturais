<?php
namespace MapasCulturais;

/**
 * Classe base para implementações de saída da API.
 *
 * Esta classe abstrata define a interface para diferentes formatos de saída
 * da API do Mapas Culturais (JSON, HTML, Excel, etc.). Classes derivadas
 * devem implementar os métodos abstratos para gerar saídas específicas.
 *
 * @property-read string $contentType O tipo de conteúdo (content-type)
 * @property-read string $hookClassName Nome da classe usado nos hooks
 * 
 * @hook api.response({API_OUTPUT_ID}).error:before
 * @hook api.response({API_OUTPUT_ID}).error:after
 * @hook api.response({API_OUTPUT_ID}).item({$singular_object_name}):before
 * @hook api.response({API_OUTPUT_ID}).item({$singular_object_name}):after
 * @hook api.response({API_OUTPUT_ID}).array({$singular_object_name}):before
 * @hook api.response({API_OUTPUT_ID}).array({$singular_object_name}):after
 * 
 * @package MapasCulturais
 */
abstract class ApiOutput{
    use Traits\Singleton,
        Traits\MagicGetter;

    /**
     * Nome da classe usado nos hooks
     * @var string
     */
    protected $hookClassName = '';

    /**
     * Construtor da classe
     */
    protected function __construct() {
        $this->hookClassName = App::i()->getRegisteredApiOutputId($this);
    }

    /**
     * Exibe dados de erro
     *
     * @param mixed $data
     * 
     * @hook **api.response({API_OUTPUT_ID}).error:before**
     * @hook **api.response({API_OUTPUT_ID}).error:after**
     * 
     */
    public function outputError($data){
        $app = App::i();

        $hook_data = ['data' => $data];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).error:before", $hook_data);

        $app->response = $app->response->withHeader('Content-Type', $this->contentType);

        ob_start();
        $this->_outputError($data);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response.error:after", $hook_data);
        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).error:after", $hook_data);

        $app->response->getBody()->write($output);
    }

    /**
     * Exibe um único item
     * 
     * @param mixed $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     * 
     * @hook **api.response({API_OUTPUT_ID}).item({$singular_object_name}):before**
     * @hook **api.response({API_OUTPUT_ID}).item({$singular_object_name}):after**
     */
    public function outputItem($data, $singular_object_name = 'entity', $plural_object_name = 'entities'){
        $app = App::i();

        $hook_data = [
            'data' => $data,
            'singular_object_name' => $singular_object_name,
            'plural_object_name' => $plural_object_name
        ];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).item({$singular_object_name}):before", $hook_data);

        $app->response = $app->response->withHeader('Content-Type', $this->contentType);

        ob_start();
        $this->_outputItem($data, $singular_object_name, $plural_object_name);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).item({$singular_object_name}):after", $hook_data);

        $app->response->getBody()->write($output);
    }

    /**
     * Exibe um array de itens
     * 
     * @param array $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     * 
     * @hook **api.response({API_OUTPUT_ID}).array({$singular_object_name}):before**
     * @hook **api.response({API_OUTPUT_ID}).array({$singular_object_name}):after**
     */
    public function outputArray(array $data, $singular_object_name = 'entity', $plural_object_name = 'entities'){
        $app = App::i();

        $hook_data = [
            'data' => $data,
            'singular_object_name' => $singular_object_name,
            'plural_object_name' => $plural_object_name
        ];

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).array({$plural_object_name}):before", $hook_data);

        $app->response = $app->response->withHeader('Content-Type', $this->contentType);
        ob_start();
        $this->_outputArray($data, $singular_object_name, $plural_object_name);
        $output = ob_get_clean();

        $hook_data['output'] = $output;

        $app->applyHookBoundTo($this, "api.response({$this->hookClassName}).array({$plural_object_name}):after", $hook_data);
        
        $app->response->getBody()->write($output);
    }

    /**
     * Retorna o tipo de conteúdo (content type) desta resposta
     *
     * @return string o tipo de conteúdo
     * 
     * @example retorna a string **application/json**
     */
    abstract protected function getContentType();

    /**
     * Retorna o conteúdo a ser impresso para um array
     * 
     * @param array $data
     * @param string $singular_object_name
     * @param string $plural_object_name
     */
    abstract protected function _outputArray(array $data, $singular_object_name = 'entity', $plural_object_name = 'entities');

    /**
     * Retorna o conteúdo a ser impresso para um item
     * 
     * @param mixed $data
     * @param string $object_name
     */
    abstract protected function _outputItem($data, $object_name = 'entity');

    /**
     * Retorna a mensagem de erro a ser impressa
     * 
     * @param mixed $data
     */
    abstract protected function _outputError($data);
}

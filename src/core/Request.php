<?php

namespace MapasCulturais;

use Psr\Http\Message\ServerRequestInterface as RequestInterface;


/**
 * Classe que representa uma requisição HTTP no MapasCulturais
 * 
 * @package MapasCulturais
 */
class Request {
    /**
     * Objeto PSR-7 da requisição
     * @var RequestInterface
     */
    public RequestInterface $psr7request;

    /**
     * Cabeçalhos da requisição
     * @var array
     */
    public array $headers;

    /**
     * ID do controlador da requisição
     * @var string
     */
    public $controllerId;

    /**
     * Ação sendo executada
     * @var string
     */
    public $action;

    /**
     * Parâmetros da requisição
     * @var array
     */
    public $params;

    /**
     * Rota da requisição (método + controller.action)
     * @var string
     */
    public $route = "";

    /**
     * Construtor da requisição
     * 
     * @param RequestInterface $psr7request Requisição PSR-7
     * @param string $controller_id ID do controlador
     * @param string $action Ação sendo executada
     * @param array $params Parâmetros da requisição
     */
    public function __construct(RequestInterface $psr7request, $controller_id, $action, $params) {
        if ($psr7request->getHeaderLine('Content-Type') === 'application/json') {
            $psr7request = $psr7request->withParsedBody(json_decode($psr7request->getBody()->getContents(), JSON_OBJECT_AS_ARRAY));
        }
        $this->psr7request = $psr7request;
        $this->headers = $psr7request->getHeaders();

        $this->controllerId = $controller_id;
        $this->action = $action;
        $this->params = $params;

        $this->route = $this->getMethod() . " {$controller_id}.$action";
    }

    /**
     * Retorna os parâmetros de query da requisição
     * 
     * @return array
     */
    public function params() {
        return (array) $this->psr7request->getQueryParams();
    }

    /**
     * Retorna o método HTTP da requisição
     * 
     * @return string
     */
    function getMethod() {
        return $this->psr7request->getMethod();
    }

    /**
     * Verifica se a requisição é AJAX
     * 
     * @return bool
     */
    public function isAjax() {
        if($this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' || $this->getHeaderLine('Content-Type') === 'application/json') {
            return true;
        }
    }

    /**
     * Obtém um parâmetro GET
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public function get(string $key = null, $default = null) {
        $params = $this->psr7request->getQueryParams();
        
        if ($key) {
            return $params[$key] ?? $default;
        } else {
            return $params;
        }
    }

    /**
     * Obtém um parâmetro POST
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public function post(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    /**
     * Obtém um parâmetro PUT
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public function put(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    /**
     * Obtém um parâmetro PATCH
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public function patch(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    /**
     * Obtém um parâmetro DELETE
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    public function delete(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    /**
     * Método interno para obter parâmetros do corpo da requisição
     * 
     * @param string|null $key Chave do parâmetro
     * @param mixed $default Valor padrão se a chave não existir
     * @return mixed
     */
    protected function _POST(string $key = null, $default = null) {
        if ($key) {
            return $_POST[$key] ?? $default;
        } else {
            return $_POST;
        }
    }

    /**
     * Retorna o referer da requisição
     * 
     * @return string
     */
    public function getReferer() {
        return $this->headers['HTTP_REFERER'] ?? $this->headers['Referer'] ?? '';
    }

    /**
     * Retorna o valor de um cabeçalho específico
     * 
     * @param string $name Nome do cabeçalho
     * @return string|null
     */
    public function getHeaderLine($name): string|null {
        return $this->psr7request->getHeaderLine($name);
    }

    /**
     * Retorna o caminho da requisição
     * 
     * @return string
     */
    public function getPathInfo() {
        return $this->psr7request->getUri()->getPath();
    }

    /**
     * Retorna o User-Agent da requisição
     * 
     * @return string
     */
    public function getUserAgent() {
        return $this->psr7request->getHeaderLine('User-Agent');
    }

    /**
     * Retorna o endereço IP do cliente
     * 
     * @return string
     */
    public function getIp() {
        return $this->psr7request->getAttribute('ip_address');
    }
}
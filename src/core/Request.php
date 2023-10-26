<?php

namespace MapasCulturais;

use Psr\Http\Message\ServerRequestInterface as RequestInterface;

class Request {
    public \Slim\Psr7\Request $psr7request;
    public array $headers;

    public $controllerId;
    public $action;
    public $params;

    public $route = "";

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

    public function params() {
        return array_merge(
            (array) $this->psr7request->getParsedBody(), 
            (array) $this->psr7request->getQueryParams()
        );
    }

    function getMethod() {
        return $this->psr7request->getMethod();
    }

    public function isAjax() {
        if($this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' || $this->getHeaderLine('Content-Type') === 'application/json') {
            return true;
        }
    }

    public function get(string $key = null, $default = null) {
        $params = $this->psr7request->getQueryParams();
        
        if ($key) {
            return $params[$key] ?? $default;
        } else {
            return $params;
        }
    }

    public function post(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    public function put(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    public function patch(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    public function delete(string $key = null, $default = null) {
        return $this->_POST($key, $default);
    }

    protected function _POST(string $key = null, $default = null) {
        if ($key) {
            return $_POST[$key] ?? $default;
        } else {
            return $_POST;
        }
    }

    public function getReferer() {
        return $this->headers['HTTP_REFERER'] ?? $this->headers['Referer'] ?? '';
    }

    public function getHeaderLine($name): string|null {
        return $this->psr7request->getHeaderLine($name);
    }

    public function getPathInfo() {
        return $this->psr7request->getUri()->getPath();
    }

    public function getUserAgent() {
        return $this->psr7request->getHeaderLine('User-Agent');
    }

    public function getIp() {
        return $this->psr7request->getAttribute('ip_address');
    }
}
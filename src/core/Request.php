<?php

namespace MapasCulturais;

use Psr\Http\Message\ServerRequestInterface as RequestInterface;

class Request {
    public \Slim\Psr7\Request $psr7request;

    public function __construct(RequestInterface $psr7request) {
        $this->psr7request = $psr7request;
    }

    public function params() {
        return array_merge(
            (array) $this->psr7request->getParsedBody(), 
            (array) $this->psr7request->getQueryParams()
        );
    }

    public function isAjax() {
        $request = $this->psr7request;
        if($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest' || $request->getHeaderLine('Content-Type') === 'application/json') {
            return true;
        }
    }
}
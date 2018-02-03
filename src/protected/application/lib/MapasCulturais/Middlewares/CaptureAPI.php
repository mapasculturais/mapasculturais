<?php
namespace MapasCulturais\Middlewares;
use \MapasCulturais\App;

class CaptureAPI extends \Slim\Middleware{

    protected $_path;
    
    protected $_logs;

    public function __construct($path = '/tmp/mapas-api-log') {
        $this->_path = $path;
        $this->_logs = file_exists($this->_path) ? json_decode(file_get_contents($this->_path)) : (object) [];
    }
    
    function save($controller, $endpoint, $qdata, $result){
        $app = App::i();

        $log = [
            'userId' => $app->user->id,
            'controller' => $controller,
            'endpoint' => $endpoint,
            'qdata' => $qdata,
            'result' => $result
            
        ];
        
        $md5 = md5(json_encode($log));
        
        $this->_logs->$md5 = $log;
    }
    
    public function call() {
        // Get reference to application
        $app = App::i();
        
        $self = $this;
        
        $app->hook('API.find(<<*>>).result', function($qdata, $result, $endpoint) use($self){
            $self->save($this->id, $endpoint, $qdata, $result);
        });
        
        $this->next->call();
        $api_request = $app->view->controller->method === 'API';
        
        if($api_request){
            $controller = $app->view->controller;
            $qdata = $controller->data;
            $endpoint = $app->view->controller->action;
            $result = $app->response->body();
            
            $this->save($controller->id, $endpoint, $qdata, json_decode($result));
        }
        
        file_put_contents($this->_path, json_encode($this->_logs, JSON_PRETTY_PRINT));
    }

    /**
     *
     * @param type $format
     * @param type $utimestamp
     *
     * @see http://stackoverflow.com/questions/169428/php-datetime-microseconds-always-returns-0
     *
     * @return type
     */
    function udate($format, $utimestamp = null){
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
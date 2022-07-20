<?php
namespace MapasCulturais\Exceptions;

/**
 * @property-read MapasCulturais\Entities\Request $request The request that throw this exception
 */
class WorkflowRequestTransport extends \Exception{
    protected $request = null;
    
    public function __construct(\MapasCulturais\Entities\Request $request) {
        $this->request = $request;
        parent::__construct();
    }
    public function __get($name){
        if($name == 'request')
                return $this->request;
    }
}
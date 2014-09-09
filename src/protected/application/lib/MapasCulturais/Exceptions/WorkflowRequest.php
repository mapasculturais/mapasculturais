<?php
namespace MapasCulturais\Exceptions;

/**
 * @property-read MapasCulturais\Entities\Request[] $requests The request that throw this exception
 */
class WorkflowRequest extends \Exception{
    protected $requests = null;
    
    function __construct(array $requests) {
        $this->requests = $requests;
        
        parent::__construct();
    }
    function __get($name){
        if($name == 'requests')
            return $this->requests;
    }
    
    function addRequest(\MapasCulturais\Entities\Request $request){
        $this->requests[] = $request;
    }
}
<?php
namespace MapasCulturais\Exceptions;

class WorkflowRequest extends \Exception{
    protected $request = null;
    
    public function __construct(\MapasCulturais\Entities\Request $request) {
        $this->request = $request;
        parent::__construct($request->getRequestMessage());
    }
    public function __get($name){
        if($name = 'request')
                return $this->request;
    }
}
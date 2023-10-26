<?php

namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\ApiQuery;

trait ControllerApiV2 {

    public function apiCount($params) {
        $dql = $this->_apiGetDql($params);
    }

    public function apiFindOne($params) {
        $dql = $this->_apiGetDql($params);
    }

    public function apiFind($params) {
        $dql = $this->_apiGetDql($params);
    }

    public function createApiQuery($params) {
        $query = new ApiQuery($this->entityClassName, $params);
        
    }

    

}

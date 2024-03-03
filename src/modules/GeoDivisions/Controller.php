<?php
namespace GeoDivisions;

use MapasCulturais\App;
use \Doctrine\ORM\Query\ResultSetMapping;

class Controller extends \MapasCulturais\Controller{

    function usesAPI() {
        return true;
    }

    function API_list() {
        $app = App::i();
        $include_data = isset($this->data['includeData']);
        $result = $app->getGeoDivisions($include_data);
        
        $this->json($result);
    }
}
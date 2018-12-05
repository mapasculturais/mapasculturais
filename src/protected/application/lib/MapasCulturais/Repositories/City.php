<?php
namespace MapasCulturais\Repositories;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Traits;


class City extends \MapasCulturais\Repository{


    public function findByStateCode($stateCode)
    {
        $app = App::i();
        $result = $app->repo('State')->findBy(['code' => $stateCode]);
        $state = null;
        $cities = [];

        if(!empty($result)){
            $state = $result[0];
        }

        if(!is_null($state)){
            $cities = $app->repo('City')->findBy(['state' => $state]);
        }
        return $cities;
    }
}
<?php
namespace MapasCulturais\Repositories;

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
            $cities = $app->repo('State')->findBy(['state_id' => $state->id]);
        }

        return $cities;
    }
}
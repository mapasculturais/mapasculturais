<?php
namespace MapasCulturais\Controllers;
use MapasCulturais\App;
class GeoDivision extends \MapasCulturais\Controller{
    function usesAPI(){
        return true;
    }

    function API_list(){

        $app = App::i();
        $result = [];
        if(isset($this->data['includeData'])){
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('type', 'type');
            $rsm->addScalarResult('name', 'name');

            $strNativeQuery = "SELECT type, name FROM geo_division";

            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);

            $divisions = $query->getScalarResult();
        }
        foreach($app->getRegisteredGeoDivisions() as $geoDivision){
            $r = clone $geoDivision;
            if(isset($this->data['includeData'])){
                foreach($divisions as $index => $division){
                    if(!isset($r->data)){
                       $r->data = [];
                    }
                    if($r->key === $division['type']){
                        $r->data[] = $division['name'];
                    }
                }
            }
            $result[] = $r;
        }
        $this->json($result);
    }
}
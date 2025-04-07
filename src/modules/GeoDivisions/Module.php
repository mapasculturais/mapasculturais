<?php

namespace GeoDivisions;

use LDAP\Result;
use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init()
    {   
        $app = App::i();
        $app->hook('entity(<<agent|space>>).save:before', function() use ($app) {

            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('type', 'type');
            $rsm->addScalarResult('name', 'name');
            $rsm->addScalarResult('cod', 'cod');

            $x = $this->location->longitude;
            $y = $this->location->latitude;

            $strNativeQuery = "SELECT type, name, cod FROM geo_division WHERE ST_Contains(geom, ST_Transform(ST_GeomFromText('POINT($x $y)',4326),4326))";

            $query = $app->em->createNativeQuery($strNativeQuery, $rsm);

            $divisions = $query->getScalarResult();

            foreach ($app->getRegisteredGeoDivisions() as $d) {
                $metakey = $d->metakey;
                $this->$metakey = '';
            }

            foreach ($divisions as $div) {
                $metakey = 'geo' . ucfirst($div['type']);
                $this->$metakey = $div['name'];

                $metakey2 = 'geo' . ucfirst($div['type']) . '_cod';
                $this->$metakey2 = $div['cod'];
            }
        });

        $self = $this;
        $app->hook('App::getGeoDivisions', function($value, $include_data = false) use ($self) {
            return $self->getGeoDivisions($include_data);
        });
    }

    function register()
    {        
        $app = App::i();
        $controllers = $app->getRegisteredControllers();

        if (!isset($controllers['geoDivisions'])) {
            $app->registerController('geoDivisions', Controller::class);
        }

        $geoDivisionsHierarchyCfg = $app->config['app.geoDivisionsHierarchy'];
        foreach ($geoDivisionsHierarchyCfg as $slug => $division) {
            $label = $division;
            
            if (is_array($division)) {
                $label = $division['name'];
            }

            $slug = ucfirst($slug);
            $this->registerAgentMetadata("geo{$slug}", ['label' => $label]);
            $this->registerAgentMetadata("geo{$slug}_cod", ['label' => $label]);

            $this->registerSpaceMetadata("geo{$slug}", ['label' => $label]);
            $this->registerSpaceMetadata("geo{$slug}_cod", ['label' => $label]);
        }
    }

    function getGeoDivisions($includeData = false)
    {
        $app = App::i();

        $cahce_key = 'geo_divisions:' . ($includeData ? 'with_data' : 'without_data');

        if($app->cache->contains($cahce_key)){
            return $app->cache->fetch($cahce_key);
        }

        $result = [];

        if($includeData){
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('type', 'type');
            $rsm->addScalarResult('cod', 'cod');
            $rsm->addScalarResult('name', 'name');

            $where = [];
            foreach ($app->config['app.geoDivisionsFilters'] ?? [] as $filter) {
                $where[] = "ST_Within(geom, (SELECT geom FROM geo_division WHERE cod = '{$filter}'))";
                $where[] = "ST_Covers(geom, (SELECT geom FROM geo_division WHERE cod = '{$filter}'))";
            }
            $where = implode(' OR ', $where);
            $where = $where ? "WHERE {$where}" : '';

            $strNativeQuery = "
                SELECT 
                    type, 
                    cod, 
                    name 
                FROM geo_division
                {$where}";

            $query = $app->em->createNativeQuery($strNativeQuery, $rsm);
            $divisions = $query->getScalarResult();
        }

        foreach($app->getRegisteredGeoDivisions() as $geoDivision){
            $r = clone $geoDivision;

            if($includeData){
                foreach($divisions as $index => $division){
                    if(!isset($r->data)){
                       $r->data = [];
                    }

                    if($r->key === $division['type']){
                        $r->data[$division['cod']] = $division['name'];
                    }
                }
            }
            $result[] = $r;
        }

        $app->cache->save($cahce_key, $result);

        return $result;
    }
}
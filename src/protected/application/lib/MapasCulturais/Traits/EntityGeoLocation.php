<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;
use \MapasCulturais\Types\GeoPoint;

/**
 * Defines that the entity has a location and geoLocation properties.
 *
 * This trait automaticaly sets the value of the property _geoLocation with a PostGIS ST_Geography type
 * when the location property changes.
 *
 * Use this trait in entities that have the location and the _geoLocation properties.
 *
 * <code>
 * // example with GeoPoint
 * $entity->location = new GeoPoint(-45.123, -23.345);
 *
 * // example with arrays
 * $entity->location = [-45.123, -23.345];
 * $entity->location = ['x' => -45.123, 'y' => -23.345];
 * $entity->location = ['longitude' => -45.123, 'latitude' => -23.345];
 *
 * </code>
 *
 * @property \MapasCulturais\Types\GeoPoint $location The location of the entity.
 */
trait EntityGeoLocation{

    /**
     * This entity has geoLocation
     * @return bool true
     */
    public static function usesGeoLocation(){
        return true;
    }

    /**
     * Sets the value of the location property.
     *
     * You can set the value of this property using a GeoPoint object or using an array,
     *
     * <code>
     * // example with GeoPoint
     * $entity->location = new GeoPoint(-45.123, -23.345);
     *
     * // example with arrays
     * $entity->location = [-45.123, -23.345];
     * $entity->location = ['x' => -45.123, 'y' => -23.345];
     * $entity->location = ['longitude' => -45.123, 'latitude' => -23.345];
     *
     * </code>
     *
     * This method sets the value of the property _geoLocation with a PostGIS ST_Geography type.
     *
     * @param \MapasCulturais\Types\GeoPoint|array $location
     */
    function setLocation($location){
        $x = $y = null;
        if(!($location instanceof GeoPoint)){
            if(is_array($location) && key_exists('x', $location) && key_exists('y', $location)){
                $x = $location['x'];
                $y = $location['y'];

            }elseif(is_array($location) && key_exists('longitude', $location) && key_exists('latitude', $location)){
                $x = $location['longitude'];
                $y = $location['latitude'];

            }elseif(is_array($location) && count($location) === 2 && is_numeric($location[0]) && is_numeric($location[1])){
                $x = $location[0];
                $y = $location[1];
            }else{
                throw new \Exception(\MapasCulturais\i::__('The location must be an instance of \MapasCulturais\Types\GeoPoint or an array with two numeric values'));
            }
            $location = new GeoPoint($x,$y);
        }

        if(is_numeric($x) && is_numeric($y)){
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
            $sql = "SELECT ST_GeographyFromText('POINT({$location->longitude} {$location->latitude})') AS geo";
            $rsm->addScalarResult('geo','geo');
            $query = App::i()->em->createNativeQuery($sql, $rsm);
            $this->_geoLocation = $query->getSingleScalarResult();
        }

        $this->location = $location;
    }


    function locationEquals($location){
        if($this->location instanceof GeoPoint && $location instanceof GeoPoint){
            if($this->location->latitude == $location->latitude && $this->location->longitude == $location->longitude)
                return true;
        }
        return false;
    }

}

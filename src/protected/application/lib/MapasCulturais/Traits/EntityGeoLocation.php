<?php
namespace MapasCulturais\Traits;

use Exception;
use MapasCulturais\App;
use \MapasCulturais\Types\GeoPoint;
use stdClass;

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
    function setLocation($location)
    {
        $x = $y = null;
        if (!($location instanceof GeoPoint)) {
            if ($location instanceof \stdClass && (isset($location->latitude) && isset($location->longitude) || isset($location->x) && isset($location->y))) {
                $location = (array) $location;
            }

            $location_values = null;

            if (isset($location['x']) && isset($location['y'])) { 
                $location_values = [$location['x'], $location['y']];                    
            } else if (isset($location['longitude']) && isset($location['latitude'])) {
                $location_values = [$location['longitude'], $location['latitude']];                    
            } else if (isset($location[0]) && isset($location[1])) {
                $location_values = $location;
            } 

            if (is_array($location_values) && (count($location_values) === 2) && is_numeric($location_values[0]) && is_numeric($location_values[1])) {
                $x = $location_values[0];
                $y = $location_values[1];
            } else {
                App::i()->log->debug(print_r($location, true));
                throw new \Exception(\MapasCulturais\i::__('The location must be an instance of \MapasCulturais\Types\GeoPoint or an array with two numeric values, ' . gettype($location) . ' given.'));
            }
            $location = new GeoPoint($x,$y);
        } else {
            $x = $location->latitude;
            $y = $location->longitude;
        }
        if (is_numeric($x) && is_numeric($y)) {
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

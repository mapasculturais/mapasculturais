<?php
namespace MapasCulturais\Types;

use \MapasCulturais\App;

/**
 * Representation of a geographyc point with longitude and latitude.
 *
 * Use instances of this class in properties mapped to type POINT in database.
 *
 * If you set the latitude or longitude with a number out of the range, this class will throw an Exception.
 *
 * @property float $longitude Longitude. Must be a number between 180 and -180.
 * @property float $latitude Latitude. Must be a number between 90 and -90.
 *
 */
class GeoPoint implements \JsonSerializable{
    use \MapasCulturais\Traits\MagicGetter,
        \MapasCulturais\Traits\MagicSetter;


    /**
     * Latitude. Must be a number between 90 and -90.
     *
     * If you set this property with a value greater than 90 or less then -90, an Exception will be throwed.
     *
     * @var float
     */
    protected $latitude = 0;


    /**
     * Longitude. Must be a number between 180 and -180.
     *
     * If you set this property with a value greater than 180 or less then -180, an Exception will be throwed.
     *
     * @var float
     */
    protected $longitude = 0;

    /**
     * Contructs the GeoPoint.
     *
     * @param float $longitude between 180 and -180
     * @param float $latitude between 90 and -90
     */
    public function __construct($longitude, $latitude) {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    /**
     * Returns (longitude,latidude) string
     *
     * @return string
     */
    public function __toString(){
        return "({$this->longitude},{$this->latitude})";
    }


    public function jsonSerialize(){
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];
    }


    /**
     * Sets the Latitude of the point.
     *
     * @param float $val Latitude
     *
     * @throws \Exception if the value is greater than 90 or less then -90
     */

    public function setLatitude($val) {
         if($val > 90 || $val < -90 || !is_numeric($val))
            throw new \Exception(\MapasCulturais\i::__('Latitude precisa ser um float entre 90 e -90'));

         $this->latitude = $val;
    }

    /**
     * Sets the Longitude of the point.
     *
     * @param float $val Longitude
     *
     * @throws \Exception if the value is greater than 180 or less then -180
     */
    public function setLongitude($val) {
         if($val > 180 || $val < -180)
            throw new \Exception(\MapasCulturais\i::__('Longitude precisa ser um float entre 180 em -180'));

         $this->longitude = $val;
    }
}

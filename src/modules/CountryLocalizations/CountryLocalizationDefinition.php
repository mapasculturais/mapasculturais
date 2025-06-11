<?php

namespace CountryLocalizations;

use MapasCulturais\Entity;
use MapasCulturais\Traits;
/**
 * @property-read string $countryCode
 * @property-read string $countryName
 * @property-read array[] $activeLevels
 * @property-read array[] $levelHierarchy
 */
abstract class CountryLocalizationDefinition
{
    use Traits\MagicGetter,
        Traits\MagicSetter;
        
    abstract function register();

    // =================== GETTERS ================== //

    /**
     * 
     * @return string 
     */
    abstract public function getCountryCode(): ?string;
    
    /**
     * 
     * @return string 
     */
    abstract public function getCountryName(): ?string;

    /**
     * 
     * @return array[]
     */
    abstract public function getActiveLevels(): ?array;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getPostalCode(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string 
     */
    abstract public function getLevel0(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string 
     */
    abstract public function getLevel1(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLevel2(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLevel3(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLevel4(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLevel5(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLevel6(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLine1(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getLine2(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract public function getFullAddress(Entity $entity): ?string;

    /**
     * 
     * @return array[]
     */
    abstract public function getLevelHierarchy(): ?array;

    // =================== SETTERS ===================== //

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setPostalCode(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel0(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel1(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel2(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel3(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel4(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel5(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLevel6(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLine1(Entity $entity, ?string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setLine2(Entity $entity, ?string $value): void;

     /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract public function setFullAddress(Entity $entity, ?string $value): void;

}

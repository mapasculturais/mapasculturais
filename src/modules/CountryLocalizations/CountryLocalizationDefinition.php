<?php

namespace CountryLocalizations;

use MapasCulturais\Entity;
use MapasCulturais\Traits;
/**
 * @property-read string $countryCode
 * @property-read string $countryName
 * @property-read array[] $activeLevels
 * @property-read string $postalCode
 * @property-read string $level1
 * @property-read string $level2
 * @property-read string $level3
 * @property-read string $level4
 * @property-read string $level5
 * @property-read string $level6
 * @property-read string $line1
 * @property-read string $line2
 * @property-read string $fullAddress
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
    abstract protected function getCountryCode(): string;
    
    /**
     * 
     * @return string 
     */
    abstract protected function getCountryName(): string;

    /**
     * 
     * @return array[]
     */
    abstract protected function getActiveLevels(): array;

    /**
     * 
     * @return string 
     */
    abstract protected function getPostalCode($entity): string;

    /**
     * @param Entity $entity
     * @return string 
     */
    abstract protected function getLevel1(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLevel2(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLevel3(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLevel4(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLevel5(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLevel6(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLine1(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getLine2(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return string
     */
    abstract protected function getFullAddress(Entity $entity): string;

    /**
     * @param Entity $entity
     * @return array[]
     */
    abstract protected function getLevelHierarchy(Entity $entity): array;

    // =================== SETTERS ===================== //

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel1(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel2(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel3(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel4(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel5(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLevel6(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLine1(Entity $entity, string $value): void;

    /**
     * @param Entity $entity
     * @param string $value
     * @return void
     */
    abstract protected function setLine2(Entity $entity, string $value): void;

}

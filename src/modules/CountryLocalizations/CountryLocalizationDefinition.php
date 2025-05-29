<?php

namespace CountryLocalizations;

/**
 * @package CountryLocalizations
 */
abstract class CountryLocalizationDefinition
{

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
     * 
     * @return string 
     */
    abstract protected function getLevel1($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLevel2($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLevel3($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLevel4($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLevel5($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLevel6($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLine1($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getLine2($entity): string;

    /**
     * 
     * @return string 
     */
    abstract protected function getFullAddress($entity): string;

    /**
     * 
     * @return array[]
     */
    abstract protected function getLevelHierarchy($entity): array;

    // =================== SETTERS ===================== //

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel1($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel2($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel3($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel4($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel5($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLevel6($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLine1($entity, $value): void;

    /**
     * @param mixed $entity
     * @param mixed $value
     * @return void
     */
    abstract protected function setLine2($entity, $value): void;

}

<?php
namespace MapasCulturais;
/**
 * The MapasCulturais validator class.
 *
 * This class extends Respect/Validation with validations created to MapasCulturais
 *
 */
class Validator extends \Respect\Validation\Validator{
    /**
     * Validates a URL
     *
     * If a domain is passed as param this method will ensures that the domain of the URL ends with the given domain.
     *
     * @example v::url()->validate('http://foo.bar/teste') return true
     * @example v::url('foo.bar')->validate('http://foo.bar.com/') return false
     * @example v::url('foo.bar')->validate('http://foo.bar/') return true
     * @example v::url('foo.bar')->validate('http://foo.bar/teste/') return true
     * @example v::url('foo.bar')->validate('http://subdomain.foo.bar/teste/') return true
     *
     * @param type $domain
     * @return type
     */
    static function url($domain = null){
        return self::call(
                'parse_url',
                self::arrayType()
                    ->key('scheme', self::startsWith('http'))
                    ->key('host', $domain ? self::domain()->endsWith($domain) : self::domain())
                );

    }

    /**
     * Validates Brazilian Phone Numbers
     *
     * @return type
     */
    static function brPhone(){
        return self::regex('/^\(?\d{2}\)?[ ]*\d{4,5}-?\d{4}$/');
    }
    
    /**
     * Validates Time
     *
     * @return type
     */
    static function time(){
        return self::regex('#([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?#');
    }
}
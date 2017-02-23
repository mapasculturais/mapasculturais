<?php
namespace EvaluationMethodSimple;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\EvaluationMethod{
    public function getSlug() {
        return 'simple';
    }
    public function getName() {
        return i::__('avaliação simples');
    }
    
    public function getDescription() {
        return i::__('Este método de avaliação consiste num select box com os status possíveis para uma inscrição.');
    }
    
    public function register() {
        ;
    }
    
    public function _init() {
        ;
    }
    
}

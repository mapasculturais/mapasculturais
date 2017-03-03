<?php
namespace EvaluationMethodDocumentary;
use MapasCulturais\i;

class Plugin extends \MapasCulturais\EvaluationMethod{
    public function getSlug() {
        return 'documentary';
    }
    public function getName() {
        return i::__('Avaliação Documental');
    }
    
    public function getDescription() {
        return i::__('Consiste num check box e um campo de texto para cada campo do formulário de inscrição.');
    }
    
    public function getConfigurationFormPartName() {
        return null;
    }
    
    public function _register() {
        ;
    }
    
    public function _init() {
        ;
    }
}

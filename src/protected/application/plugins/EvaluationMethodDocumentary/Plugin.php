<?php
namespace EvaluationMethodDocumentary;

use MapasCulturais\i;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\EvaluationMethod {

    public function getSlug() {
        return 'documentary';
    }

    public function getName() {
        return i::__('Avaliação Documental');
    }

    public function getDescription() {
        return i::__('Consiste num checkbox e um textarea para cada campo do formulário de inscrição.');
    }

    public function getConfigurationFormPartName() {
        return ;
    }

    protected function _register() {
        ;
    }

    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'documentary-evaluation-form', 'js/evaluation-form--documentary.js', ['entity.module.opportunity']);
    }

    public function _init() {
        ;
    }

    public function getConsolidatedResult(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        if(!is_array($evaluation->evaluationData) || count($evaluation->evaluationData) == 0){
            return true; // valid
        }

        foreach ($evaluation->evaluationData as $id => $value) {
            if(isset($value['invalid']) && $value['invalid']){
                return false;
            }
        }

        return true;
    }

    public function evaluationToString(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        if(!isset($evaluation->result)){
            return '';
        }

        if($evaluation->result){
            return i::__('Inscrição válida');
        } else {
            return i::__('Inscrição inválida');
        }
    }

}

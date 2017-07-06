<?php
namespace EvaluationMethodDocumentary;

use MapasCulturais\i;
use MapasCulturais\App;

const STATUS_INVALID = 'invalid';
const STATUS_VALID = 'valid';

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
        $app->view->enqueueStyle('app', 'documentary-evaluation-method', 'css/documentary-evaluation-method.css');
    }

    public function _init() {
        ;
    }

    public function getEvaluationResult(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        $data = (array) $evaluation->evaluationData;
        
        if(count($data) == 0){
            return true; // valid
        }

        foreach ($data as $id => $value) {
            if(isset($value['evaluation']) && $value['evaluation'] === STATUS_INVALID){
                return false;
            }
        }

        return true;
    }

    public function evaluationToString(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        if(is_null($evaluation->result)){
            return '';
        }

        if($evaluation->result){
            return i::__('Inscrição válida');
        } else {
            return i::__('Inscrição inválida');
        }
    }
    
    public function fetchRegistrations() {
        return true;
    }

}
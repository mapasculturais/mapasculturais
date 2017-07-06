<?php

namespace EvaluationMethodSimple;

use MapasCulturais\i;
use MapasCulturais\App;

class Plugin extends \MapasCulturais\EvaluationMethod {

    public function getSlug() {
        return 'simple';
    }

    public function getName() {
        return i::__('Avaliação Simplificada');
    }

    public function getDescription() {
        return i::__('Consiste num select box com os status possíveis para uma inscrição.');
    }

    public function getConfigurationFormPartName() {
        return null;
    }

    protected function _register() {
        ;
    }

    function enqueueScriptsAndStyles() {
        $app = App::i();

        $app->view->enqueueScript('app', 'simple-evaluation-form', 'js/ng.evaluationMethod.simple.js', ['entity.module.opportunity']);
        $app->view->jsObject['angularAppDependencies'][] = 'ng.evaluationMethod.simple';
    }

    public function _init() {
        ;
    }

    public function getEvaluationResult(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        if ($evaluation->evaluationData->status) {
            return $evaluation->evaluationData->status;
        } else {
            return null;
        }
    }

    public function evaluationToString(\MapasCulturais\Entities\RegistrationEvaluation $evaluation) {
        switch ($evaluation->result) {
            case 2:
                return i::__('Inválida');
                break;
            case 3:
                return i::__('Não selecionada');
                break;
            case 8:
                return i::__('Suplente');
                break;
            case 10:
                return i::__('Selecionada');
                break;
            default:
                return '';

        }
    }
    
    public function fetchRegistrations() {
        return true;
    }

}

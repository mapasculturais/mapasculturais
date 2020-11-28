<?php
namespace MapasCulturais\Controllers;
use \MapasCulturais\App;
use \MapasCulturais\i;
use PHPUnit\Runner\Exception;

class Indicadores extends \MapasCulturais\Controller{

    function GET_index() {
        $this->render('index');
    }

    function GET_profissionaisDeSaude() {
       $this->render('profissionais-de-saude');
    }

    function GET_profissionaisDeSaudeMedicos() {
        $this->render('profissionais-de-saude-medicos');
    }

    function GET_instituicoes() {
        $this->render('instituicoes-saude');
    }
}
<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;

class Excel extends \MapasCulturais\ApiOutputs\Html{
    protected function getContentType() {
        $app = \MapasCulturais\App::i();
        $response = $app->response();
        //$response['Content-Encoding'] = 'UTF-8';
        $response['Content-Type'] = 'application/force-download';
        $response['Content-Disposition'] ='attachment; filename="mapas-culturais-dados-exportados.xls"';
        $response['Pragma'] ='no-cache';

        return 'application/vnd.ms-excel; charset=UTF-8';
    }
}

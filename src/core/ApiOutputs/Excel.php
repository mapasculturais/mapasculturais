<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;

class Excel extends \MapasCulturais\ApiOutputs\Html{
    protected function getContentType() {
        $app = \MapasCulturais\App::i();
        
        $app->response = $app->response->withHeader('Content-Type', 'application/force-download');
        $app->response = $app->response->withHeader('Content-Disposition', 'attachment; filename="mapas-culturais-dados-exportados.xls"');
        $app->response = $app->response->withHeader('Pragma', 'no-cache');

        return 'application/vnd.ms-excel; charset=UTF-8';
    }
}

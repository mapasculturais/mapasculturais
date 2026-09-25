<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;

/**
 * Saída de API em formato Excel (XLS)
 * 
 * Esta classe estende a saída HTML para gerar arquivos Excel,
 * configurando os cabeçalhos HTTP apropriados para download.
 * 
 * @package MapasCulturais\ApiOutputs
 */
class Excel extends \MapasCulturais\ApiOutputs\Html{
    /**
     * Retorna o tipo de conteúdo HTTP para esta saída
     * 
     * Configura cabeçalhos para forçar o download do arquivo Excel
     * 
     * @return string Tipo de conteúdo (application/vnd.ms-excel; charset=UTF-8)
     */
    protected function getContentType() {
        $app = \MapasCulturais\App::i();
        
        $app->response = $app->response->withHeader('Content-Type', 'application/force-download');
        $app->response = $app->response->withHeader('Content-Disposition', 'attachment; filename="mapas-culturais-dados-exportados.xls"');
        $app->response = $app->response->withHeader('Pragma', 'no-cache');

        return 'application/vnd.ms-excel; charset=UTF-8';
    }
}

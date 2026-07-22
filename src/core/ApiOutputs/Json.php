<?php
namespace MapasCulturais\ApiOutputs;
use MapasCulturais\App;

/**
 * Saída de API em formato JSON
 * 
 * Esta classe gera respostas JSON para as requisições da API,
 * incluindo cabeçalhos CORS apropriados para permitir acesso
 * de diferentes origens conforme configurado.
 * 
 * @package MapasCulturais\ApiOutputs
 */
class Json extends \MapasCulturais\ApiOutput{
    /**
     * Retorna o tipo de conteúdo HTTP para esta saída
     * 
     * @return string Tipo de conteúdo (application/json)
     */
    protected function getContentType() {
        return 'application/json';
    }

    /**
     * Gera a saída JSON para um erro
     * 
     * @param mixed $data Dados do erro
     */
    protected function _outputError($data) {
        echo json_encode(['error' => true, 'data' => $data]);
    }

    /**
     * Gera a saída JSON para um array de dados
     * 
     * @param array $data Dados a serem serializados
     * @param string $singular_object_name Nome no singular para a entidade (não utilizado)
     * @param string $plural_object_name Nome no plural para a entidade (não utilizado)
     */
    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $app = App::i();
        $app->response = $app->response->withHeader('Access-Control-Allow-Origin', \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }

    /**
     * Gera a saída JSON para um único item
     * 
     * @param mixed $data Dados a serem serializados
     * @param string $object_name Nome do objeto (não utilizado)
     */
    protected function _outputItem($data, $object_name = 'Entity') {
        $app = App::i();
        $app->response = $app->response->withHeader('Access-Control-Allow-Origin', \MapasCulturais\App::i()->config['api.accessControlAllowOrigin']);
        echo json_encode($data);
    }
}
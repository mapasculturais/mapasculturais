<?php

namespace ApiDocumentation;

use MapasCulturais\App;

/**
 * Documentação navegável da API pública, em /api/documentation.
 */
class Controller extends \MapasCulturais\Controller
{
    function usesAPI()
    {
        return true;
    }

    /**
     * Renderiza o Swagger UI apontado para a especificação desta instalação.
     */
    function API_index()
    {
        $this->partial('index');
    }

    /**
     * Devolve a especificação OpenAPI da API pública, em YAML.
     */
    function API_openapi()
    {
        $app = App::i();

        $app->response = $app->response->withHeader('Content-Type', 'text/plain; charset=UTF-8');
        $app->halt(200, file_get_contents(__DIR__ . '/openapi.yaml'));
    }
}

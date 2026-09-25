<?php

namespace AgentDocuments;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Entities\Agent;

/**
 * Módulo da issue #32: exigência de documento no agente conforme o tipo.
 *
 * Com a config "agents.requiredDocumentsByType" (env
 * AGENTS_REQUIRED_DOCUMENTS_BY_TYPE, default false) ativa, todo salvamento de
 * agente passa a exigir:
 *  - tipo 1 (Individual, pessoa física ou MEI): CPF preenchido;
 *  - tipo 2 (Coletivo, pessoa jurídica): CNPJ preenchido.
 *
 * A exigência é injetada dinamicamente no hook entity(Agent).validations, que
 * roda em Entity::getValidationErrors() a cada salvamento (criação e edição),
 * lendo a config em tempo de execução. O valor verificado é o EFETIVO do
 * metadado, já contemplando o fallback cpf/cnpj -> documento definido em
 * src/conf/agent-types.php (a validação "required" avalia a propriedade mágica
 * $agent->cpf/$agent->cnpj, que aplica o fallback).
 *
 * Com a flag desativada o hook não injeta nada e o comportamento é o atual:
 * campos opcionais, com o formato (v::cpf()/v::cnpj()) validado quando
 * preenchidos.
 */
class Module extends \MapasCulturais\Module
{
    public function _init()
    {
        $app = App::i();

        $app->hook('entity(Agent).validations', function (&$validations) {
            /** @var Agent $this */
            if ($document = $this->requiredDocumentMetadata()) {
                $message = $document === 'cpf'
                    ? i::__('O CPF é obrigatório para este tipo de agente.')
                    : i::__('O CNPJ é obrigatório para este tipo de agente.');

                $validations[$document]['required'] = $message;
            }
        });

        $app->hook('mapas.printJsObject:before', function () use ($app) {
            $this->jsObject['config']['agentDocuments'] = [
                'requiredDocumentsByType' => (bool) ($app->config['agents.requiredDocumentsByType'] ?? false),
                'documentMetadataByType' => Agent::DOCUMENT_METADATA_BY_TYPE,
            ];
        });
    }

    public function register() {}
}

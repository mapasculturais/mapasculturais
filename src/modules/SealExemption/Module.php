<?php
namespace SealExemption;

use MapasCulturais\App;
use MapasCulturais\Entities\Registration;

require_once __DIR__ . '/ProponentAgentResolver.php';
require_once __DIR__ . '/SealExemptionVerifier.php';
require_once __DIR__ . '/SealExemptionService.php';

/**
 * Módulo de Avaliação Automática por Selos.
 *
 * Permite que proponentes com todos os selos validadores configurados como
 * plenamente válidos (fully_valid) sejam automaticamente dispensados de uma
 * fase de avaliação, recebendo status 10 e avançando para a próxima etapa.
 *
 * Serviços:
 * - ProponentAgentResolver: determina o agente proponente correto.
 * - SealExemptionVerifier: verifica validade dos selos do agente.
 * - SealExemptionService: orquestra a verificação e aplicação da isenção.
 *
 * Hooks:
 * - entity(Registration).send:after          → isenção na 1ª fase (EMC local) ou fases sincronizadas.
 * - entity(Registration).get(sealExempt)      → getter virtual booleano.
 * - entity(Registration).get(proponentAgentMissing) → getter virtual booleano.
 *
 * @package SealExemption
 */
class Module extends \MapasCulturais\Module
{
    /** @var SealExemptionService|null */
    private ?SealExemptionService $service = null;

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        // Snapshot opcional da isenção aplicada (rastreabilidade).
        $this->registerRegistrationMetadata('sealExemptionSnapshot', [
            'label' => \MapasCulturais\i::__('Snapshot da isenção por selos'),
            'type' => 'json',
            'description' => \MapasCulturais\i::__(
                'Registra os selos e configuração vigente no momento em que a ' .
                'isenção foi concedida. Complementa o sistema de entity_revision.'
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function _init(): void
    {
        $app = App::i();

        // Instanciar serviços
        $this->service = new SealExemptionService(
            new ProponentAgentResolver(),
            new SealExemptionVerifier()
        );

        $this->registerSendAfterHook($app);
        $this->registerDraftResetHook($app);
        $this->registerEvaluationResultHooks($app);
        $this->registerVirtualGetters($app);
        $this->registerSummaryHooks($app);
        $this->registerPhasesDataHook($app);
    }

    /**
     * Inclui sealExemptionConfig e canEditSealConfig no payload das fases
     * enviado ao frontend ($MAPAS.opportunityPhases).
     */
    private function registerPhasesDataHook(App $app): void
    {
        $appendFields = function (&$properties) {
            foreach (['sealExemptionConfig', 'canEditSealConfig'] as $field) {
                if (strpos($properties, $field) === false) {
                    $properties .= ',' . $field;
                }
            }
        };

        $app->hook('module(OpportunityPhases).evaluationPhaseData', $appendFields);

        $app->hook('module(OpportunityPhases).dataCollectionPhaseData', function (&$properties) use ($appendFields) {
            $appendFields($properties);
        });
    }

    /**
     * Hook: entity(Registration).send:after
     *
     * Dispara a verificação de isenção quando:
     * - a inscrição entra em fase de avaliação via sync (fases subsequentes), ou
     * - a inscrição é enviada na primeira fase cuja oportunidade possui EMC com
     *   sealExemptionConfig ativa (coleta + avaliação na mesma fase).
     *
     * Guardas adicionais: EMC presente, config ativa, tipo != technical.
     */
    private function registerSendAfterHook(App $app): void
    {
        $service = $this->service;

        $app->hook('entity(Registration).send:after', function () use ($service) {
            /** @var Registration $this */
            $service->processExemptionOnSend($this);
        });
    }

    /**
     * Limpa flags de isenção quando a inscrição volta a rascunho, permitindo
     * reavaliação automática no próximo envio.
     */
    private function registerDraftResetHook(App $app): void
    {
        $app->hook('entity(Registration).status(draft)', function () use ($app) {
            /** @var Registration $this */

            if ($this->sealExemptionStatus === null && $this->sealExemptionTimestamp === null) {
                return;
            }

            $app->disableAccessControl();
            $this->sealExemptionStatus = null;
            $this->sealExemptionTimestamp = null;
            $this->save(true);
            $app->enableAccessControl();
        });
    }

    /**
     * Concede os selos validadores quando uma inscrição não isenta passa pela
     * avaliação e recebe um resultado.
     *
     * Hooks explícitos para os três status que disparam concessão:
     * - approved (10): concede TODOS os selos configurados.
     * - notapproved (3): concessão PARCIAL (selos com 100% dos invalidadores válidos).
     * - waitlist (8): concessão PARCIAL (mesma regra do notapproved).
     * - invalid (2): concessão PARCIAL (mesma regra).
     *
     * Não usa wildcard status(<<*>>) para evitar overhead em draft/sent.
     */
    private function registerEvaluationResultHooks(App $app): void
    {
        $service = $this->service;

        $grant = function () use ($service) {
            /** @var Registration $this */
            $service->grantValidatorSealsAfterEvaluationResult($this);
        };

        $app->hook('entity(Registration).status(approved)', $grant);
        $app->hook('entity(Registration).status(notapproved)', $grant);
        $app->hook('entity(Registration).status(waitlist)', $grant);
        $app->hook('entity(Registration).status(invalid)', $grant);
    }

    /**
     * Getters virtuais para compatibilidade com a API e Frontend.
     *
     * - sealExempt: boolean (true se sealExemptionStatus === 'granted')
     * - proponentAgentMissing: boolean (true se sealExemptionStatus === 'agent_missing')
     */
    private function registerVirtualGetters(App $app): void
    {
        // sealExempt → booleano derivado do enum
        $app->hook('entity(Registration).get(sealExempt)', function (&$value) {
            /** @var Registration $this */
            $value = $this->sealExemptionStatus === 'granted';
        });

        // proponentAgentMissing → booleano derivado do enum
        $app->hook('entity(Registration).get(proponentAgentMissing)', function (&$value) {
            /** @var Registration $this */
            $value = $this->sealExemptionStatus === 'agent_missing';
        });
    }

    /**
     * Acrescenta no resumo das avaliações a quantidade de inscrições que foram
     * selecionadas automaticamente pela isenção por selos.
     */
    private function registerSummaryHooks(App $app): void
    {
        $app->hook('evaluations(<<*>>).summary', function (&$data) use ($app) {
            /** @var \MapasCulturais\Entities\EvaluationMethodConfiguration $this */
            $config = $this->sealExemptionConfig;

            if (!SealExemptionService::hasActiveConfig($config)) {
                return;
            }

            $opportunity = $this->owner;
            if (!$opportunity) {
                return;
            }

            $count = (int) $app->em->getConnection()->fetchOne(
                "SELECT COUNT(id)
                 FROM registration
                 WHERE opportunity_id = ?
                   AND status > 0
                   AND seal_exemption_status = 'granted'",
                [(int) $opportunity->id]
            );

            $data['evaluations'][\MapasCulturais\i::__('dispensadas automaticamente por selos')] = $count;
        });
    }
}

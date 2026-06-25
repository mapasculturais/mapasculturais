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
 * - entity(Registration).send:after          → dispara verificação de isenção.
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
        $this->registerManualApprovalHook($app);
        $this->registerVirtualGetters($app);
        $this->registerApiQueryHooks($app);
    }

    /**
     * Hook: entity(Registration).send:after
     *
     * Dispara a verificação de isenção quando uma inscrição entra em uma fase
     * de avaliação via sincronização de fases. Guardas:
     * 1. previousPhaseRegistrationId existe (não é submissão de primeira fase).
     * 2. O EMC tem sealExemptionConfig com selos não-vazio.
     * 3. O tipo de avaliação não é 'technical'.
     */
    private function registerSendAfterHook(App $app): void
    {
        $service = $this->service;

        $app->hook('entity(Registration).send:after', function () use ($service) {
            /** @var Registration $this */

            // Guarda 1: inscrição veio de sync de fase anterior
            if (!$this->previousPhaseRegistrationId) {
                return;
            }

            // Guarda 2: EMC com configuração de isenção ativa
            $emc = $this->evaluationMethodConfiguration;
            if (!$emc) {
                return;
            }

            $config = $emc->sealExemptionConfig;
            if (!$config || empty($config->seals)) {
                return;
            }

            // Guarda 3: excluir Avaliação Técnica (defesa em profundidade)
            // Padrão idiomático: evaluationMethodConfiguration->type->id retorna o slug.
            $evalType = $emc->type;
            if ($evalType && $evalType->id === 'technical') {
                return;
            }

            $service->applyExemptionCheck($this, $config);
        });
    }

    /**
     * Concede os selos validadores quando uma inscrição não isenta passa pela
     * avaliação manual e é selecionada.
     */
    private function registerManualApprovalHook(App $app): void
    {
        $service = $this->service;

        $app->hook('entity(Registration).status(approved)', function () use ($service) {
            /** @var Registration $this */
            $service->grantValidatorSealsAfterManualApproval($this);
        });
    }

    /**
     * Getters virtuais para compatibilidade com a API e Frontend.
     *
     * - sealExempt: boolean (true se sealExemptionStatus === 'granted')
     * - proponentAgentMissing: boolean (true se sealExemptionStatus === 'agent_missing')
     * - sealExemptionLabel: string (rótulo configurado no EMC, com fallback)
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

        // sealExemptionLabel → rótulo da fase (do EMC) com fallback
        $app->hook('entity(Registration).get(sealExemptionLabel)', function (&$value) {
            /** @var Registration $this */
            $emc = $this->evaluationMethodConfiguration;
            $value = SealExemptionService::getConfigLabel($emc?->sealExemptionConfig);
        });
    }

    /**
     * Acrescenta campos derivados da isenção por selos em respostas da API sem
     * acoplar essa regra ao core ApiQuery.
     */
    private function registerApiQueryHooks(App $app): void
    {
        $app->hook('ApiQuery(registration).findResult', function (&$result) {
            /** @var \MapasCulturais\ApiQuery $this */
            $selecting = (array) ($this->selecting ?? []);
            if (!in_array('sealExemptionLabel', $selecting, true) || empty($result)) {
                return;
            }

            $registration_ids = [];
            foreach ($result as $entity) {
                $id = $entity[$this->pk] ?? null;
                if ($id !== null && $id !== '') {
                    $registration_ids[] = (int) $id;
                }
            }

            $registration_ids = array_values(array_unique($registration_ids));
            if (!$registration_ids) {
                return;
            }

            $ids = implode(',', $registration_ids);
            $dql = "
                SELECT r.id AS reg_id, emcm.value AS config_value
                FROM " . Registration::class . " r
                JOIN r.opportunity o
                JOIN o.evaluationMethodConfiguration emc
                LEFT JOIN emc.__metadata emcm WITH emcm.key = :meta_key
                WHERE r.id IN ({$ids})
            ";

            $query = $this->em->createQuery($dql);
            $query->setParameter('meta_key', 'sealExemptionConfig');
            $rows = $query->getArrayResult();

            $labels = [];
            foreach ($rows as $row) {
                $config_value = $row['config_value'];
                $label = null;

                if ($config_value !== null && $config_value !== '') {
                    $config = json_decode($config_value, true);
                    $label = SealExemptionService::getConfigLabel(is_array($config) ? $config : []);
                }

                $labels[(int) $row['reg_id']] = $label;
            }

            foreach ($result as &$entity) {
                $id = $entity[$this->pk] ?? null;
                $entity['sealExemptionLabel'] = ($id !== null && array_key_exists((int) $id, $labels))
                    ? $labels[(int) $id]
                    : null;
            }
        });
    }
}

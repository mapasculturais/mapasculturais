<?php
namespace SealExemption;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Seal;
use MapasCulturais\i;

/**
 * Orquestra a verificação e aplicação da isenção automática por selos.
 *
 * Fluxo (especificação seção 3.5):
 * 1. Resolver agente proponente via ProponentAgentResolver.
 * 2. Se sem agente: setar seal_exemption_status = 'agent_missing'.
 * 3. Verificar selos via SealExemptionVerifier.
 * 4. Se isento: setStatusToApproved(false) + seal_exemption_status = 'granted'
 *    + timestamp + snapshot opcional + enfileirar sync da próxima fase
 *    + enfileirar atualização do summary/cache do EMC (especificação §3.6).
 * 5. Se não isento: seal_exemption_status permanece NULL (avaliação normal).
 *
 * Idempotência: se seal_exemption_status já é não-NULL, a verificação já foi
 * processada — não re-processa.
 *
 * Transacionalidade: status + flags escritos na mesma transação Doctrine.
 *
 * @package SealExemption
 */
class SealExemptionService
{
    /** @var ProponentAgentResolver */
    private ProponentAgentResolver $agentResolver;

    /** @var SealExemptionVerifier */
    private SealExemptionVerifier $sealVerifier;

    /**
     * @param ProponentAgentResolver $agentResolver
     * @param SealExemptionVerifier $sealVerifier
     */
    public function __construct(
        ProponentAgentResolver $agentResolver,
        SealExemptionVerifier $sealVerifier
    ) {
        $this->agentResolver = $agentResolver;
        $this->sealVerifier = $sealVerifier;
    }

    /**
     * Normaliza sealExemptionConfig para array associativo.
     *
     * @param mixed $config
     * @return array
     */
    public static function normalizeConfig($config): array
    {
        if (is_string($config)) {
            $decoded = json_decode($config, true);
            return is_array($decoded) ? $decoded : [];
        }

        if (is_object($config)) {
            $decoded = json_decode(json_encode($config), true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($config) ? $config : [];
    }

    /**
     * Verifica se há configuração ativa de isenção por selos.
     *
     * @param mixed $config
     * @return bool
     */
    public static function hasActiveConfig($config): bool
    {
        $config = self::normalizeConfig($config);
        $seals = $config['seals'] ?? null;

        return is_array($seals) && !empty(array_filter($seals, 'intval'));
    }

    /**
     * Retorna os IDs de selos da configuração ativa.
     *
     * @param mixed $config
     * @return int[]
     */
    public static function getConfiguredSealIds($config): array
    {
        $config = self::normalizeConfig($config);
        $seals = $config['seals'] ?? [];

        return array_values(array_unique(array_filter(array_map('intval', (array) $seals))));
    }

    /**
     * Resolve o rótulo público da isenção por selos.
     *
     * @param mixed $config
     * @return string
     */
    public static function getConfigLabel($config): string
    {
        $config = self::normalizeConfig($config);
        $label = isset($config['label']) ? trim((string) $config['label']) : '';

        return $label !== '' ? $label : i::__('Isento por selos válidos');
    }

    /**
     * Texto explícito para exibição do status de isenção.
     *
     * @param string|null $status
     * @param mixed $config
     * @return string
     */
    public static function getStatusLabel(?string $status, $config = null): string
    {
        if ($status === 'granted') {
            return self::getConfigLabel($config);
        }

        if ($status === 'agent_missing') {
            return i::__('Sem agente');
        }

        return i::__('Não isenta');
    }

    /**
     * Processa a verificação de isenção para uma inscrição.
     *
     * Deve ser chamado após a inscrição "entrar na fase" (hook send:after),
     * respeitando as guardas do hook (previousPhaseRegistrationId + config ativa).
     *
     * @param Registration $registration
     * @param object|null $config Configuração de selos do EMC ({ seals: [ids], label: string }).
     *                             Se null, não há configuração — não processa.
     * @return void
     */
    public function applyExemptionCheck(Registration $registration, ?object $config): void
    {
        // Sem configuração de isenção — nada a fazer
        if (!self::hasActiveConfig($config)) {
            return;
        }

        $app = App::i();

        // Hook before — permite plugins cancelarem ou modificarem
        $proceed = true;
        $app->applyHookBoundTo(
            $registration,
            'entity(Registration).sealExemption:before',
            [&$proceed, &$config]
        );

        if (!$proceed) {
            return;
        }

        // Idempotência: já processada nesta fase
        if ($registration->sealExemptionStatus !== null) {
            return;
        }

        // Idempotência: já aprovada por outro caminho
        if ($registration->status === Registration::STATUS_APPROVED) {
            return;
        }

        $sealIds = self::getConfiguredSealIds($config);

        // 1. Resolver agente proponente
        $agent = $this->agentResolver->resolve($registration);

        // 2. Sem agente identificável
        if (!$agent) {
            $this->markAgentMissing($registration);
            $app->applyHookBoundTo(
                $registration,
                'entity(Registration).sealExemption:after',
                ['agent_missing']
            );
            return;
        }

        // 3. Verificar selos
        $isExempt = $this->sealVerifier->hasAllValidSeals($agent, $sealIds);

        if (!$isExempt) {
            // 5. Não isento — avaliação normal (status permanece NULL)
            $app->applyHookBoundTo(
                $registration,
                'entity(Registration).sealExemption:after',
                ['not_exempt']
            );
            return;
        }

        // 4. Isento — aplicar isenção
        $this->grantExemption($registration, $config, $agent, $sealIds);

        $app->applyHookBoundTo(
            $registration,
            'entity(Registration).sealExemption:after',
            ['granted']
        );
    }

    /**
     * Concede os selos validadores ao proponente após aprovação manual.
     *
     * A inscrição só recebe os selos se:
     * - a fase tem sealExemptionConfig ativa;
     * - a inscrição não foi isenta automaticamente;
     * - todos os campos dos selos configurados foram avaliados como válidos.
     *
     * @param Registration $registration
     * @return bool True quando todos os selos foram concedidos.
     */
    public function grantValidatorSealsAfterManualApproval(Registration $registration): bool
    {
        if ($registration->status !== Registration::STATUS_APPROVED) {
            return false;
        }

        if ($registration->sealExemptionStatus === 'granted') {
            return false;
        }

        $emc = $registration->evaluationMethodConfiguration;
        if (!$emc instanceof EvaluationMethodConfiguration || !self::hasActiveConfig($emc->sealExemptionConfig)) {
            return false;
        }

        if ($this->isTechnicalEvaluation($registration)) {
            return false;
        }

        $sealIds = self::getConfiguredSealIds($emc->sealExemptionConfig);
        if (!$sealIds || !$this->canGrantValidatorSealsAfterSelection($registration, $sealIds)) {
            return false;
        }

        $agent = $this->agentResolver->resolve($registration);
        if (!$agent instanceof Agent) {
            return false;
        }

        $app = App::i();
        $applyingAgent = $registration->opportunity?->owner ?: $agent;

        $app->disableAccessControl();
        try {
            foreach ($sealIds as $sealId) {
                $seal = $app->repo('Seal')->find($sealId);
                if (!$seal instanceof Seal) {
                    continue;
                }
                $relation = $agent->createSealRelation($seal, true, true, $applyingAgent);
                $relation->renew();
                $relation->save(true);
            }
        } finally {
            $app->enableAccessControl();
        }

        return true;
    }

    /**
     * Marca a inscrição como "sem agente proponente identificado".
     *
     * A inscrição segue para avaliação normal — apenas sinalizada.
     *
     * @param Registration $registration
     * @return void
     */
    private function markAgentMissing(Registration $registration): void
    {
        $app = App::i();

        $app->disableAccessControl();
        $registration->sealExemptionStatus = 'agent_missing';
        $registration->save(true);
        $app->enableAccessControl();
    }

    /**
     * Em avaliação documental há validação campo a campo; nos demais métodos
     * não técnicos a aprovação manual da fase confirma os selos configurados.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @return bool
     */
    private function canGrantValidatorSealsAfterSelection(Registration $registration, array $sealIds): bool
    {
        if ($this->isDocumentaryEvaluation($registration)) {
            return $this->allConfiguredSealFieldsAreValid($registration, $sealIds);
        }

        return true;
    }

    /**
     * @param Registration $registration
     * @return bool
     */
    private function isTechnicalEvaluation(Registration $registration): bool
    {
        $typeId = $registration->evaluationMethodConfiguration?->type?->id;
        $slug = $registration->opportunity?->evaluationMethod?->slug;

        return $typeId === 'technical' || $slug === 'technical';
    }

    /**
     * @param Registration $registration
     * @return bool
     */
    private function isDocumentaryEvaluation(Registration $registration): bool
    {
        $typeId = $registration->evaluationMethodConfiguration?->type?->id;
        $slug = $registration->opportunity?->evaluationMethod?->slug;

        return $typeId === 'documentary' || $slug === 'documentary';
    }

    /**
     * Verifica se todos os campos associados aos selos configurados foram
     * avaliados como válidos no formulário documental.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @return bool
     */
    private function allConfiguredSealFieldsAreValid(Registration $registration, array $sealIds): bool
    {
        $evaluations = $registration->sentEvaluations;
        if (!$evaluations || count($evaluations) === 0) {
            return false;
        }

        $evaluatedFields = [];
        foreach ($evaluations as $evaluation) {
            $data = (array) ($evaluation->evaluationData ?? []);
            foreach ($data as $fieldId => $fieldEvaluation) {
                $fieldEvaluation = (array) $fieldEvaluation;
                if (($fieldEvaluation['evaluation'] ?? null) === 'valid') {
                    $evaluatedFields[(string) $fieldId] = true;
                }
            }
        }

        if (!$evaluatedFields) {
            return false;
        }

        $requiredFieldIdsBySeal = $this->getRequiredFieldIdsBySeal($registration, $sealIds);
        if (!$requiredFieldIdsBySeal) {
            return false;
        }

        foreach ($sealIds as $sealId) {
            $requiredFieldIds = $requiredFieldIdsBySeal[$sealId] ?? [];
            if (!$requiredFieldIds) {
                return false;
            }

            foreach ($requiredFieldIds as $fieldId) {
                if (empty($evaluatedFields[(string) $fieldId])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Mapeia selo => campos da inscrição que validam aquele selo na fase.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @return array<int, array<int>>
     */
    private function getRequiredFieldIdsBySeal(Registration $registration, array $sealIds): array
    {
        $app = App::i();
        $sealFields = [];

        foreach ($sealIds as $sealId) {
            $seal = $app->repo('Seal')->find($sealId);
            if (!$seal instanceof Seal) {
                continue;
            }

            $config = (array) $seal->lockedFieldsConfig;
            foreach (array_keys($config) as $lockedField) {
                $sealFields[$sealId][] = $lockedField;
            }
        }

        if (!$sealFields) {
            return [];
        }

        $result = [];
        $phases = $registration->opportunity->allPhases ?: [$registration->opportunity];
        foreach ($phases as $phase) {
            foreach ($phase->registrationFieldConfigurations as $field) {
                $entityField = $this->getRegistrationFieldEntityName($field);
                if (!$entityField) {
                    continue;
                }

                foreach ($sealFields as $sealId => $lockedFields) {
                    foreach ($lockedFields as $lockedField) {
                        if ($this->lockedFieldMatchesRegistrationField($lockedField, $entityField)) {
                            $result[$sealId][] = (int) $field->id;
                        }
                    }
                }
            }
        }

        foreach ($result as $sealId => $fields) {
            $result[$sealId] = array_values(array_unique($fields));
        }

        return $result;
    }

    /**
     * @param object $field
     * @return string|null
     */
    private function getRegistrationFieldEntityName(object $field): ?string
    {
        if (!in_array($field->fieldType, ['agent-owner-field', 'agent-collective-field', 'space-field'], true)) {
            return null;
        }

        $config = (array) ($field->config ?? []);
        $entityField = $config['entityField'] ?? null;

        return is_string($entityField) && $entityField !== '' ? $entityField : null;
    }

    /**
     * @param string $lockedField Ex.: agent.name, space.terms:area
     * @param string $entityField Ex.: name, terms:area
     * @return bool
     */
    private function lockedFieldMatchesRegistrationField(string $lockedField, string $entityField): bool
    {
        $parts = explode('.', $lockedField, 2);
        $fieldName = $parts[1] ?? $lockedField;

        return $fieldName === $entityField;
    }

    /**
     * Concede a isenção: status 10 + flags + snapshot + sync da próxima fase.
     *
     * Todas as escritas são atômicas na mesma transação Doctrine.
     *
     * @param Registration $registration
     * @param object $config Configuração de selos do EMC.
     * @param \MapasCulturais\Entities\Agent $agent Agente proponente.
     * @param array $sealIds IDs dos selos validadores.
     * @return void
     */
    private function grantExemption(
        Registration $registration,
        object $config,
        \MapasCulturais\Entities\Agent $agent,
        array $sealIds
    ): void {
        $app = App::i();

        $app->disableAccessControl();

        // Status 10 (aprovado/isento)
        $registration->setStatusToApproved(false);

        // Flags de isenção
        $registration->sealExemptionStatus = 'granted';
        $registration->sealExemptionTimestamp = new \DateTime();

        // Snapshot opcional para rastreabilidade (should-have)
        $this->saveSnapshot($registration, $config, $agent, $sealIds);

        $registration->save(true);

        $app->enableAccessControl();

        // Enfileirar sincronização para a próxima fase
        $this->enqueueNextPhaseSync($registration);

        // Invalidar o cache de summary do EMC para refletir a isenção (spec §3.6).
        // Usa enqueueOrReplaceJob, então múltiplas isenções em massa colapsam num
        // único job por EMC (dedup por ID).
        $this->enqueueEmcSummaryRefresh($registration);
    }

    /**
     * Salva um snapshot dos dados da isenção como metadado da inscrição.
     *
     * Estrutura: { emc_id, seal_ids, label, agent_id }
     *
     * @param Registration $registration
     * @param object $config
     * @param \MapasCulturais\Entities\Agent $agent
     * @param array $sealIds
     * @return void
     */
    private function saveSnapshot(
        Registration $registration,
        object $config,
        \MapasCulturais\Entities\Agent $agent,
        array $sealIds
    ): void {
        $emc = $registration->evaluationMethodConfiguration;

        $snapshot = (object) [
            'emc_id'  => $emc?->id,
            'seal_ids' => $sealIds,
            'label'   => $config->label ?? null,
            'agent_id' => $agent->id,
        ];

        $registration->setMetadata('sealExemptionSnapshot', json_encode($snapshot));
    }

    /**
     * Enfileira a sincronização da inscrição para a próxima fase.
     *
     * Se não há próxima fase, a inscrição permanece com status 10 aguardando
     * publicação do resultado.
     *
     * @param Registration $registration
     * @return void
     */
    private function enqueueNextPhaseSync(Registration $registration): void
    {
        $opportunity = $registration->opportunity;

        if (!$opportunity) {
            return;
        }

        $nextPhase = $opportunity->nextPhase;

        if ($nextPhase) {
            $nextPhase->enqueueRegistrationSync([$registration]);
        }
    }

    /**
     * Enfileira a atualização do cache/summary do EMC após uma isenção.
     *
     * Especificação §3.6 (Invalidação de cache/summary): garante que
     * EvaluationMethodConfiguration::summary reflita as inscrições isentas.
     *
     * Por que é necessário: o hook existente entity(Registration).status(<<*>>)
     * (Opportunities/Module.php) enfileira UpdateSummaryCaches passando apenas
     * 'opportunity' — o que atualiza o summary da oportunidade, mas NÃO o do EMC
     * (o job _execute trata 'opportunity' e 'evaluationMethodConfiguration' com
     * ifs independentes). Este método fecha essa lacuna enfileirando o job com
     * a chave 'evaluationMethodConfiguration'.
     *
     * Coalescência em massa: usa EvaluationMethodConfiguration::enqueueUpdateSummary
     * que internamente chama enqueueOrReplaceJob. O ID gerado é
     * "UpdateSummaryCaches::{emc_id}", então múltiplas isenções concedidas durante
     * um sync de fase colapsam num único job pendente (a última start_string vence).
     * O atraso de 5 segundos coalesce rajadas rápidas mantendo responsividade.
     *
     * Chamado apenas em grantExemption (status muda para 10). Casos agent_missing
     * e not_exempt não alteram o status do registro, portanto não invalidam o
     * summary.
     *
     * @param Registration $registration Inscrição isenta (status 10).
     * @return void
     */
    private function enqueueEmcSummaryRefresh(Registration $registration): void
    {
        $emc = $registration->evaluationMethodConfiguration;

        if (!$emc) {
            return;
        }

        $emc->enqueueUpdateSummary('5 seconds');
    }
}

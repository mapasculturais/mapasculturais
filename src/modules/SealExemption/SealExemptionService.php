<?php
namespace SealExemption;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\AgentSealRelation;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\SealRelation;
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
     * Lê a configuração de isenção do EMC ignorando restrições de metadado privado.
     *
     * @param EvaluationMethodConfiguration $emc
     * @return object|null
     */
    public static function resolveSealExemptionConfig(EvaluationMethodConfiguration $emc): ?object
    {
        $app = App::i();

        $app->disableAccessControl();
        try {
            $raw = $emc->sealExemptionConfig ?? $emc->getMetadata('sealExemptionConfig');
        } finally {
            $app->enableAccessControl();
        }

        if (!self::hasActiveConfig($raw)) {
            return null;
        }

        return (object) self::normalizeConfig($raw);
    }

    /**
     * Indica se a isenção deve ser verificada no envio da inscrição.
     *
     * - Fases subsequentes: inscrição sincronizada da fase anterior.
     * - Primeira fase: oportunidade com EMC e config ativa na própria fase de coleta.
     *
     * @param Registration $registration
     * @return bool
     */
    public function shouldProcessExemptionOnSend(Registration $registration): bool
    {
        if ($registration->previousPhaseRegistrationId) {
            return true;
        }

        $opportunity = $registration->opportunity;

        return $opportunity && $opportunity->isFirstPhase;
    }

    /**
     * Verifica e aplica isenção após o envio da inscrição (hook send:after).
     *
     * @param Registration $registration
     * @return void
     */
    public function processExemptionOnSend(Registration $registration): void
    {
        if (!$this->shouldProcessExemptionOnSend($registration)) {
            return;
        }

        $emc = $registration->evaluationMethodConfiguration;
        if (!$emc instanceof EvaluationMethodConfiguration) {
            return;
        }

        // Na 1ª fase (sem sync), só processa config do EMC desta oportunidade —
        // não config herdada de fase-filha de avaliação.
        if (!$registration->previousPhaseRegistrationId) {
            $opportunity = $registration->opportunity;
            if (!$opportunity || (int) $emc->opportunity->id !== (int) $opportunity->id) {
                return;
            }
        }

        $evalType = $emc->type;
        if ($evalType && $evalType->id === 'technical') {
            return;
        }

        $config = self::resolveSealExemptionConfig($emc);
        if (!$config) {
            return;
        }

        $this->applyExemptionCheck($registration, $config);
    }

    /**
     * Resolve o rótulo público fixo da isenção por selos.
     *
     * O texto é padronizado; não há mais configuração de rótulo customizável
     * por fase (simplificação de UX).
     *
     * @param mixed $config
     * @return string
     */
    public static function getConfigLabel($config): string
    {
        return i::__('Dispensada por selos');
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
     * respeitando as guardas de processExemptionOnSend + config ativa.
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

        // Idempotência: já aprovada (por isenção ou outro caminho) neste ciclo.
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

        // 3b. Se não isento pelos selos, tentar relevação condicional de invalidadores
        // (spec-fe9b2cfc): invalidadores amarrados a condições podem ser relevados
        // quando a condição não se aplica ao proponente (ex.: não-cotista c/ Raça/Cor).
        if (!$isExempt) {
            $conditions = self::extractConditions($config);
            if (!empty($conditions)) {
                $isExempt = $this->isExemptWithConditions($registration, $agent, $sealIds, $conditions);
            }
        }

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
     * Thin wrapper para compatibilidade retroativa.
     */
    public function grantValidatorSealsAfterManualApproval(Registration $registration): bool
    {
        return $this->grantValidatorSealsAfterEvaluationResult($registration);
    }

    /**
     * Ponto de entrada unificado para concessão de selos pós-avaliação.
     * Bifurca por status:
     * - Status 10 (Selecionada): concede TODOS os selos (comportamento histórico).
     * - Status 2 (Inválida) / 3 (Não selecionada) / 8 (Suplente): concessão
     *   PARCIAL — apenas selos cujos campos invalidadores foram 100% validados.
     * - Status 0 (Rascunho) / 1 (Pendente): nenhum selo.
     *
     * @param Registration $registration
     * @return bool True quando o processo de concessão foi executado.
     */
    public function grantValidatorSealsAfterEvaluationResult(Registration $registration): bool
    {
        $status = $registration->status;
        $grantableStatuses = [
            Registration::STATUS_APPROVED,    // 10
            Registration::STATUS_NOTAPPROVED, // 3
            Registration::STATUS_WAITLIST,    // 8
            Registration::STATUS_INVALID,     // 2
        ];
        if (!in_array($status, $grantableStatuses, true)) {
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
        if (!$sealIds) {
            return false;
        }

        // BIFURCAÇÃO por status
        if ($status === Registration::STATUS_APPROVED) {
            // Status 10: comportamento atual — concede todos
            if (!$this->canGrantValidatorSealsAfterSelection($registration, $sealIds)) {
                return false;
            }
            $sealsToGrant = $sealIds;
        } else {
            // Status 2/3/8: concessão parcial por selo
            $sealsToGrant = $this->getGrantableSealsForPartialGrant($registration, $sealIds);
        }

        if (empty($sealsToGrant)) {
            return false;
        }

        $agent = $this->agentResolver->resolve($registration);
        if (!$agent instanceof Agent) {
            return false;
        }

        return $this->applySealGrants($agent, $sealsToGrant, $registration);
    }

    /**
     * Aplica a concessão de selos ao agente proponente.
     *
     * Idempotente: verifica via findOneBy se a relação selo→agente já existe
     * antes de criar e renovar.
     *
     * @param Agent $agent
     * @param int[] $sealIds
     * @param Registration $registration
     * @return bool
     */
    private function applySealGrants(Agent $agent, array $sealIds, Registration $registration): bool
    {
        $app = App::i();
        $applyingAgent = $registration->opportunity?->owner ?: $agent;
        $relationClass = $agent->getSealRelationEntityClassName();

        $app->disableAccessControl();
        try {
            foreach ($sealIds as $sealId) {
                $seal = $app->repo('Seal')->find($sealId);
                if (!$seal instanceof Seal) {
                    continue;
                }

                // Idempotência: não renova nem duplica relação pré-existente
                $existing = $app->repo($relationClass)->findOneBy(['seal' => $seal, 'owner' => $agent]);
                if ($existing) {
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
     * Campos com condição em sealExemptionConfig.conditions cuja cláusula
     * não se aplica à inscrição (relevados) não entram na exigência.
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

        $bindingsBySeal = $this->getRequiredFieldBindingsBySeal($registration, $sealIds);
        if (!$bindingsBySeal) {
            return false;
        }

        $conditions = self::extractConditions(
            $registration->evaluationMethodConfiguration?->sealExemptionConfig
        );

        $evaluatedFields = $this->getValidEvaluatedFieldIds($registration);

        foreach ($sealIds as $sealId) {
            $bindings = $bindingsBySeal[$sealId] ?? [];
            if (!$bindings) {
                return false;
            }

            $requiredFieldIds = $this->excludeWaivedFieldIds(
                $registration,
                (int) $sealId,
                $bindings,
                $conditions
            );

            if (!$requiredFieldIds) {
                // Todos os campos do selo foram relevados por condição → OK
                continue;
            }

            if (!$evaluatedFields) {
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
     * Mapeia selo => bindings (fieldId + lockedField) dos campos do formulário
     * que validam aquele selo na fase.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @param bool $invalidatorsOnly Quando true, considera só locked fields com isInvalidator.
     * @return array<int, list<array{fieldId: int, lockedField: string}>>
     */
    private function getRequiredFieldBindingsBySeal(
        Registration $registration,
        array $sealIds,
        bool $invalidatorsOnly = false
    ): array {
        $app = App::i();
        $sealFields = [];

        foreach ($sealIds as $sealId) {
            $seal = $app->repo('Seal')->find($sealId);
            if (!$seal instanceof Seal) {
                continue;
            }

            $config = (array) $seal->lockedFieldsConfig;
            foreach ($config as $lockedField => $fieldConfig) {
                if ($invalidatorsOnly && ($fieldConfig['isInvalidator'] ?? false) !== true) {
                    continue;
                }
                $sealFields[$sealId][] = $lockedField;
            }
        }

        if (!$sealFields) {
            return [];
        }

        $result = [];
        $first_phase = $registration->opportunity->firstPhase;
        $phases = $first_phase->allPhases ?: [$first_phase];
        foreach ($phases as $phase) {
            foreach ($phase->registrationFieldConfigurations as $field) {
                $entityField = $this->getRegistrationFieldEntityName($field);
                if (!$entityField) {
                    continue;
                }

                foreach ($sealFields as $sealId => $lockedFields) {
                    foreach ($lockedFields as $lockedField) {
                        if ($this->lockedFieldMatchesRegistrationField($lockedField, $entityField)) {
                            $result[$sealId][] = [
                                'fieldId' => (int) $field->id,
                                'lockedField' => (string) $lockedField,
                            ];
                        }
                    }
                }
            }
        }

        return $result;
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
        $result = [];
        foreach ($this->getRequiredFieldBindingsBySeal($registration, $sealIds) as $sealId => $bindings) {
            $result[$sealId] = array_values(array_unique(array_column($bindings, 'fieldId')));
        }

        return $result;
    }

    /**
     * Remove da lista de campos exigidos aqueles relevados por conditions
     * (condição não satisfeita / sem branco).
     *
     * @param Registration $registration
     * @param int $sealId
     * @param list<array{fieldId: int, lockedField: string}> $bindings
     * @param array $conditions extractConditions()
     * @return int[]
     */
    private function excludeWaivedFieldIds(
        Registration $registration,
        int $sealId,
        array $bindings,
        array $conditions
    ): array {
        if (!$bindings) {
            return [];
        }

        $sealConditions = $conditions[$sealId] ?? $conditions[(string) $sealId] ?? [];
        if (!is_array($sealConditions) || !$sealConditions) {
            return array_values(array_unique(array_column($bindings, 'fieldId')));
        }

        $fieldIds = [];
        foreach ($bindings as $binding) {
            $lockedField = $binding['lockedField'] ?? '';
            $condition = $sealConditions[$lockedField] ?? null;
            if ($condition && $this->isInvalidadorWaived($condition, $registration)) {
                continue;
            }
            $fieldIds[] = (int) $binding['fieldId'];
        }

        return array_values(array_unique($fieldIds));
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
     * Estrutura: { emc_id, seal_ids, agent_id }
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

    /**
     * Retorna set de field IDs marcados como "valid" nas avaliações enviadas.
     *
     * @param Registration $registration
     * @return array<string, bool>
     */
    private function getValidEvaluatedFieldIds(Registration $registration): array
    {
        $evaluations = $registration->sentEvaluations;
        if (!$evaluations || count($evaluations) === 0) {
            return [];
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

        return $evaluatedFields;
    }

    /**
     * Retorna IDs de selos que possuem pelo menos um campo isInvalidator=true.
     *
     * @param int[] $sealIds
     * @return int[]
     */
    private function getSealsWithInvalidators(array $sealIds): array
    {
        $app = App::i();
        $result = [];
        foreach ($sealIds as $sealId) {
            $seal = $app->repo('Seal')->find($sealId);
            if (!$seal instanceof Seal) {
                continue;
            }
            foreach ((array) $seal->lockedFieldsConfig as $fieldConfig) {
                if (($fieldConfig['isInvalidator'] ?? false) === true) {
                    $result[] = $sealId;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Mapeia selo => IDs de campos da inscrição, filtrando apenas invalidadores.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @return array<int, array<int>>
     */
    private function getInvalidatorFieldIdsBySeal(Registration $registration, array $sealIds): array
    {
        $result = [];
        foreach ($this->getRequiredFieldBindingsBySeal($registration, $sealIds, true) as $sealId => $bindings) {
            $result[$sealId] = array_values(array_unique(array_column($bindings, 'fieldId')));
        }

        return $result;
    }

    /**
     * Determina quais selos são concedíveis na concessão parcial (status 2/3/8).
     *
     * Métodos não-documentais: retorna [] (sem dados granulares para decidir).
     * Invalidadores relevados por conditions não entram na exigência do selo.
     *
     * @param Registration $registration
     * @param int[] $sealIds
     * @return int[] IDs dos selos concedíveis.
     */
    private function getGrantableSealsForPartialGrant(Registration $registration, array $sealIds): array
    {
        if (!$this->isDocumentaryEvaluation($registration)) {
            return [];
        }

        $evaluatedFields = $this->getValidEvaluatedFieldIds($registration);

        $sealsWithInvalidators = $this->getSealsWithInvalidators($sealIds);
        $invalidatorBindingsBySeal = $this->getRequiredFieldBindingsBySeal($registration, $sealIds, true);
        $conditions = self::extractConditions(
            $registration->evaluationMethodConfiguration?->sealExemptionConfig
        );

        $grantable = [];
        foreach ($sealIds as $sealId) {
            $hasInvalidators = in_array($sealId, $sealsWithInvalidators, true);

            if (!$hasInvalidators) {
                // Selo sem invalidadores → sempre concedível
                $grantable[] = $sealId;
                continue;
            }

            $bindings = $invalidatorBindingsBySeal[$sealId] ?? [];
            if (empty($bindings)) {
                // Invalidadores configurados mas não resolvem no formulário → não conceder (fail-safe)
                continue;
            }

            $requiredFields = $this->excludeWaivedFieldIds(
                $registration,
                (int) $sealId,
                $bindings,
                $conditions
            );

            if (empty($requiredFields)) {
                // Todos os invalidadores relevados por condição → concedível
                $grantable[] = $sealId;
                continue;
            }

            if (!$evaluatedFields) {
                continue;
            }

            $allValid = true;
            foreach ($requiredFields as $fieldId) {
                if (empty($evaluatedFields[(string) $fieldId])) {
                    $allValid = false;
                    break;
                }
            }
            if ($allValid) {
                $grantable[] = $sealId;
            }
        }

        return $grantable;
    }

    // ===================================================================
    // Condicionalidade de invalidadores (spec-fe9b2cfc)
    // ===================================================================

    /**
     * Recalcula e persiste computed_status de uma SealRelation levando em conta
     * a estrutura genérica `conditions` dos editais (sealExemptionConfig).
     *
     * Usado pelo job NotifySealExpirations. Não altera o fluxo de isenção.
     *
     * Regra:
     * - Sem conditions aplicáveis ao selo → status bruto (getComputedStatus).
     * - Com conditions + inscrições encontradas: se em TODOS os contextos o
     *   selo é efetivamente válido (invalidadores vencidos relevados quando a
     *   condição não se aplica), grava fully_valid; senão mantém o status bruto.
     *
     * @param SealRelation $relation
     * @return void
     */
    public function recomputeSealRelationComputedStatus(SealRelation $relation): void
    {
        $app = App::i();

        // Recarrega relação/campos do banco (expiry_date pode ter sido
        // atualizado via SQL ou por outro processo).
        if ($relation->id) {
            $app->em->refresh($relation);
            foreach ($relation->getSealRelationFields() as $field) {
                $app->em->refresh($field);
            }
        }

        // Sempre sincroniza a propriedade ORM com o status bruto primeiro.
        // Importante: NÃO usar $relation->computedStatus na leitura — o magic
        // getter chama getComputedStatus() (cálculo ao vivo) e mascara o valor
        // persistido, impedindo o flush de corrigir status stale.
        $relation->updateComputedStatus();

        if (!($relation instanceof AgentSealRelation)) {
            return;
        }

        $raw = $relation->getComputedStatus();
        if ($raw === 'fully_valid') {
            return;
        }

        $owner = $relation->owner;
        if (!$owner instanceof Agent) {
            return;
        }

        $contexts = $this->findConditionContextsForSealRelation($relation);
        if (empty($contexts)) {
            return;
        }

        foreach ($contexts as [$registration, $sealConditions]) {
            if (!$this->isSealEffectivelyValid($relation, $sealConditions, $registration)) {
                // Mantém o status bruto já gravado por updateComputedStatus().
                return;
            }
        }

        // Todos os contextos relevam os invalidadores condicionados → válido.
        $relation->computedStatus = 'fully_valid';
    }

    /**
     * Localiza contextos (inscrição na 1ª fase + conditions do selo) em que
     * este AgentSealRelation participa via sealExemptionConfig.
     *
     * @param AgentSealRelation $relation
     * @return list<array{0: Registration, 1: array}>
     */
    private function findConditionContextsForSealRelation(AgentSealRelation $relation): array
    {
        $app = App::i();
        $sealId = (int) $relation->seal->id;
        $agent = $relation->owner;
        if (!$agent instanceof Agent) {
            return [];
        }

        $rows = $app->em->getConnection()->fetchAllAssociative(
            'SELECT object_id, value FROM evaluationmethodconfiguration_meta WHERE key = ?',
            ['sealExemptionConfig']
        );

        $contexts = [];
        foreach ($rows as $row) {
            $config = self::normalizeConfig(json_decode((string) $row['value'], true));
            if (!self::hasActiveConfig($config)) {
                continue;
            }

            if (!in_array($sealId, self::getConfiguredSealIds($config), true)) {
                continue;
            }

            $conditions = $config['conditions'] ?? [];
            if (!is_array($conditions) || empty($conditions)) {
                continue;
            }

            $sealConditions = $conditions[$sealId] ?? $conditions[(string) $sealId] ?? null;
            if (!is_array($sealConditions) || empty($sealConditions)) {
                continue;
            }

            $emc = $app->repo('EvaluationMethodConfiguration')->find((int) $row['object_id']);
            if (!$emc instanceof EvaluationMethodConfiguration || !$emc->opportunity) {
                continue;
            }

            $opportunity = $emc->opportunity;
            $firstPhase = $opportunity->firstPhase ?: $opportunity;

            // Preferência: inscrição na 1ª fase (onde ficam campos como appliedForQuota).
            // Fallback: inscrição na própria fase do EMC (padrão de alguns testes/sync).
            $registration = $app->repo('Registration')->findOneBy([
                'opportunity' => $firstPhase,
                'owner' => $agent,
            ]);
            if (
                (!$registration instanceof Registration || $registration->status < 0)
                && (int) $opportunity->id !== (int) $firstPhase->id
            ) {
                $registration = $app->repo('Registration')->findOneBy([
                    'opportunity' => $opportunity,
                    'owner' => $agent,
                ]);
            }

            if (!$registration instanceof Registration || $registration->status < 0) {
                continue;
            }

            $contexts[] = [$registration, $sealConditions];
        }

        return $contexts;
    }

    /**
     * Extrai as condições de relevação da configuração do EMC.
     *
     * Estrutura esperada (spec D1):
     *   conditions: { "<sealId>": { "<invalidatorKey>": { clauses: [ {field, values} ] } } }
     *
     * @param object|null $config
     * @return array Estrutura de condições normalizada (vazia se ausente).
     */
    public static function extractConditions(?object $config): array
    {
        if (!$config || !isset($config->conditions)) {
            return [];
        }

        $conditions = $config->conditions;
        if (is_object($conditions)) {
            $conditions = json_decode(json_encode($conditions), true);
        }

        return is_array($conditions) ? $conditions : [];
    }

    /**
     * Validação estrutural das condições (T2).
     *
     * Verifica forma: sealId → invalidatorKey → { clauses: [{ field, values }] }.
     * A validação cruzada (invalidador existe e é invalidador no selo) é feita
     * em validateConditionsAgainstSeals, separada por exigir carga dos selos.
     *
     * @param mixed $config
     * @return string|null Mensagem de erro ou null se válido.
     */
    public static function validateConditionsStructure($config): ?string
    {
        $config = self::normalizeConfig($config);
        $conditions = $config['conditions'] ?? null;

        if ($conditions === null) {
            return null;
        }

        if (!is_array($conditions)) {
            return i::__('O campo "conditions" deve ser um objeto.');
        }

        foreach ($conditions as $sealId => $invalidators) {
            if (!is_array($invalidators) || empty($invalidators)) {
                return i::__('Cada selo em "conditions" deve ter ao menos um invalidador.');
            }

            foreach ($invalidators as $invalidatorKey => $condDef) {
                if (!is_string($invalidatorKey) || $invalidatorKey === '') {
                    return i::__('A chave do invalidador deve ser um fieldKey não vazio.');
                }

                $clauses = $condDef['clauses'] ?? null;
                if (!is_array($clauses) || empty($clauses)) {
                    return i::__('Cada invalidador deve ter ao menos uma cláusula em "clauses".');
                }

                foreach ($clauses as $clause) {
                    $field = $clause['field'] ?? null;
                    $values = $clause['values'] ?? null;

                    if (!is_string($field) || $field === '') {
                        return i::__('O campo "field" da cláusula deve ser uma string não vazia.');
                    }
                    if (!is_array($values) || empty($values)) {
                        return i::__('O campo "values" da cláusula deve ser um array não vazio.');
                    }
                    foreach ($values as $v) {
                        if (!is_scalar($v)) {
                            return i::__('Os valores aceitos devem ser escalares.');
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Validação cruzada: cada invalidador referenciado em conditions deve existir
     * e ser invalidador (isInvalidator===true) no lockedFieldsConfig do selo.
     *
     * @param mixed $config
     * @return string|null Mensagem de erro ou null se válido.
     */
    public static function validateConditionsAgainstSeals($config): ?string
    {
        $config = self::normalizeConfig($config);
        $conditions = $config['conditions'] ?? null;
        $sealIds = self::getConfiguredSealIds($config);

        if (!$conditions || !$sealIds) {
            return null;
        }

        $app = App::i();
        foreach ($conditions as $sealIdStr => $invalidators) {
            $sealId = (int) $sealIdStr;
            if (!in_array($sealId, $sealIds, true)) {
                return sprintf(i::__('O selo %d em "conditions" não está na lista de selos configurados.'), $sealId);
            }

            $seal = $app->repo('Seal')->find($sealId);
            if (!$seal instanceof Seal) {
                return sprintf(i::__('Selo %d não encontrado.'), $sealId);
            }

            $lockedConfig = (array) $seal->lockedFieldsConfig;
            foreach (array_keys($invalidators) as $invalidatorKey) {
                $fieldConfig = $lockedConfig[$invalidatorKey] ?? null;
                if (!is_array($fieldConfig)) {
                    return sprintf(i::__('O invalidador "%s" não existe no selo %d.'), $invalidatorKey, $sealId);
                }
                if (($fieldConfig['isInvalidator'] ?? false) !== true) {
                    return sprintf(i::__('O campo "%s" do selo %d não é um invalidador.'), $invalidatorKey, $sealId);
                }
            }
        }

        return null;
    }

    /**
     * Verifica isenção considerando relevação condicional de invalidadores.
     *
     * Para cada selo NÃO fully_valid, inspeciona seus SealRelationField: se todos
     * os invalidadores vencidos puderem ser relevados por condições não-satisfeitas
     * (e não houver outros campos vencidos), o selo é efetivamente válido.
     *
     * @param Registration $registration
     * @param Agent $agent
     * @param int[] $sealIds
     * @param array $conditions Estrutura de conditions (extractConditions).
     * @return bool
     */
    private function isExemptWithConditions(
        Registration $registration,
        Agent $agent,
        array $sealIds,
        array $conditions
    ): bool {
        $app = App::i();

        foreach ($sealIds as $sealId) {
            // Caminho rápido: selo já fully_valid via computed_status
            if ($this->isSealFullyValidForAgent($agent->id, $sealId)) {
                continue;
            }

            // Selo não fully_valid — buscar condições para este selo
            $sealConditions = $conditions[$sealId] ?? $conditions[(string) $sealId] ?? null;
            if (empty($sealConditions)) {
                // Sem condições para relevação → selo falha
                return false;
            }

            $relation = $app->repo('AgentSealRelation')->findOneBy([
                'seal' => $sealId,
                'owner' => $agent,
                'status' => 1,
            ]);

            if (!$relation) {
                return false;
            }

            if (!$this->isSealEffectivelyValid($relation, $sealConditions, $registration)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica via SQL se o agente possui o selo como fully_valid (computed_status).
     *
     * @param int $agentId
     * @param int $sealId
     * @return bool
     */
    private function isSealFullyValidForAgent(int $agentId, int $sealId): bool
    {
        $app = App::i();
        $conn = $app->em->getConnection();

        $sql = "
            SELECT COUNT(1) AS cnt
            FROM seal_relation sr
            WHERE sr.object_type = ?
              AND sr.object_id = ?
              AND sr.status = 1
              AND sr.seal_id = ?
              AND sr.computed_status = 'fully_valid'
        ";

        return (int) $conn->executeQuery($sql, [
            'MapasCulturais\Entities\Agent',
            $agentId,
            $sealId,
        ])->fetchOne() > 0;
    }

    /**
     * Determina a validade efetiva de uma relação de selo aplicando relevação.
     *
     * Um campo vencido invalidador COM condição relevável (condição não-satisfeita,
     * sem branco) é ignorado. Qualquer outro campo vencido (invalidador sem condição,
     * invalidador com condição satisfeita/branco, ou não-invalidador) faz o selo
     * NÃO ser efetivamente fully_valid.
     *
     * @param \MapasCulturais\Entities\AgentSealRelation $relation
     * @param array $sealConditions Condições do selo: invalidatorKey → { clauses }
     * @param Registration $registration
     * @return bool
     */
    private function isSealEffectivelyValid(
        \MapasCulturais\Entities\AgentSealRelation $relation,
        array $sealConditions,
        Registration $registration
    ): bool {
        // Leitura via SQL fresco (evita stale cache do Doctrine em testes / concorrência).
        $app = App::i();
        $conn = $app->em->getConnection();
        $rows = $conn->fetchAllAssociative(
            "SELECT field_name, is_invalidator, expiry_date
             FROM seal_relation_field
             WHERE seal_relation_id = :rid",
            ['rid' => $relation->id]
        );

        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->setTime(0, 0, 0);

        foreach ($rows as $row) {
            // Verificar se está vencido
            $expiry = $row['expiry_date'];
            if ($expiry === null) {
                continue; // sem expiração → não vence
            }
            $expiryDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $expiry, new \DateTimeZone('UTC'));
            if ($expiryDate >= $now) {
                continue; // não vencido
            }

            // Campo vencido
            $isInvalidator = (bool) $row['is_invalidator'];
            if (!$isInvalidator) {
                // Não-invalidador vencido → partially_valid → não é fully_valid
                return false;
            }

            $lockedFieldKey = $row['field_name'];
            $condition = $sealConditions[$lockedFieldKey] ?? null;

            if (!$condition) {
                // Invalidador vencido sem condição → aplica
                return false;
            }

            if (!$this->isInvalidadorWaived($condition, $registration)) {
                // Condição não releva (branco ou satisfeita) → aplica
                return false;
            }
            // Relevado — continua verificando os demais campos
        }

        return true;
    }

    /**
     * Determina se um invalidador deve ser relevado para esta inscrição.
     *
     * Regra (spec — Regra de Negócio):
     * - Branco (qualquer campo condicional vazio) → NÃO relevado (branco invalida).
     * - Todas as cláusulas satisfeitas → NÃO relevado (condição atendida, aplica normal).
     * - Alguma cláusula não-satisfeita (sem branco) → RELEVADO.
     *
     * @param array|object $condition { clauses: [{ field, values }] }
     * @param Registration $registration
     * @return bool True se o invalidador deve ser relevado.
     */
    private function isInvalidadorWaived($condition, Registration $registration): bool
    {
        $clauses = is_object($condition) ? ($condition->clauses ?? []) : ($condition['clauses'] ?? []);

        foreach ($clauses as $clause) {
            $clause = (array) $clause;
            $fieldName = $clause['field'] ?? '';
            $accepted = $clause['values'] ?? [];

            $value = $this->readRegistrationFieldValue($registration, $fieldName);

            // Branco → não relevado (branco invalida)
            if ($this->isBlankValue($value)) {
                return false;
            }

            // Cláusula não satisfeita → relevado
            if (!$this->valueMatchesAccepted($value, $accepted)) {
                return true;
            }
        }

        // Todas as cláusulas satisfeitas → não relevado
        return false;
    }

    /**
     * Lê o valor de um campo do formulário da inscrição (metadata registrada).
     *
     * @param Registration $registration
     * @param string $fieldName Ex.: 'appliedForQuota', 'field_42'
     * @return mixed
     */
    private function readRegistrationFieldValue(Registration $registration, string $fieldName)
    {
        $app = App::i();
        $app->disableAccessControl();
        try {
            $value = $registration->getMetadata($fieldName);
        } finally {
            $app->enableAccessControl();
        }

        return $value;
    }

    /**
     * Verifica se o valor é considerado "branco" (vazio/nulo).
     *
     * @param mixed $value
     * @return bool
     */
    private function isBlankValue($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        // Boolean false NÃO é branco — é um valor deliberado (Desmarcado)
        return false;
    }

    /**
     * Verifica se o valor do campo satisfaz a cláusula (igualdade, OU entre aceitos).
     *
     * @param mixed $fieldValue
     * @param array $acceptedValues
     * @return bool
     */
    private function valueMatchesAccepted($fieldValue, array $acceptedValues): bool
    {
        $norm = $this->normalizeComparable($fieldValue);

        // Campo multivalorado (checkboxes/JSON) → interseção não-vazia
        if (is_array($norm)) {
            foreach ($norm as $v) {
                if ($this->anyAcceptedMatches($v, $acceptedValues)) {
                    return true;
                }
            }
            return false;
        }

        return $this->anyAcceptedMatches($norm, $acceptedValues);
    }

    /**
     * @param mixed $scalarValue
     * @param array $acceptedValues
     * @return bool
     */
    private function anyAcceptedMatches($scalarValue, array $acceptedValues): bool
    {
        $target = $this->normalizeComparable($scalarValue);
        foreach ($acceptedValues as $accepted) {
            if ($this->normalizeComparable($accepted) === $target) {
                return true;
            }
        }
        return false;
    }

    /**
     * Normaliza um valor para comparação por igualdade.
     *
     * Booleanos e suas representações em string ('1'/'0', 'true'/'false') são
     * normalizados para '1'/'0'. Strings são trimadas. JSON de arrays é decodificado.
     *
     * @param mixed $v
     * @return string|array
     */
    private function normalizeComparable($v)
    {
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }
        if ($v === 'true') {
            return '1';
        }
        if ($v === 'false') {
            return '0';
        }
        if (is_string($v)) {
            $decoded = json_decode($v, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            // '1'/'0' numéricos para campos booleanos armazenados como string
            if ($v === '1') {
                return '1';
            }
            if ($v === '0') {
                return '0';
            }
            return trim($v);
        }
        if (is_array($v)) {
            return $v;
        }

        return (string) $v;
    }

    /**
     * Identifica quais campos invalidadores dos selos não têm campo
     * correspondente no formulário da oportunidade.
     *
     * @param Opportunity $firstPhase
     * @param int[] $sealIds
     * @return array<int, array{missing: list<array{fieldKey: string, label: string}>}>
     */
    public function getMissingInvalidatorsBySeal(Opportunity $firstPhase, array $sealIds): array
    {
        $app = App::i();
        $result = [];

        // Coleta todos os entityFields presentes no formulário
        $formEntityFields = [];
        $phases = $firstPhase->allPhases ?: [$firstPhase];
        foreach ($phases as $phase) {
            foreach ($phase->registrationFieldConfigurations as $field) {
                $entityField = $this->getRegistrationFieldEntityName($field);
                if ($entityField) {
                    $formEntityFields[$entityField] = true;
                }
            }
        }

        foreach ($sealIds as $sealId) {
            $seal = $app->repo('Seal')->find($sealId);
            $result[$sealId] = ['missing' => []];
            if (!$seal instanceof Seal) {
                continue;
            }

            $config = (array) $seal->lockedFieldsConfig;
            foreach ($config as $lockedField => $fieldConfig) {
                if (($fieldConfig['isInvalidator'] ?? false) !== true) {
                    continue;
                }

                $parts = explode('.', $lockedField, 2);
                $fieldName = $parts[1] ?? $lockedField;

                if (!isset($formEntityFields[$fieldName])) {
                    $result[$sealId]['missing'][] = [
                        'fieldKey' => $lockedField,
                        'label' => $lockedField,
                    ];
                }
            }
        }

        return $result;
    }
}

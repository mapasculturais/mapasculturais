<?php
namespace SealExemption;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;

/**
 * Verifica se um agente possui TODOS os selos configurados como plenamente
 * válidos (fully_valid).
 *
 * Regras (especificação seção 3.5):
 * - O predicado é estritamente getComputedStatus() === 'fully_valid'.
 * - partially_valid NÃO conta como válido (decisão de produto — princípio
 *   conservador).
 * - STATUS_PENDING (-5) é tratado como inválido (o filtro status = 1 na query
 *   exclui pendentes).
 * - A verificação exige TODOS os selos configurados (lógica AND).
 *
 * @package SealExemption
 */
class SealExemptionVerifier
{
    /**
     * Discriminator value para relações de selo com agentes.
     */
    private const AGENT_OBJECT_TYPE = 'MapasCulturais\Entities\Agent';

    /**
     * Status ativo da SealRelation.
     */
    private const SEAL_RELATION_STATUS_ENABLED = 1;

    /**
     * Verifica se o agente possui todos os selos configurados como fully_valid.
     *
     * Método unitário — usa uma query SQL agregada para contar selos válidos
     * do agente entre os selos configurados.
     *
     * @param Agent $agent O agente proponente.
     * @param array $sealIds IDs dos selos configurados como validadores.
     * @return bool True se o agente possui TODOS os selos como fully_valid.
     */
    public function hasAllValidSeals(Agent $agent, array $sealIds): bool
    {
        if (empty($sealIds)) {
            return false;
        }

        $requiredCount = count($sealIds);
        $validCount = $this->countValidSeals($agent->id, $sealIds);

        return $validCount >= $requiredCount;
    }

    /**
     * Verificação em lote: retorna os IDs de agentes que possuem TODOS os
     * selos configurados como fully_valid.
     *
     * Usa uma única query agregada para evitar N+1 durante o sync de fases.
     * O índice parcial idx_seal_relation_agent_valid (criado na migration T1)
     * cobre exatamente este predicado.
     *
     * @param array $agentIds IDs dos agentes a verificar.
     * @param array $sealIds  IDs dos selos configurados como validadores.
     * @return array Lista de agent_id que possuem todos os selos válidos.
     */
    public function findAgentsWithAllValidSeals(array $agentIds, array $sealIds): array
    {
        if (empty($agentIds) || empty($sealIds)) {
            return [];
        }

        $requiredCount = count($sealIds);

        $app = App::i();
        $conn = $app->em->getConnection();

        // Parâmetros posicionais para IN clauses
        $agentPlaceholders = implode(',', array_fill(0, count($agentIds), '?'));
        $sealPlaceholders = implode(',', array_fill(0, count($sealIds), '?'));

        $sql = "
            SELECT sr.object_id AS agent_id
            FROM seal_relation sr
            WHERE sr.object_type = ?
              AND sr.object_id IN ({$agentPlaceholders})
              AND sr.status = ?
              AND sr.seal_id IN ({$sealPlaceholders})
              AND sr.computed_status = 'fully_valid'
            GROUP BY sr.object_id
            HAVING COUNT(DISTINCT sr.seal_id) >= ?
        ";

        $params = array_merge(
            [self::AGENT_OBJECT_TYPE],
            array_map('intval', $agentIds),
            [self::SEAL_RELATION_STATUS_ENABLED],
            array_map('intval', $sealIds),
            [$requiredCount]
        );

        $result = $conn->executeQuery($sql, $params);

        $exemptAgents = [];
        foreach ($result->fetchAllAssociative() as $row) {
            $exemptAgents[] = (int) $row['agent_id'];
        }

        return $exemptAgents;
    }

    /**
     * Conta quantos dos selos configurados o agente possui como fully_valid.
     *
     * @param int $agentId ID do agente.
     * @param array $sealIds IDs dos selos configurados.
     * @return int Número de selos válidos (0 a count($sealIds)).
     */
    private function countValidSeals(int $agentId, array $sealIds): int
    {
        $app = App::i();
        $conn = $app->em->getConnection();

        $sealPlaceholders = implode(',', array_fill(0, count($sealIds), '?'));

        $sql = "
            SELECT COUNT(DISTINCT sr.seal_id) AS valid_count
            FROM seal_relation sr
            WHERE sr.object_type = ?
              AND sr.object_id = ?
              AND sr.status = ?
              AND sr.seal_id IN ({$sealPlaceholders})
              AND sr.computed_status = 'fully_valid'
        ";

        $params = array_merge(
            [self::AGENT_OBJECT_TYPE, $agentId, self::SEAL_RELATION_STATUS_ENABLED],
            array_map('intval', $sealIds)
        );

        $result = $conn->executeQuery($sql, $params);

        return (int) $result->fetchOne();
    }
}

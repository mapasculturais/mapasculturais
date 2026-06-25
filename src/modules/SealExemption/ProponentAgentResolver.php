<?php
namespace SealExemption;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;

/**
 * Resolve o agente proponente correto de uma inscrição conforme o tipo de
 * proponente e a configuração de agente coletivo da oportunidade.
 *
 * Regras (especificação seção 3.5):
 * - Pessoa Física ou MEI      → registration.owner (agente individual)
 * - Pessoa Jurídica ou Coletivo:
 *     - Se firstPhase.useAgentRelationColetivo in ['required','optional']
 *         → agente do grupo 'coletivo' vinculado à inscrição
 *     - Caso contrário
 *         → registration.owner
 *
 * @package SealExemption
 */
class ProponentAgentResolver
{
    /**
     * Tipos de proponente cujo agente verificado é sempre o owner (individual).
     */
    private const INDIVIDUAL_TYPES = ['Pessoa Física', 'MEI'];

    /**
     * Tipos de proponente que podem usar agente coletivo.
     */
    private const COLLECTIVE_TYPES = ['Pessoa Jurídica', 'Coletivo'];

    /**
     * Valores de useAgentRelationColetivo que habilitam o agente coletivo.
     */
    private const COLLECTIVE_ENABLED_MODES = ['required', 'optional'];

    /**
     * Resolve o agente proponente de uma inscrição.
     *
     * @param Registration $registration
     * @return Agent|null Retorna o agente ou null se não for possível identificá-lo.
     */
    public function resolve(Registration $registration): ?Agent
    {
        $proponentType = $registration->proponentType;

        // Tipos individuais: sempre o owner
        if (in_array($proponentType, self::INDIVIDUAL_TYPES, true)) {
            return $this->resolveOwner($registration);
        }

        // Tipos coletivos: depende da configuração da oportunidade
        if (in_array($proponentType, self::COLLECTIVE_TYPES, true)) {
            return $this->resolveCollective($registration);
        }

        // Tipo desconhecido: tenta o owner como fallback
        return $this->resolveOwner($registration);
    }

    /**
     * Resolve o agente individual (owner) da inscrição.
     *
     * @param Registration $registration
     * @return Agent|null
     */
    private function resolveOwner(Registration $registration): ?Agent
    {
        $owner = $registration->owner;

        if ($owner && $owner->id) {
            return $owner;
        }

        return null;
    }

    /**
     * Resolve o agente proponente para tipos coletivos (PJ/Coletivo).
     *
     * Se a oportunidade está configurada para usar agente coletivo, retorna o
     * agente do grupo 'coletivo'. Caso contrário, retorna o owner.
     *
     * @param Registration $registration
     * @return Agent|null
     */
    private function resolveCollective(Registration $registration): ?Agent
    {
        $opportunity = $registration->opportunity;

        if (!$opportunity) {
            return $this->resolveOwner($registration);
        }

        $firstPhase = $opportunity->firstPhase ?: $opportunity;

        $useColetivo = $firstPhase->useAgentRelationColetivo;

        if (in_array($useColetivo, self::COLLECTIVE_ENABLED_MODES, true)) {
            return $this->resolveCollectiveAgent($registration);
        }

        // Não usa agente coletivo: fallback para o owner
        return $this->resolveOwner($registration);
    }

    /**
     * Obtém o agente do grupo 'coletivo' vinculado à inscrição.
     *
     * @param Registration $registration
     * @return Agent|null
     */
    private function resolveCollectiveAgent(Registration $registration): ?Agent
    {
        $agents = $registration->getRelatedAgents('coletivo');

        if (!empty($agents) && isset($agents[0]) && $agents[0]->id) {
            return $agents[0];
        }

        // Agente coletivo não encontrado — retorna null para sinalizar "sem agente"
        return null;
    }
}

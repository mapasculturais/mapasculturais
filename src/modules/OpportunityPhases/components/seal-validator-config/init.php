<?php
/**
 * init.php — seal-validator-config
 *
 * Expõe para o frontend os selos que o gestor tem permissão de aplicar
 * (applySeal) e que estão ativos, além de um contador de selos ativos
 * sem permissão (rodapé de transparência do multiselect).
 *
 * Por selo também anexa:
 * - invalidators: campos invalidadores do selo (UI de condicionalidade)
 * - missingInvalidators: invalidadores ausentes no formulário (aviso amarelo)
 *
 * Spec §4.1 — "Selos sem permissão: Ocultos com contador de transparência".
 *
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Seal;
use MapasCulturais\Entities\Space;
use MapasCulturais\i;
use SealExemption\ProponentAgentResolver;
use SealExemption\SealExemptionService;
use SealExemption\SealExemptionVerifier;

require_once MODULES_PATH . 'SealExemption/ProponentAgentResolver.php';
require_once MODULES_PATH . 'SealExemption/SealExemptionVerifier.php';
require_once MODULES_PATH . 'SealExemption/SealExemptionService.php';

$app = App::i();

// Descrições de propriedades para resolver labels dos invalidadores
$agent_descriptions = Agent::getPropertiesMetadata();
$space_descriptions = Space::getPropertiesMetadata();
$agent_taxonomies = $app->getRegisteredTaxonomies('MapasCulturais\Entities\Agent');
$space_taxonomies = $app->getRegisteredTaxonomies('MapasCulturais\Entities\Space');

/**
 * Resolve a label humana de um lockedField (ex.: agent.raca → "Raça/Cor").
 */
$resolve_locked_field_label = function (string $lockedField) use (
    $agent_descriptions,
    $space_descriptions,
    $agent_taxonomies,
    $space_taxonomies
): string {
    $parts = explode('.', $lockedField, 2);
    $controller = $parts[0] ?? '';
    $fieldName = $parts[1] ?? $lockedField;

    // terms:taxonomy (ex.: agent.terms:area)
    if (strpos($fieldName, 'terms:') === 0) {
        $termSlug = substr($fieldName, 6);
        $taxonomies = ($controller === 'space') ? $space_taxonomies : $agent_taxonomies;
        foreach ($taxonomies as $tax) {
            if ($tax->slug === $termSlug) {
                return $tax->name ?: $lockedField;
            }
        }
        return $lockedField;
    }

    // Campo de entidade (agent.raca, space.name)
    $descriptions = ($controller === 'space') ? $space_descriptions : $agent_descriptions;
    if (isset($descriptions[$fieldName]['label'])) {
        return $descriptions[$fieldName]['label'];
    }

    return $lockedField;
};

$available_seals = [];
$controlled_ids = [];

// Selos ativos sobre os quais o usuário tem permissão de aplicar.
// getHasControlSeals() exige 'modify', portanto só roda em contexto autenticado.
if (!$app->user->is('guest')) {
    try {
        $controlled = $app->user->getHasControlSeals();
    } catch (\Throwable $e) {
        $controlled = [];
    }

    foreach ($controlled as $seal) {
        if ($seal instanceof Seal && $seal->status >= 0) {
            $controlled_ids[$seal->id] = true;

            // Invalidadores do selo (para condicionalidade — spec-fe9b2cfc)
            $invalidators = [];
            $lockedConfig = (array) $seal->lockedFieldsConfig;
            foreach ($lockedConfig as $lockedField => $fieldConfig) {
                if (($fieldConfig['isInvalidator'] ?? false) === true) {
                    $invalidators[] = [
                        'fieldKey' => $lockedField,
                        'label' => $resolve_locked_field_label($lockedField),
                    ];
                }
            }

            $available_seals[] = [
                'value' => (int) $seal->id,
                'label' => $seal->name,
                'invalidators' => $invalidators,
                'missingInvalidators' => [],
            ];
        }
    }
}

// Contagem de selos ativos sem permissão (transparência no rodapé do popover).
$denied_count = 0;
try {
    $all_active_query = new ApiQuery(Seal::class, ['status' => 'GTE(0)', '@select' => 'id']);
    $all_active_ids = $all_active_query->findIds();
    foreach ($all_active_ids as $id) {
        if (!isset($controlled_ids[$id])) {
            $denied_count++;
        }
    }
} catch (\Throwable $e) {
    // Em caso de falha, apenas omite o contador.
}

// Cruzamento selo × formulário: invalidadores ausentes nas fases da oportunidade.
$opportunity = null;
$requested = $this->controller->requestedEntity ?? null;
if ($requested instanceof Opportunity) {
    $opportunity = $requested->firstPhase ?: $requested;
} elseif ($requested instanceof EvaluationMethodConfiguration && $requested->opportunity) {
    $opportunity = $requested->opportunity->firstPhase ?: $requested->opportunity;
} elseif ($requested && method_exists($requested, 'getOpportunity')) {
    $from_entity = $requested->getOpportunity();
    if ($from_entity instanceof Opportunity) {
        $opportunity = $from_entity->firstPhase ?: $from_entity;
    }
}

if ($opportunity instanceof Opportunity && $available_seals) {
    try {
        $service = new SealExemptionService(
            new ProponentAgentResolver(),
            new SealExemptionVerifier()
        );
        $seal_ids = array_column($available_seals, 'value');
        $missing_by_seal = $service->getMissingInvalidatorsBySeal($opportunity, $seal_ids);

        foreach ($available_seals as &$seal_option) {
            $seal_id = (int) $seal_option['value'];
            $missing = $missing_by_seal[$seal_id]['missing'] ?? [];
            // Troca label técnica (agent.raca) pela label humana já resolvida acima.
            foreach ($missing as &$item) {
                $field_key = (string) ($item['fieldKey'] ?? '');
                if ($field_key !== '') {
                    $item['label'] = $resolve_locked_field_label($field_key);
                }
            }
            unset($item);
            $seal_option['missingInvalidators'] = $missing;
        }
        unset($seal_option);
    } catch (\Throwable $e) {
        // Falha na checagem não deve quebrar a tela de configuração.
    }
}

$this->jsObject['config']['sealValidatorConfig'] = [
    'availableSeals' => $available_seals,
    'deniedSealsCount' => (int) $denied_count,
    // Campos do formulário das fases, para o seletor de campo condicional
    // (mesmo padrão do registration-distribution-rule; inclui appliedForQuota).
    'conditionalFields' => $this->jsObject['config']['registrationFilterFields'] ?? [],
];

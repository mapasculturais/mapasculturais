<?php
/**
 * init.php — seal-validator-config
 *
 * Expõe para o frontend os selos que o gestor tem permissão de aplicar
 * (applySeal) e que estão ativos, além de um contador de selos ativos
 * sem permissão (rodapé de transparência do multiselect).
 *
 * Spec §4.1 — "Selos sem permissão: Ocultos com contador de transparência".
 *
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Seal;
use MapasCulturais\i;

$app = App::i();

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
            $available_seals[] = [
                'value' => (int) $seal->id,
                'label' => $seal->name,
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

$this->jsObject['config']['sealValidatorConfig'] = [
    'availableSeals' => $available_seals,
    'deniedSealsCount' => (int) $denied_count,
];

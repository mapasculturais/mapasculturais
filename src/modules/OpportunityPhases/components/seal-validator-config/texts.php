<?php
/**
 * texts.php — seal-validator-config
 * Strings localizadas (PT-BR para docs/comunicação; código em EN).
 *
 * i::__() aceita apenas (text, domain) — sem interpolação. Contagens são
 * montadas no frontend (Vue) ou via concatenação.
 */

use MapasCulturais\i;

return [
    'emptyState' => i::__('Nenhum selo validador configurado. A isenção automática está desativada nesta fase.'),
    'removeSuccess' => i::__('Selos validadores removidos. A isenção automática foi desativada nesta fase.'),
    'removeError' => i::__('Não foi possível remover os selos validadores. Tente novamente.'),
    'saveError' => i::__('Não foi possível salvar a configuração de selos validadores.'),
];

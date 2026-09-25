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
    'pendingTitle' => i::__('Confirme os campos no formulário de inscrição'),
    'pendingIntro' => i::__('Os selos abaixo usam campos que ainda não existem no formulário. Inclua esses campos na inscrição para que a validação automática por selos possa funcionar.'),
    'pendingSealPrefix' => i::__('Selo'),
    'pendingFieldsLabel' => i::__('Campos que faltam'),
    // Condicionalidade de invalidadores (spec-fe9b2cfc)
    'conditionsSection' => i::__('Condicionalidade de invalidadores'),
    'conditionsIntro' => i::__('Para cada selo, você pode fazer com que um campo invalidador só seja exigido quando o proponente preencher determinado campo do formulário. Quando a condição não se aplica, o invalidador é relevado (não barra o proponente).'),
    'conditionFieldLabel' => i::__('Campo do formulário'),
    'conditionFieldPlaceholder' => i::__('Selecione um campo do formulário'),
    'conditionValuesLabel' => i::__('Valores aceitos (basta um)'),
    'conditionAddClause' => i::__('Adicionar campo'),
    'conditionRemove' => i::__('Remover condição'),
    'conditionRemoveClause' => i::__('Remover campo'),
    'conditionBlankAlert' => i::__('Atenção: se o campo condicional estiver em branco, o invalidador é aplicado normalmente — ou seja, segue a configuração do selo. Assim, se qualquer campo invalidador estiver inválido, o selo se torna inválido automaticamente.'),
    'conditionPreviewTemplate' => i::__('O campo será exigido quando'),
    'conditionMarcado' => i::__('Marcado'),
    'conditionDesmarcado' => i::__('Desmarcado'),
    'conditionSim' => i::__('Sim'),
    'conditionNao' => i::__('Não'),
];

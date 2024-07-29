<?php

$visibleColumns = [
    'category',
    'number',
    'owner.name',
    'proponentType',
    'range',
    'score',
    'status'
];

$app->applyHook('component(opportunity-results-table).visibleColumns', [&$visibleColumns]);

$this->jsObject['config']['opportunityResultsTable']['visibleColumns'] = $visibleColumns;
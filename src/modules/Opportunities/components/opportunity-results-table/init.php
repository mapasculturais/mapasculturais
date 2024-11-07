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

$columns = [
    'category',
    'number',
    'agent',
    'proponentType',
    'range',
    'score',
    'status'
];

$app->applyHook('component(opportunity-results-table).visibleColumns', [&$visibleColumns]);

$this->jsObject['config']['opportunityResultsTable']['visibleColumns'] = $visibleColumns;
$this->jsObject['config']['opportunityResultsTable']['columns'] = $columns;
<?php

$showColumns = [
    'category',
    'number',
    'owner',
    'proponentType',
    'range',
    'score',
    'status'
];

$app->applyHook('component(opportunity-results-table).showColumns', [&$showColumns]);

$this->jsObject['config']['opportunityResultsTable']['showColumns'] = $showColumns;
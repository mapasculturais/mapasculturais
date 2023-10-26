<?php
$skiped_fields = [
    'agents' => [
        'createTimestamp',
        'updateTimestamp',
        'id',
        'location',
        'userId',
        'public',
        'sentNotification',
        'event_importer_processed_file',
        '_subsiteId',
        '_type',
    ],
    
    'spaces'  => [
        'createTimestamp',
        'updateTimestamp',
        'id',
        'location',
        'userId',
        'public',
        '_subsiteId',
        '_type',
    ]
];

$app->applyHook('component(seal-locked-fields).skiped_fields', [&$skiped_fields]);

$this->jsObject['config']['sealLockedSkipedFields'] = $skiped_fields;
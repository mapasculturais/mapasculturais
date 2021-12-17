<?php

return [
    'plugins' => [
        'EvaluationMethodTechnical' => ['namespace' => 'EvaluationMethodTechnical', 'config' => ['step' => 0.1]],
        'EvaluationMethodSimple' => ['namespace' => 'EvaluationMethodSimple'],
        'EvaluationMethodDocumentary' => ['namespace' => 'EvaluationMethodDocumentary'],
        "MapasNetwork" => [
            "namespace" => "MapasNetwork",
            "config" => [
                'nodes' => explode(",", env("MAPAS_NETWORK_NODES", "")),
                'filters' => [
                    'agent' => [ 'En_Estado' => 'MS' ],
                    'space' => [ 'En_Estado' => 'MS' ],
                ]
            ]
        ],
    ]
];
<?php

return [
    'plugins' => [
         'MultipleLocalAuth',
         'AdminLoginAsUser',
         'RecreatePCacheOnLogin',
         'SpamDetector',
         'MapasNetwork' => [
             'namespace' => 'MapasNetwork',
             'config' => [
                 'nodes' => [
                     'https://mapa.cultura.gov.br/',
                     'https://mapacultural.pa.gov.br/',
                     'https://mapacultural.secult.ce.gov.br/',
                 ],
                 'filters' => [
                     'agent' => ['En_Estado' => 'BR'],
                     'space' => ['En_Estado' => 'BR'],
                 ],
             ],
         ],
    ]
];

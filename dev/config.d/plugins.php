<?php

return [
	'plugins' => [
		'MultipleLocalAuth',
		'AdminLoginAsUser',
		'RecreatePCacheOnLogin',
		'SpamDetector',
		'MapasBlame',
		'Metabase' => [
			'namespace' => 'Metabase',
			'config' => [
				'enabled' => function () {
					$app = \MapasCulturais\App::i();

					// Gestão de metabase por tema
					$themes = array_keys(array_filter([
						'MapaMinC\Theme' => env('METABASE_ENABLED', false),
						'Funarte\Theme' => env('METABASE_FUNARTE_ENABLED', false),
						'CulturaViva\Theme' => env('METABASE_CULTURAVIVA_ENABLED', false),
					], fn($enabled) => $enabled));

					// Verifica se o tema atual está na lista de temas habilitados
					return in_array(get_class($app->view), $themes);
				},
			]
		],
	]
];

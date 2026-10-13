<?php

use MapasCulturais\i;

$this->assetManager->publishFolder('swagger-ui/', 'swagger-ui/');

$asset_url = $app->assetUrl . 'swagger-ui/';
$spec_url = $app->getBaseUrl() . 'api/documentation/openapi';

?>
<!DOCTYPE html>
<html lang="<?= str_replace('_', '-', $app->getCurrentLCode()) ?>" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= i::__('API pública') ?> — <?= htmlspecialchars($app->siteName) ?></title>
    <link rel="stylesheet" href="<?= $asset_url ?>swagger-ui.css">
    <style>
        body { margin: 0; background: #fafafa; }
        .swagger-ui .topbar,
        .swagger-ui .scheme-container { display: none; }
        .swagger-ui .info { margin: 32px 0; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="<?= $asset_url ?>swagger-ui-bundle.js"></script>
    <script>
        window.ui = SwaggerUIBundle({
            url: <?= json_encode($spec_url) ?>,
            dom_id: '#swagger-ui',
            deepLinking: true,
            docExpansion: 'list',
            tryItOutEnabled: true,
            presets: [SwaggerUIBundle.presets.apis],
        });
    </script>
</body>
</html>

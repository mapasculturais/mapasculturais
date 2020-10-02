<?php
$app_mode = env('APP_MODE', 'production');
$is_production = $app_mode == 'production';

return [
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem([
        'publishPath' => BASE_PATH . 'assets/',

        'mergeScripts' => $is_production,
        'mergeStyles' => $is_production,

        'process.js' => !$is_production ?
                'cp {IN} {OUT}':
                'terser {IN} --source-map --output {OUT} ',

        'process.css' => !$is_production ?
                'cp {IN} {OUT}':
                'uglifycss {IN} > {OUT}',

        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    ]),
];
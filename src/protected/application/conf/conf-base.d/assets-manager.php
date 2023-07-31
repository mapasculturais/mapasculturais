<?php
$app_mode = env('APP_MODE', 'production');
$is_production = $app_mode == 'production';

return [
    'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem([
        'publishPath' => BASE_PATH . 'assets/',

        'mergeScripts' =>  env('ASSETS_MERGE_SCRIPTS', $is_production),
        'mergeStyles' => env('ASSETS_MERGE_STYLES', $is_production),

        'process.js' => !$is_production ?
                'cp {IN} {OUT}':
                'terser {IN} --source-map --output {OUT} ',

        'process.css' => !$is_production ?
                'cp {IN} {OUT}':
                'uglifycss {IN} > {OUT}',

        'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
    ]),
];
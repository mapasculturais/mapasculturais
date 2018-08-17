<?php
$app_mode = env('APP_MODE', 'production');
$is_production = $app_mode == 'production';

return [
        'themes.assetManager' => new \MapasCulturais\AssetManagers\FileSystem(array(
            'publishPath' => BASE_PATH . 'assets/',

            'mergeScripts' => $is_production,
            'mergeStyles' => $is_production,

            'process.js' => !$is_production ?
                    'cp {IN} {OUT}':
                    'uglifyjs {IN} -o {OUT} --source-map {OUT}.map --source-map-include-sources --source-map-url /assets/{FILENAME}.map -b -p ' . substr_count(BASE_PATH, '/'),

            'process.css' => !$is_production ?
                    'cp {IN} {OUT}':
                    'uglifycss {IN} > {OUT}',

            'publishFolderCommand' => 'cp -R {IN} {PUBLISH_PATH}{FILENAME}'
        )),
];
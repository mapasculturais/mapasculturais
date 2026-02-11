<?php
$app_mode = env('APP_MODE', 'production');
$is_production = $app_mode == 'production';

return [
    'themes.assetManager' => [
        'publishPath' => BASE_PATH . 'assets/',

        // When merging, the AssetManager already concatenates file contents via
        // file_get_contents() before calling the process command. An empty process
        // pattern makes it fall back to file_put_contents() which writes the
        // pre-concatenated content directly — correct for both merge and single-file.
        // terser/uglifycss are not available in the production image.
        'mergeScripts' =>  env('ASSETS_MERGE_SCRIPTS', $is_production),
        'mergeStyles' => env('ASSETS_MERGE_STYLES', $is_production),

        // null: individual assets use PHP copy(), merged groups use file_put_contents()
        // (both are correct — no external tool needed since assets are pre-built)
        'process.js' => null,

        'process.css' => null,

        'publishFolderCommand' => 'cp -Ru {IN} {PUBLISH_PATH}{FILENAME}'
    ],
];
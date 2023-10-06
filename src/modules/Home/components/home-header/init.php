<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$background_asset = $app->config['homeHeader.background'] ? $app->config['homeHeader.background'] : $app->config['module.home']['home-header'];

$this->jsObject['config']['homeHeader'] = [
    'background' => $this->asset($background_asset, false),
    'banner' => $app->config['homeHeader.banner'] ? $this->asset($app->config['homeHeader.banner'], false) : '',
    'bannerLink' => $app->config['homeHeader.bannerLink'],
    'downloadableLink' => $app->config['homeHeader.downloadableLink'],
];
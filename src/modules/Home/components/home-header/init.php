<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->jsObject['config']['homeHeader'] = [
    'title' => $app->config['homeHeader.title'],
    'description' => $app->config['homeHeader.description'],
    'background' => $app->config['homeHeader.background'] ? $this->asset($app->config['homeHeader.background'], false) : $this->asset($app->config['module.home']['home-header']),
    'banner' => $app->config['homeHeader.banner'] ? $this->asset($app->config['homeHeader.banner'], false) : '',
    'bannerLink' => $app->config['homeHeader.bannerLink'],
    'downloadableLink' => $app->config['homeHeader.downloadableLink'],
];
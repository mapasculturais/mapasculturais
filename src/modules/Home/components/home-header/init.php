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
    'secondBanner' => $app->config['homeHeader.secondBanner'] ? $this->asset($app->config['homeHeader.secondBanner'], false) : '',
    'secondBannerLink' => $app->config['homeHeader.secondBannerLink'],
    'secondDownloadableLink' => $app->config['homeHeader.secondDownloadableLink'],
    'thirdBanner' => $app->config['homeHeader.thirdBanner'] ? $this->asset($app->config['homeHeader.thirdBanner'], false) : '',
    'thirdBannerLink' => $app->config['homeHeader.thirdBannerLink'],
    'thirdDownloadableLink' => $app->config['homeHeader.thirdDownloadableLink'],
];
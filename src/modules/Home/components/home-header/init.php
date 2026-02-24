<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$background_asset = $app->config['homeHeader.background'] ? $app->config['homeHeader.background'] : $app->config['module.home']['home-header'];

$theme = $this;
$resolveUrl = function ($value) use ($theme) {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (preg_match('#^(https?:)?//#i', $value) || preg_match('#^data:#i', $value) || strpos($value, '/') === 0) {
        return $value;
    }

    return $theme->asset($value, false);
};

$this->jsObject['config']['homeHeader'] = [
    'background' => $resolveUrl($background_asset),

    'banner' => $resolveUrl($app->config['homeHeader.banner'] ?? ''),
    'bannerLink' => (string) ($app->config['homeHeader.bannerLink'] ?? ''),
    'bannerAlt' => (string) ($app->config['homeHeader.bannerAlt'] ?? ''),
    'bannerOpenInNewTab' => (bool) ($app->config['homeHeader.bannerOpenInNewTab'] ?? false),

    'secondBanner' => $resolveUrl($app->config['homeHeader.secondBanner'] ?? ''),
    'secondBannerLink' => (string) ($app->config['homeHeader.secondBannerLink'] ?? ''),
    'secondBannerAlt' => (string) ($app->config['homeHeader.secondBannerAlt'] ?? ''),
    'secondBannerOpenInNewTab' => (bool) ($app->config['homeHeader.secondBannerOpenInNewTab'] ?? false),

    'thirdBanner' => $resolveUrl($app->config['homeHeader.thirdBanner'] ?? ''),
    'thirdBannerLink' => (string) ($app->config['homeHeader.thirdBannerLink'] ?? ''),
    'thirdBannerAlt' => (string) ($app->config['homeHeader.thirdBannerAlt'] ?? ''),
    'thirdBannerOpenInNewTab' => (bool) ($app->config['homeHeader.thirdBannerOpenInNewTab'] ?? false),
];
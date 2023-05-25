<?php
/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->jsObject['config']['logo'] = [
    'title' => $app->config['logo.title'],
    'subtitle' => $app->config['logo.subtitle'],
    'colors' => $app->config['logo.colors'],
    'image' => $app->config['logo.image'] ? $this->asset($app->config['logo.image'], false) : '',
    'hideLabel' => $app->config['logo.hideLabel'],
];
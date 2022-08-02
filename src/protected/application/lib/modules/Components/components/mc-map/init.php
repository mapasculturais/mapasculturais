<?php
$this->jsObject['config']['map'] = [
    'center' => $app->config['maps.center'],
    'tileServer' => $app->config['maps.tileServer'],
    'defaultZoom' => $app->config['maps.zoom.default'],
    'approximateZoom' => $app->config['maps.zoom.approximate'],
    'preciseZoom' => $app->config['maps.zoom.precise'],
    'maxZoom' => $app->config['maps.zoom.max'],
    'minZoom' => $app->config['maps.zoom.min'],
];
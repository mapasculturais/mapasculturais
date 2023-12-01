<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 $file_name = $app->config['statesAndCities.file'];
 $content = $this->resolveFilename('states-and-cities',$file_name);
 include $content;

 $app->view->jsObject['config']['statesAndCities'] = $data;
 $app->view->jsObject['config']['statesAndCitiesEnable'] = $app->config['statesAndCities.enable'];
 $app->view->jsObject['config']['statesAndCitiesCountryCode'] = $app->config['statesAndCities.countryCode'];
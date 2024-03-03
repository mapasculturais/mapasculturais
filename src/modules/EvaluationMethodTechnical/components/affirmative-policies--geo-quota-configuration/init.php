<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 $geo_divisions = $app->getGeoDivisions(true);
 $this->jsObject['config']['geoQuotaConfiguration'] = $geo_divisions;
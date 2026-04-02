<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$app = \MapasCulturais\App::i();
$settings_id = 1;
if ($subsite = $app->subsite) {
    if($active_settings = $app->repo('SiteSettings\\Entities\\Settings')->findOneBy(['subsiteId' => $subsite->id])) {
        $settings_id =   $active_settings->id;
    }
}

$this->jsObject['config']['oneClick'] = [
    'settingsId' => $settings_id,
];

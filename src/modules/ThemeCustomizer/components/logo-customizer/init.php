<?php
/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\App;

$app = App::i();

$this->jsObject['config']['logoCustomizer'] = [
    'originalColors' => ThemeCustomizer\Module::$originalColors,
];
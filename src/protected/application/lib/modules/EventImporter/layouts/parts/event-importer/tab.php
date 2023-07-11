<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
use MapasCulturais\i;

$this->import('
    mc-tab
');
?>
<mc-tab cache key="event-importer" label="<?= i::__('Importações') ?>" slug="event-importer">
    {{global.auth.user.profile.files}}
</mc-tab>
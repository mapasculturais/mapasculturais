<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    event-importer-files
    mc-tab
');
?>
<mc-tab cache key="event-importer" label="<?= i::__('Importações') ?>" slug="event-importer">
  <event-importer-files></event-importer-files>
</mc-tab>
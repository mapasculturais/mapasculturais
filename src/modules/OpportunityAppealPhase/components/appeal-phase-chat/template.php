<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-chat
');

?>

<mc-chat v-if="thread" :thread="thread" anonymous-sender="<?= i::__('Avaliador') ?>"></mc-chat>
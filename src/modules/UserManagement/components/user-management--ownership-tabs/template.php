<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-link
    panel--entity-actions
    panel--entity-tabs
');
?>
<panel--entity-tabs tabs="publish,draft,trash,archived" :type='type' :user="user.id" :select="newSelect">
</panel--entity-tabs>

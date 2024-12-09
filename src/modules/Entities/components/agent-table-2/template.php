<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-icon
    agent-table
');
?>

<div class="agent-table-2">
    <agent-table :agentType=2></agent-table>
</div>
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
    <agent-table :additionalHeaders="additionalHeaders" :agentType="2" :extra-query="extraQuery"></agent-table>
</div>
<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-container
');
?>
<mc-container v-if="canEvaluate">
    <v1-embed-tool route="evaluationlist" :id="entity.id" min-height="600px"></v1-embed-tool>
</mc-container>
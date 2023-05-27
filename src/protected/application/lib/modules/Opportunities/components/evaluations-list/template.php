<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<mapas-container v-if="canEvaluate">
    <v1-embed-tool route="evaluationlist" :id="entity.id"></v1-embed-tool>
</mapas-container>
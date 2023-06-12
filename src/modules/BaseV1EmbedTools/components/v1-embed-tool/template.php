<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-loading
');
?>
<mc-loading :condition="!loaded"></mc-loading>
<iframe v-show="loaded" :id="iframeId" :src="url" ref="iframe" :style="{height: iframeHeight, maxHeight: maxHeight, minHeight: minHeight, maxWidth: maxWidth, minWidth: minWidth, width: '100%', border: 'none'}" ></iframe>

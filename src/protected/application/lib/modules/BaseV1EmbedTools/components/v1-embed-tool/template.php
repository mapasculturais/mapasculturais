<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
$this->import('loading');
?>
<loading :condition="!loaded"></loading>
<iframe v-show="loaded" :id="iframeId" :src="url" ref="iframe" :style="{height: iframeHeight, maxHeight: maxHeight, minHeight: minHeight, maxWidth: maxWidth, minWidth: minWidth, width: '100%', border: 'none'}" ></iframe>

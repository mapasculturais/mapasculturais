<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>

<input 
    ref="inputRef" 
    type="text" 
    @change="dispatchEvent('change', $event)" 
    @input="dispatchEvent('input', $event)" 
    @keydown="dispatchEvent('keydown', $event)" 
    @keyup="dispatchEvent('keyup', $event)" 
    @focus="dispatchEvent('focus', $event)" 
    @blur="dispatchEvent('blur', $event)" 
/>
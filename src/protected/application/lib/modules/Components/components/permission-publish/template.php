<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>
<div class="permission-publish col-12 grid-12">

    <h2 class="permission-publish__title col-12"><?php i::_e("Permissões")?></h2>
    <label class="permission-publish__label col-12"><?php i::_e("Você pode permitir que outras pessoas criem Eventos neste Espaço."); ?></label>

    <div class="publish-fields col-12">
        <div class="field-options">
            <label class="options"> <input v-model="entity" type="radio"  value="false" /> <?= i::_e('Restringir publicação por outras pessoas') ?> </label>
            <label class="options"> <input v-model="entity" type="radio"  value="true" /> <?= i::_e('Permitir publicação por outras pessoas') ?> </label>
        </div>
    </div>
</div>
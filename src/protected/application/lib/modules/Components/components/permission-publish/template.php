<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="permission-publish col-12 grid-12">
    <h2 class="permission-publish__title col-12"><?php i::_e("Permissões") ?></h2>
    <label class="permission-publish__label col-12"><?php i::_e("Você pode permitir que outras pessoas criem Eventos neste Espaço."); ?></label>

    <div class="publish-fields col-12">
        <div class="field-options">
            <label class="options"> <input v-model="entity.public" type="radio" value="false" /> <label class="permission"><?= i::_e('Restringir publicação por outras pessoas') ?></label> </label>
            <label class="options"> <input v-model="entity.public" type="radio" value="true" /> <label class="permission"><?= i::_e('Permitir publicação por outras pessoas') ?></label> </label>
        </div>
    </div>
</div>
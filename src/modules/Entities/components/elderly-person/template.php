<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

?>

<div class="field col-6">
    <label><?php i::_e("Pessoa idosa")?></label>
    <input v-if="entity.idoso" type="text" disabled value=<?php i::_e("Sim")?> />
    <input v-if="!entity.idoso" type="text" disabled value=<?php i::_e("Não")?> />
</div>
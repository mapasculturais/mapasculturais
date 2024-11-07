<?php /**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 use MapasCulturais\i;
?>
<button v-if="showButton()" @click="editForm()" class="button button--primary"><?= i::__('Editar dados') ?></button>
<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
     entity-field
     mc-modal
');
?>

<mc-modal title="<?= i::esc_attr__("Criar Usuario") ?>" classes="create-modal" @close="destroyEntity()" @open="createEntity()">
    <p><?= i::__("Crie um usuario com informações básicas e de forma rápida") ?></p>
    <form @submit.prevent="createUser();" ref="form">
        <div class="field">
            <label for="name"><?= i::__("Nome") ?></label>
            <input name="name" v-model="user.name">
        </div>
        <div class="field">
            <label for="email"><?= i::__("E-mail") ?></label>
            <input name="email" v-model="user.email">
        </div>
        <button style="display:none" type="submit"><?= i::__("Salvar") ?></button>
    </form>
    <template #actions="modal">
        <button class="button button--primary button--icon" @click="createUser();"><?= i::__("Salvar") ?></button>
        <button class="button button--text button--text-del" @click="modal.close()"><?= i::__("Cancelar") ?></button>
    </template>
    <template #button="modal">
        <button class="button button--primary button--icon" @click="modal.open()">
            <mc-icon name="add"></mc-icon> <?= i::__("Criar Usuário") ?>
        </button>
    </template>

</mc-modal>
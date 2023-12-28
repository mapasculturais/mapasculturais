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

<mc-modal title="Criar Usuario" classes="create-modal" @close="destroyEntity()" @open="createEntity()">
  <p>Criaçao de usuario</p>
  {{error}}
  <form @submit="createUser($event);">
    <div class="field">
      <label for="name">Nome</label>
      <input name="name" v-model="user.name">
      <small></small>
    </div>
    <div class="field">
      <label for="email">E-mail</label>
      <input name="email" v-model="user.email">
      <small></small>
    </div>

    <button type="submit">submit</button>


  </form>


  <template #actions="modal">
    <button @click="">fazer algo</button>
    <button @click="modal.close()">cancelar</button>
  </template>

  <template #button="modal">
    <button class="button button--primary button--icon" @click="modal.open()">Criar usuario</button>

  </template>
</mc-modal>
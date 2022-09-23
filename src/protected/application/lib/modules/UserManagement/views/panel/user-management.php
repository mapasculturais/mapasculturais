<?php
use MapasCulturais\i;

$this->import('
    loading,messages,
    panel--card-user,
    entities    
');

$profile = $app->user->profile;
?>

<div class="panel__row">
    <h1>
        <mc-icon name="user-config"></mc-icon>
        <?= i::__('Gerenciamento de usuários') ?>
    </h1>
    <a class="panel__help-link" href="#"><?=i::__('Ajuda')?></a>
</div>
<div class="panel__row">
    <p><?=i::__('Gerencia os usuários do sistema')?></p>
</div>

<entities type="user" name="user:1" :limit="25" select="id,email,status,profile.{id,name,type},roles.{id,name,subsite.{id,name}}">
    <template #header="{entities, query}">
        <form @submit="entities.refresh(); $event.preventDefault()" class="panel__row">
            <input v-model="query['@keyword']" placeholder="<?= i::esc_attr__('Pesquisar') ?>">
            <label>
                <?= i::__('Filtrar por função') ?>
                <entities #default="roles" type="system-role" select="name,slug">
                    <select v-model="query['@roles']" @change="query['@roles'] || delete query['@roles']; entities.refresh()">
                        <option :value="undefined"><?= i::__('Exibir todas') ?></option>
                        <option value="saasSuperAdmin" ><?= i::__('Super Administrador da Rede') ?></option>
                        <option value="saasAdmin" ><?= i::__('Administrador da Rede') ?></option>
                        <option value="superAdmin" ><?= i::__('Super Administrador') ?></option>
                        <option value="admin" ><?= i::__('Administrador') ?></option>
                        <option v-for="role in roles.entities" v-bind:value="role.slug">{{role.name}}</option>
                    </select>
                </entities>
            </label>
        </form>
    </template>
    <template #default="{entities}">
        <panel--card-user v-for="user in entities" :key="user.__objectId" :entity="user"></panel--card-user>    
    </template>
</entities>


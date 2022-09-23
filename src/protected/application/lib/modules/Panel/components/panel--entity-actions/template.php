<?php
use MapasCulturais\i;
use MapasCulturais\Entity;

$this->import('confirm-button');
$this->import('loading');
?>

<loading :entity="entity"></loading>

<confirm-button v-if="!entity.__processing && publishButton && entity.status == <?= Entity::STATUS_TRASH ?>"
    @confirm="publishEntity($event)"
    message="<?php i::esc_attr_e("Você está certo que deseja recuperar esta entidade da lixeira?") ?>"><?php i::_e('Recuperar') ?></confirm-button>

<confirm-button v-if="!entity.__processing && publishButton && entity.status != <?= Entity::STATUS_TRASH ?> && entity.status != <?= Entity::STATUS_ENABLED ?>"
    @confirm="publishEntity($event)"
    message="<?php i::esc_attr_e("Você está certo que deseja publicar esta entidade?") ?>"><?php i::_e('Publicar') ?></confirm-button>

<confirm-button v-if="!entity.__processing && archiveButton && entity.status != <?= Entity::STATUS_ARCHIVED ?> && hasStatus('archived')"
    @confirm="archiveEntity($event)"
    button-class="button--text archive button--icon"
    message="<?php i::esc_attr_e("Você está certo que deseja arquivar esta entidade?") ?>">
        <mc-icon name="archive"></mc-icon>
        <span><?php i::_e('Arquivar') ?></span>
    </confirm-button>

<confirm-button v-if="!entity.__processing && deleteButton && entity.status != <?= Entity::STATUS_TRASH ?> && hasStatus('trash')"
    @confirm="deleteEntity($event)"
    button-class="button--text delete button--icon"
    message="<?php i::esc_attr_e("Você está certo que deseja excluir esta entidade?") ?>">
        <mc-icon name="trash"></mc-icon>
        <span><?php i::_e('Excluir') ?></span>
    </confirm-button>

<confirm-button v-if="!entity.__processing && destroyButton && entity.status == <?= Entity::STATUS_TRASH ?>"
    @confirm="destroyEntity($event)"
    message="<?php i::esc_attr_e("Você está certo que deseja excluir definitivamente esta entidade?") ?>"><?php i::_e('Excluir permanentemente') ?></confirm-button>

<a :href="entity.singleUrl" class="button button--outline"><?php i::_e('Acessar') ?></a> 
<a :href="entity.editUrl" class="button button--primary"><?php i::_e('Editar') ?></a>
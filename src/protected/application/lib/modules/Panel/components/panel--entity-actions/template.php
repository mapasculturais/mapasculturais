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

<confirm-button v-if="!entity.__processing && archiveButton && entity.status != <?= Entity::STATUS_ARCHIVED ?>"
    @confirm="archiveEntity($event)"
    button-class="button--text archive"
    message="<?php i::esc_attr_e("Você está certo que deseja arquivar esta entidade?") ?>">
        <iconify icon="mdi:archive-outline"></iconify>
        <span><?php i::_e('Arquivar') ?></span>
    </confirm-button>

<confirm-button v-if="!entity.__processing && deleteButton && entity.status != <?= Entity::STATUS_TRASH ?>"
    @confirm="deleteEntity($event)"
    button-class="button--text delete"
    message="<?php i::esc_attr_e("Você está certo que deseja excluir esta entidade?") ?>">
        <iconify icon="mdi:delete-outline"></iconify>
        <span><?php i::_e('Excluir') ?></span>
    </confirm-button>

<confirm-button v-if="!entity.__processing && destroyButton && entity.status == <?= Entity::STATUS_TRASH ?>"
    @confirm="destroyEntity($event)"
    message="<?php i::esc_attr_e("Você está certo que deseja excluir definitivamente esta entidade?") ?>"><?php i::_e('Excluir permanentemente') ?></confirm-button>

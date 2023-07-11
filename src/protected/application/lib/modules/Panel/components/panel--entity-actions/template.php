<?php
/**
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
use MapasCulturais\Entity;

$this->import('
    mc-confirm-button
    mc-loading
');
?>
<div v-if="entity.__processing" class="panel__entity-actions">
    <mc-loading :entity="entity"></mc-loading>
</div>
<div v-if="!entity.__processing" class="panel__entity-actions">
    <mc-confirm-button v-if="undeleteButton && entity.status == <?= Entity::STATUS_TRASH ?>"
        @confirm="undeleteEntity($event)" 
        button-class="button unpublish button--primary button--icon button-action recover"
        message="<?php i::esc_attr_e("Você está certo que deseja recuperar esta entidade da lixeira?") ?>">
            <?php i::_e('Recuperar') ?>
    </mc-confirm-button>
    
    <mc-confirm-button v-if="publishButton && entity.status != <?= Entity::STATUS_TRASH ?> && entity.status != <?= Entity::STATUS_ENABLED ?> && entity.currentUserPermissions.publish"
        @confirm="publishEntity($event)"
        button-class="button publish button--primary button--icon button-action publish-archived"
        message="<?php i::esc_attr_e("Você está certo que deseja publicar esta entidade?") ?>">
            <?php i::_e('Publicar') ?>
    </mc-confirm-button>
    
    <mc-confirm-button v-if="archiveButton && entity.status != <?= Entity::STATUS_ARCHIVED ?> && hasStatus('archived') && entity.currentUserPermissions.archive"
        @confirm="archiveEntity($event)"
        button-class="button--text archive button--icon button--sm panel__entity-actions--archive"
        message="<?php i::esc_attr_e("Você está certo que deseja arquivar esta entidade?") ?>">
            <mc-icon name="archive"></mc-icon>
            <span><?php i::_e('Arquivar') ?></span>
    </mc-confirm-button>
    
    <mc-confirm-button v-if="deleteButton && entity.status != <?= Entity::STATUS_TRASH ?> && hasStatus('trash') && entity.currentUserPermissions.remove"
        @confirm="deleteEntity($event)"
        button-class="button--text delete button--icon button--sm panel__entity-actions--trash"
        message="<?php i::esc_attr_e("Você está certo que deseja excluir esta entidade?") ?>">
            <mc-icon name="trash"></mc-icon>
            <span><?php i::_e('Excluir') ?></span>
    </mc-confirm-button>
    
    <mc-confirm-button v-if="destroyButton && entity.status == <?= Entity::STATUS_TRASH ?> && entity.currentUserPermissions.destroy"
        @confirm="destroyEntity($event)"
        button-class="button--text delete button--icon button--sm panel__entity-actions--trash"
        message="<?php i::esc_attr_e("Você está certo que deseja excluir definitivamente esta entidade?") ?>">
        <mc-icon name="trash"></mc-icon>
        <?php i::_e('Excluir permanentemente') ?>
    </mc-confirm-button>

    <mc-confirm-button v-if="draftButton && entity.status != <?= Entity::STATUS_ARCHIVED ?> && hasStatus('draft')"
        @confirm="draftEntity($event)"
        button-class="button--text archive button--icon button--sm"
        message="<?php i::esc_attr_e("Você está certo que deseja transformar esta entidade em rascunho?") ?>">
            <span><?php i::_e('Tornar rascunho') ?></span>
    </mc-confirm-button>
</div>
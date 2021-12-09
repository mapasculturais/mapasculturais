<?php

use MapasCulturais\i;

$entity_owner_type = $opportunity->ownerEntity->controller->id;
?>

<header class="main-content-header">

    <h5 class="entity-parent-title">
        <div class="icon icon-<?php echo $entity_owner_type ?>"></div>
        <?php echo $opportunity->ownerEntity->name; ?>
    </h5>

    <div class="header-content">

        <?php if( $avatar = $opportunity->avatar ): ?>
            <div class="avatar com-imagem">
                <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
            </div>
        <?php else: ?>
            <div class="avatar">
                <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
            </div>
        <?php endif; ?>

        <div class="entity-type registration-type">
            <div class="icon icon-project"></div>
            <b><?php echo $opportunity->type->name; ?></b>
        </div>

        <?php $this->part( 'entity-parent', ['entity' => $opportunity, 'child_entity_request' => null] ) ?>

        <h2><?php echo $opportunity->name; ?></h2>

    </div>

</header>
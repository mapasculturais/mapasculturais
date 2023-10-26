<?php $this->part('singles/opportunity-header--owner-entity', ['entity' => $opportunity]) ?>
<header class="main-content-header">
    <div
        <?php if($header = $opportunity->getFile('header')): ?>
            class="header-image"
            style="background-image: url(<?php echo $header->transform('header')->url; ?>);"
        <?php endif; ?>
    >
    </div>
    <!--.header-image-->
    <div class="header-content">
    <?php if($avatar = $opportunity->avatar): ?>
        <div class="avatar com-imagem">
            <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
    <?php else: ?>
        <div class="avatar">
            <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
    <?php endif; ?>
        </div>
        <!--.avatar-->
        <div class="entity-type registration-type">
            <div class="icon icon-project"></div>
            <a rel='noopener noreferrer'><?php echo $opportunity->type->name; ?></a>
        </div>
        <!--.entity-type-->
        <?php $this->part('entity-parent', ['entity' => $opportunity, 'child_entity_request' => null]) ?>

        <h2><a href="<?php echo $opportunity->singleUrl ?>"><?php echo $opportunity->name; ?></a></h2>
    </div>
</header>
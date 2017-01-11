<header class="main-content-header">
    <div
        <?php if($header = $project->getFile('header')): ?>
            class="header-image"
            style="background-image: url(<?php echo $header->transform('header')->url; ?>);"
        <?php endif; ?>
    >
    </div>
    <!--.header-image-->
    <div class="header-content">
    <?php if($avatar = $project->avatar): ?>
        <div class="avatar com-imagem">
            <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
    <?php else: ?>
        <div class="avatar">
            <img class="js-avatar-img" src="<?php $this->asset('img/avatar--project.png'); ?>" />
    <?php endif; ?>
        <!-- pro responsivo!!! -->
        <?php if($project->isVerified): ?>
            <a class="verified-seal hltip active" title="<?php \MapasCulturais\i::esc_attr_e("Este projeto é verificado.");?>" href="#"></a>
        <?php endif; ?>
        </div>
        <!--.avatar-->
        <div class="entity-type registration-type">
            <div class="icon icon-project"></div>
            <a><?php echo $project->type->name; ?></a>
        </div>
        <!--.entity-type-->
        <h2><a href="<?php echo $project->singleUrl ?>"><?php echo $project->name; ?></a></h2>
    </div>
</header>
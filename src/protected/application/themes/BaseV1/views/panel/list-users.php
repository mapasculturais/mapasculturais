<?php
$this->layout = 'panel';
$first = true;
$noSubSite = ($app->getCurrentSubsiteId() == 0 || $app->getCurrentSubsiteId() == null);
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php $this->dict('entities: Users and roles') ?></h2>
	</header>
    <ul class="abas clearfix clear">
        <?php foreach ($roles as $roleSlug => $role) : ?>
            <li <?php if ($first) { $first = false; echo 'class="active"'; } ?>>
                <a href="#<?php echo $roleSlug; ?>"><?php echo $this->dict($role['pluralLabel']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php foreach ($roles as $roleSlug => $role) : ?>
        <div id="<?php echo $roleSlug; ?>">
            <?php foreach(${'list_' . $roleSlug} as $u): ?>

                <article class="objeto clearfix">

                    <h1>
                        <a href="<?php echo $u->user->profile->singleUrl; ?>">
                            <?php echo $u->user->profile->name; ?>
                        </a>
                    </h1>



                    <div class="entity-actions">
                        <?php if($noSubSite && is_object($u->subsite)):?>
                            <p>
                                <a href="<?php echo 'http://' . $u->subsite->url ?>"><?php echo 'http://' . $u->subsite->url ?></a>
                            </p>
                        <?php endif;?>
                        <?php if ($u->user->profile->canUser('RemoveRole' . $role['permissionSuffix'])): ?>
                            <a class="btn btn-small btn-danger js-confirm-before-go" data-confirm-text="<?php printf(\MapasCulturais\i::esc_attr__("Você tem certeza que deseja remover este usuário da lista de %s?"), $role['pluralLabel']);?>" href="<?php echo $app->createUrl('agent', 'removeRole', ['id' => $u->user->profile->id, 'role' => $roleSlug]); ?>">
                            <?php \MapasCulturais\i::_e("remover do papel");?>
                            </a>
                        <?php endif; ?>
                    </div>

                </article>

            <?php endforeach; ?>

            <?php if(!${'list_' . $roleSlug}): ?>
                <div class="alert info"><?php printf(\MapasCulturais\i::__("Não há %s"), $role['pluralLabel']);?></div>
            <?php endif; ?>


        </div>

    <?php endforeach; ?>



</div>

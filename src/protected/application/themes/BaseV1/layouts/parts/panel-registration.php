<?php
$project = $entity->project;
?>
<article class="objeto clearfix">
    <?php if($avatar = $project->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->number ?> - <?php echo $project->name; ?></a></h1>
	<div class="objeto-meta">
		<div><span class="label">Agente responsável:</span> <?php echo $entity->registrationOwner->name; ?></div>
        <div><span class="label">Coletivo:</span> Nome do Coletivo sem CNPJ</div>
        <div><span class="label">Instituição:</span> Nome do Coletivo com CNPJ</div>
        <div><span class="label"><?php echo $project->registrationCategTitle ?>:</span> <?php echo $entity->category ?></div>
	</div>
    <div>
        <a class="action" href="<?php echo $entity->editUrl; ?>">editar</a>
    </div>
</article>
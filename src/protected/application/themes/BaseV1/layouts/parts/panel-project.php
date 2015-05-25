<article class="objeto clearfix">
    <?php if($avatar = $entity->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
		<div><span class="label">Tipo:</span> <?php echo $entity->type->name?></div>
                <?php if($entity->registrationFrom || $entity->registrationTo): ?>
                    <div>
                        <span class="label">Inscrições:</span>
                        <?php
                            if($entity->isRegistrationOpen()) echo'open ';
                            if($entity->registrationFrom && !$entity->registrationTo)
                                echo 'a partir de '.$entity->registrationFrom->format('d/m/Y');
                            elseif(!$entity->registrationFrom && $entity->registrationTo)
                                echo ' até '. $entity->registrationTo->format('d/m/Y');
                            else
                                echo 'de '. $entity->registrationFrom->format('d/m/Y') .' a '. $entity->registrationTo->format('d/m/Y');
                        ?>
                    </div>
                <?php endif; ?>
		<div><span class="label">Organização:</span> <?php echo $entity->owner->name; ?></div>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Project::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Project::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>">publicar</a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>">excluir</a>

        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>">excluir definitivamente</a>
                <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
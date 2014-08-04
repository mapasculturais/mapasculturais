<article class="objeto clearfix">
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
    <div class="objeto-meta">
		<div><span class="label">Linguagens:</span> <?php echo implode(',', $entity->terms['linguagem'])?></div>
		<!--div><span class="label">Horário:</span> <time>00h00</time></div-->
		<!--div><span class="label">Local:</span> Teatro</div-->
		<?php if($entity->classificacaoEtaria): ?>
                    <div><span class="label">Classificação:</span> <?php echo $entity->classificacaoEtaria; ?></div>
                <?php endif; ?>
	</div>
    <div>
        <a class="action" href="<?php echo $entity->editUrl; ?>">editar</a>

        <?php if($entity->status === \MapasCulturais\Entities\Event::STATUS_ENABLED): ?>
            <a class="action" href="<?php echo $entity->deleteUrl; ?>">excluir</a>
        <?php else: ?>
            <a class="action" href="<?php echo $entity->undeleteUrl; ?>">recuperar</a>
        <?php endif; ?>
    </div>
</article>
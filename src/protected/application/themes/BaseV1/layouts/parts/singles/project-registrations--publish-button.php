<?php if ($entity->canUser('@control')): ?>
    <?php if ($entity->publishedRegistrations): ?>
        <div class="clearfix">
            <p class='alert success'>O resultado já foi publicado</p>
        </div>
    <?php else: ?>
        <div class="clearfix">
            <?php if ($entity->canUser('publishRegistrations')): ?>
                <a id="btn-publish-results" class="btn btn-primary" href="<?php echo $app->createUrl('project', 'publishRegistrations', [$entity->id]) ?>">Publicar resultados</a>
            <?php else: ?>
                <a id="btn-publish-results" class="btn btn-primary disabled hltip" title="Você só pode publicar a lista de aprovados após o término do período de inscrições.">Publicar resultados</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
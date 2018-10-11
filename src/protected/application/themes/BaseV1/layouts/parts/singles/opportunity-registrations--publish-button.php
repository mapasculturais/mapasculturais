<?php if ($entity->canUser('@control')): ?>
    <?php if ($entity->publishedRegistrations): ?>
        <div class="clearfix">
            <p class='alert success'><?php \MapasCulturais\i::_e("O resultado oficial já foi publicado");?></p>
        </div>
    <?php elseif ($entity->publishedPreliminaryRegistrations): ?>
        <div class="clearfix">
            <p class='alert success'><?php \MapasCulturais\i::_e("O resultado preliminar já foi publicado");?></p>
        </div>
    <?php else: ?>
        <div class="clearfix">
            <?php if ($entity->canUser('publishRegistrations')): ?>
                <a id="btn-publish-results" class="btn btn-primary" href="<?php echo $app->createUrl('opportunity', 'publishRegistrations', [$entity->id]) ?>"><?php \MapasCulturais\i::_e("Publicar resultados");?></a>

                <?php if ( !$entity->publishedPreliminaryRegistrations): ?>
                    <a id="btn-publish-preliminary-results" class="btn btn-primary" href="<?php echo $app->createUrl('opportunity', 'publishPreliminaryRegistrations', [$entity->id]) ?>"><?php \MapasCulturais\i::_e("Publicar resultados preliminares");?></a>
                <?php else: ?>
                    <a id="btn-publish-preliminary-results" class="btn btn-primary disabled hltip" title="<?php \MapasCulturais\i::esc_attr_e("O resultado preliminar já foi publicado.");?>"><?php \MapasCulturais\i::_e("Publicar resultados preliminares");?></a>
                <?php endif; ?>

            <?php else: ?>
                <a id="btn-publish-results" class="btn btn-primary disabled hltip" title="<?php \MapasCulturais\i::esc_attr_e("Você só pode publicar a lista de aprovados após o término do período de inscrições.");?>"><?php \MapasCulturais\i::_e("Publicar resultados final");?></a>
                <a id="btn-publish-preliminary-results" class="btn btn-primary disabled hltip" title="<?php \MapasCulturais\i::esc_attr_e("Você só pode publicar a lista de aprovados após o término do período de inscrições.");?>"><?php \MapasCulturais\i::_e("Publicar resultados preliminares");?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
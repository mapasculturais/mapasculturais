<?php if ($entity->canUser('@control')): ?>

    <?php if ($entity->publishedRegistrations): ?>
        <div class="clearfix">
            <div class='alert success'><?php \MapasCulturais\i::_e("O resultado oficial já foi publicado");?>
                <div class="close" style="cursor: pointer;"></div>
            </div>
        </div>
    <?php elseif ($entity->publishedPreliminaryRegistrations): ?>
        <div class="clearfix">
            <div class='alert success'><?php \MapasCulturais\i::_e("O resultado preliminar já foi publicado");?>
                <div class="close" style="cursor: pointer;"></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$entity->publishedRegistrations): ?>
        <div class="clearfix sombra" style="background-color: #e2e2e2; height: 30px; padding: 10px;">
            <?php
            $_evaluation_type = $entity->evaluationMethodConfiguration->getType();
            if( is_object($_evaluation_type) && property_exists($_evaluation_type, "id") && $_evaluation_type->id === "simple" ): ?>
                <button  ng-if="hasEvaluations()" class="btn btn-primary hltip" ng-click="applyEvaluations()" title="<?php \MapasCulturais\i::esc_attr_e("Aplicar os resultados das avaliações nas inscrições.");?>"> {{ data.confirmEvaluationLabel }} </button>
            <?php endif; ?>
            <?php if ($entity->canUser('publishRegistrations')): ?>

                <?php if ( !$entity->publishedPreliminaryRegistrations): ?>
                    <a id="btn-publish-preliminary-results" class="btn btn-primary " href="<?php echo $app->createUrl('opportunity', 'publishPreliminaryRegistrations', [$entity->id]) ?>"><?php \MapasCulturais\i::_e("Resultado preliminar");?></a>
                <?php else: ?>
                    <a id="btn-publish-results" class="btn btn-primary" href="<?php echo $app->createUrl('opportunity', 'publishRegistrations', [$entity->id]) ?>"><?php \MapasCulturais\i::_e("Publicar resultado");?></a>
                <?php endif; ?>

                <a class="btn btn-default download" href="<?php echo $app->createUrl('opportunity','report', [$entity->id]); ?>"><?php \MapasCulturais\i::esc_attr_e("Baixar inscritos");?></a>
                <a class="btn btn-default download" href="<?php echo $app->createUrl('opportunity','reportDrafts', [$entity->id]); ?>"><?php \MapasCulturais\i::esc_attr_e("Baixar rascunhos");?></a>

            <?php else: ?>
                <a id="btn-publish-results" class="btn btn-primary disabled hltip" title="<?php \MapasCulturais\i::esc_attr_e("Você só pode publicar a lista de aprovados após o término do período de inscrições.");?>"><?php \MapasCulturais\i::_e("Publicar resultados final");?></a>
                <a id="btn-publish-preliminary-results" class="btn btn-primary disabled hltip" title="<?php \MapasCulturais\i::esc_attr_e("Você só pode publicar a lista de aprovados após o término do período de inscrições.");?>"><?php \MapasCulturais\i::_e("Publicar resultados preliminares");?></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
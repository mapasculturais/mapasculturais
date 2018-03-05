<p>
    <?php if($entity->canUser('sendUserEvaluations')): ?>
        <a href="<?php echo $entity->sendEvaluationsUrl ?>" class="btn btn-primary"><?php \MapasCulturais\i::_e('Enviar Avaliações'); ?></a>
    <?php elseif($entity->canUser('evaluateRegistrations')): ?>
        <a class="btn btn-default hltip" title="<?php \MapasCulturais\i::esc_attr_e('É necessário avaliar todas as inscrições antes de enviar') ?>"><?php \MapasCulturais\i::_e('Enviar Avaliações'); ?></a>
    <?php endif; ?>
</p>
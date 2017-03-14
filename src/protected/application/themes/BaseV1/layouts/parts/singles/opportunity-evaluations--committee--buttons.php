<?php if($entity->canUser('sendUserEvaluations')): ?>
<a href="<?php echo $entity->sendEvaluationsUrl ?>" class="btn btn-primary"><?php \MapasCulturais\i::_e('Enviar inscrições'); ?></a>
<?php endif; ?>
<?php
use MapasCulturais\i;

$full_date_to = \OpportunityAccountability\Module::fullTextDate($entity->opportunity->registrationTo->getTimestamp());
?>
<div class="registration-fieldset" ng-controller="RegistrationFieldsController">
    <?php if ($entity->opportunity->isRegistrationOpen() && (\MapasCulturais\App::i())->user->profile->id == $entity->owner->id): ?>
        <p class="registration-help"><?php i::_e("Certifique-se que você preencheu as informações corretamente antes de enviar sua prestação de contas.");?> <strong><?php i::_e("Depois de enviada, não será mais possível editá-la.");?></strong></p>
        <p class="registration-help"><?php i::_e("A prestaçao de contas pode ser enviada até") ?> <?= $full_date_to ?></p>
        <a class="btn btn-primary" ng-click="sendRegistration(false, true)" rel='noopener noreferrer'><?php i::_e("Enviar prestação de contas");?></a>
    <?php else: ?>
        <p class="registration-help">
            <strong>
                <?php if ((new \DateTime()) > $entity->opportunity->registrationTo) {
                    echo sprintf(i::__("As prestações de contas encerraram-se em %s."), $full_date_to);
                } else {
                    $full_date_from = \OpportunityAccountability\Module::fullTextDate($entity->opportunity->registrationFrom->getTimestamp());
                    echo sprintf(i::__("As prestações de contas terão início em %s."), $full_date_from);
                } ?>
            </strong>
        </p>
    <?php endif; ?>

    <?php if (!$entity->opportunity->isRegistrationOpen() && $entity->canUser('send')): ?>
        <?php if($entity->sentTimestamp): ?>
            <a ng-click="sendRegistration(false, true)" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" data-status="<?= MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php i::_e("reenviar");?></a>
        <?php else: ?>
            <a ng-click="sendRegistration(false, true)" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="<?php i::esc_attr_e('Somente super admins podem usar este botão e somente deve ser usado para enviar prestações de contas que não foram enviadas por problema do sistema.'); ?>" data-status="<?= MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php i::_e("enviar esta inscrição");?></a>
        <?php endif; ?>
    <?php endif ?>
</div>

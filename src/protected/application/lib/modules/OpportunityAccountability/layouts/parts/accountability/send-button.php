<?php 
use MapasCulturais\i;

/* translators: gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642 */
$date_to = strftime( i::__("%d de {%B} de %G às %H:%M") , $entity->opportunity->registrationTo->getTimestamp());
$full_date_to = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
    return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
}, $date);
?>
<div class="registration-fieldset">
    <?php if($entity->opportunity->isRegistrationOpen()): ?>
        <p class="registration-help"><?php i::_e("Certifique-se que você preencheu as informações corretamente antes de enviar sua prestação de contas.");?> <strong><?php i::_e("Depois de enviada, não será mais possível editá-la.");?></strong></p>
        <p class="registration-help"><?php i::_e("A prestaçao de contas pode ser enviada até") ?> <?= $full_date_to ?></p>
        <a class="btn btn-primary" ng-click="sendRegistration()" rel='noopener noreferrer'><?php i::_e("Enviar prestação de contas");?></a>
    <?php else: ?>
        <p class="registration-help">
            <strong>
                <?php i::_e("As prestações de contas encerraram-se em");?> <?= $full_date_to; ?>.
            </strong>
        </p>
    <?php endif; ?>

    <?php if(!$entity->opportunity->isRegistrationOpen() && $entity->canUser('send')): ?>
        <?php if($entity->sentTimestamp): ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" data-status="<?= MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php i::_e("reenviar");?></a>
        <?php else: ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="<?php i::esc_attr_e('Somente super admins podem usar este botão e somente deve ser usado para enviar prestações de contas que não foram enviadas por problema do sistema.'); ?>" data-status="<?= MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php i::_e("enviar esta inscrição");?></a>
        <?php endif; ?>
    <?php endif ?>
</div>

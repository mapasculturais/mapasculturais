<div class="registration-fieldset">
    <?php if($entity->project->isRegistrationOpen()): ?>
        <p class="registration-help">Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição. <strong>Depois de enviada, não será mais possível editá-la.</strong></p>
        <a class="btn btn-primary" ng-click="sendRegistration()">Enviar inscrição</a>
    <?php else: ?>
        <p class="registration-help">
            <strong>
                <?php // gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642
                $date = strftime("%d de {%B} de %G às %H:%M", $entity->project->registrationTo->getTimestamp());
                $full_date = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
                    return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
                }, $date);
                ?>
                As inscrições encerraram-se em <?php echo $full_date; ?>.
            </strong>
        </p>
    <?php endif; ?>

    <?php if(!$entity->project->isRegistrationOpen() && $app->user->is('superAdmin')): ?>
        <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="Somente super admins podem usar este botão e somente deve ser usado para enviar inscrições que não foram enviadas por problema do sistema." data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>">enviar esta inscrição</a>
    <?php endif ?>
</div>
<?php
$app = \MapasCulturais\App::i();
?>

<div>
    <div class="validation-fields-errors">
        <div class="errors-header" ng-if="numFieldErrors() > 0">
            <p class="errors-header-title title"><?= \MapasCulturais\i::_e('O cadastro não foi enviado!') ?></p>
            <p class="errors-header-title text"><?= \MapasCulturais\i::_e('Corrija os campos listados abaixo e valide seu formulário utilizando o botão Salvar e validar.') ?></p>
            <div class="errors " ng-repeat="field in data.fields" ng-if="entityErrors[field.fieldName]">
                <a ng-click="scrollTo('wrapper-' + field.fieldName, 130)">
                    <span class="errors-field" ng-repeat="error in entityErrors[field.fieldName]"> <strong>{{field.title.replace(':', '')}}:</strong> {{error}} </span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="registration-fieldset">    
    <?php if($entity->opportunity->isRegistrationOpen()): ?>
        <p class="registration-help"><?php \MapasCulturais\i::_e("Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição.");?> <strong><?php \MapasCulturais\i::_e("Depois de enviada, não será mais possível editá-la.");?></strong></p>
        <a class="btn btn-primary" ng-click="sendRegistration()" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Enviar inscrição");?></a>
    <?php else: ?>
        <p class="registration-help">
            <strong>
                <?php /* translators: gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642 */
                $date = strftime( \MapasCulturais\i::__("%d de {%B} de %G às %H:%M") , $entity->opportunity->registrationTo->getTimestamp());
                $full_date = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
                    return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
                }, $date);
                ?>
                <?php \MapasCulturais\i::_e("As inscrições encerraram-se em");?> <?php echo $full_date; ?>.
            </strong>
        </p>
    <?php endif; ?>

    <?php if(!$entity->opportunity->isRegistrationOpen() && $entity->canUser('send')): ?>
        <?php if($entity->sentTimestamp): ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php \MapasCulturais\i::_e("reenviar");?></a>
        <?php else: ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="<?php \MapasCulturais\i::esc_attr_e('Somente super admins podem usar este botão e somente deve ser usado para enviar inscrições que não foram enviadas por problema do sistema.'); ?>" data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>"><?php \MapasCulturais\i::_e("enviar esta inscrição");?></a>
        <?php endif; ?>
    <?php endif ?>
</div>

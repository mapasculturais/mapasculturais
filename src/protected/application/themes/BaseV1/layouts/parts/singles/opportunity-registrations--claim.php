<?php
$app = \MapasCulturais\App::i();
$user = $app->user;
if($this->controller->action === 'create')
    return false;
?>

<div class="opportunity-claim-box">
    <input class="button-form-opportunity-claim btn-primary" ng-click="form[<?php echo $registration->id?>] = !form[<?php echo $registration->id?>]" type="button" name="opportunity-claim" value="<?php \MapasCulturais\i::esc_attr_e('Solicitar Recurso'); ?>">
</div>

<form class="form-opportunity-claim js-opportunity-claim-form" ng-show="form[<?php echo $registration->id?>]" ng-controller="OpportunityClaimController">
    <p>
        <?php \MapasCulturais\i::_e("Mensagem");?>:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="30" name="message"></textarea>
    </p>
    <p>
        <button class="js-submit-button opportunity-claim-form" ng-click="send()" id="<?php echo $registration->id?>"><?php \MapasCulturais\i::_e("Enviar");?></button>
    </p>
    <div class="widget opportunity-claim-box" hidden="hidden">
        <p class="alert sucess"><?php \MapasCulturais\i::_e("Enviado com Sucesso");?>.<span class="close opportunity-claim-form"></span></p>
    </div>
</form>
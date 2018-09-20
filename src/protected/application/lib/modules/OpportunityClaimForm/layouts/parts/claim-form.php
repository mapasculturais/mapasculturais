<?php
use MapasCulturais\i;
$app = \MapasCulturais\App::i();
$user = $app->user;
?>

<div class="opportunity-claim-box">
    <input class="button-form-opportunity-claim btn-primary" ng-click="form[<?php echo $registration->id?>] = !form[<?php echo $registration->id?>]" type="button" name="opportunity-claim" value="<?php i::esc_attr_e('Solicitar Recurso'); ?>">
</div>

<form class="form-opportunity-claim js-opportunity-claim-form" ng-show="form[<?php echo $registration->id?>]" ng-controller="OpportunityClaimController">
    <p>
        <?php i::_e("Mensagem");?>:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="30" name="message"></textarea>
    </p>
    <p>

        <button class="js-submit-button opportunity-claim-form"
                ng-click="
                send(<?php echo $registration->id?>);
                form[<?php echo $registration->id?>] = false;
            "
        >
            <?php i::_e("Enviar");?>
        </button>
    </p>

</form>

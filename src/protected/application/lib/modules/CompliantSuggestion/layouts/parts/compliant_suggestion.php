<?php
use MapasCulturais\i;
$app = MapasCulturais\App::i();
$user = $app->user;
$compliantUrl = '';
if(isset($app->config['module.CompliantSuggestion'])) {
    $compliantUrl = !isset($app->config['module.CompliantSuggestion']['compliantUrl']) ? $app->config['module.CompliantSuggestion']['compliantUrl'] : '';
}

if($this->controller->action === 'create')
    return false;
?>

<div class="compliant-suggestion-box">
<?php if(isset($compliant)): ?>
    <?php if($compliantUrl) { ?>
        <a class="btn btn-warning" target="_blank" href="<?php echo $compliantUrl?>"> <?php i::_e('Denunciar'); ?> </a>
    <?php } else { ?>
        <button ng-show="!data.showForm" ng-click="data.showForm = 'compliant'" class="button-form-compliant-suggestion compliant btn-warning"><?php i::_e('Denunciar'); ?></button>
    <?php } ?>
<?php endif;?>

<?php if(isset($suggestion)): ?>
    <button ng-show="!data.showForm" ng-click="data.showForm = 'suggestion'" class="button-form-compliant-suggestion suggestion btn-success"><?php i::_e('Contato'); ?></button>
<?php endif;?>
</div>

<?php if(isset($compliant)): ?>
    <form ng-show="data.showForm === 'compliant'" class="form-complaint-suggestion js-compliant-form" ng-controller="CompliantController">
        <?php if($user->is('guest')):?>
        <p>
            <?php i::_e("Nome");?>:<br />
            <input ng-model="data.name" type="text" rows="5" name="nome" class="input-name">
        </p>
        <p>
            <?php i::_e("E-mail");?>:<br />
            <input ng-model="data.email" type="email" rows="5" name="email" class="input-email">
        </p>
        <?php endif;?>
        <p>
            <?php i::_e("Tipo");?>:</br>
            <select ng-model="data.type" ng-options="item for item in compliant_type" class="compliant-type"></select>
        </p>
        <p>
            <?php i::_e("Mensagem");?>:<br />
            <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
        </p>
        <p>
            <input type='checkbox' ng-model="data.anonimous" name="anonimous">
            <label for="anonimous"><?php i::_e("Denúncia anônima");?></label>
        </p>
        <p>
            <input type='checkbox' ng-model="data.copy" name="copy">
            <label for="copy"><?php i::_e("Receber cópia da denúncia");?></label>
        </p>
        
        <?php if (isset($googleRecaptchaSiteKey)): ?>
            <p>
                <div class="g-recaptcha" data-sitekey="<?php echo $googleRecaptchaSiteKey; ?>" data-callback="captcha"></div>
            </p>
        <?php endif; ?>

        <p ng-show="!data.compliantStatus">
            <button ng-click="data.showForm = false" class="button-form-compliant-suggestion suggestion btn-default" ><?php i::_e('Cancelar'); ?></button>
            <button class="js-submit-button compliant-form btn-warning" ng-click="send()"><?php i::_e("Enviar Denúncia");?></button>
        </p>

        <p ng-show="data.compliantStatus == 'sending'">
            <img src="<?php $this->asset('img/spinner.gif') ?>" /> <?php i::_e('Enviando mensagem'); ?>
        </p>
    </form>
<?php endif;?>

<?php if(isset($suggestion)): ?>
    <form ng-show="data.showForm === 'suggestion';" class="form-complaint-suggestion js-suggestion-form" ng-controller="SuggestionController" <?php if(!$app->user->is('guest')): ?> ng-init="data.name='<?php echo htmlentities($app->user->profile->name) ?>'; data.email='<?php echo htmlentities($app->user->email) ?>';" <?php endif; ?>>
        <?php if($user->is('guest')):?>
            <p>
                <label>
                    <?php i::_e("Nome");?>:<br />
                    <input ng-model="data.name" type="text" rows="5" name="name" class="input-name">
                </label>
            </p>
            <p>
                <label>
                    <?php i::_e("E-mail");?>:<br />
                    <input ng-model="data.email" type="email" rows="5" name="email" class="input-email">
                </label>
            </p>
        <?php else: ?>
            <input ng-model="data.name" type="hidden" name="name" >
            <input ng-model="data.email" type="hidden" name="email">
        <?php endif;?>
        <p>
            <label>
                <?php i::_e("Tipo");?>:</br>
                <select ng-model="data.type" ng-options="item for item in suggestion_type" class="suggestion-type"></select>
            </label>
        </p>
        <p>
            <label>
                <?php i::_e("Mensagem");?>:<br />
                <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
            </label>
        </p>
        <p>
            <label>
                <input id="anonimous" type='checkbox' ng-model="data.anonimous" name="anonimous">
                <?php i::_e("Mensagem anônima");?>
            </label>
        </p>
        <p>
            <label>
                <input id="only_owner" type='checkbox' ng-model="data.only_owner" name="only_owner">
                <?php i::_e("Enviar somente para o Responsável");?>
            </label>
        </p>
        <p>
            <label>
                <input id="copy" type='checkbox' ng-model="data.copy" name="copy">
                <?php i::_e("Receber cópia da mensagem");?>
            </label>
        </p>

        <?php if (isset($googleRecaptchaSiteKey)): ?>
        <p>
            <div class="g-recaptcha" data-sitekey="<?php echo $googleRecaptchaSiteKey; ?>" data-callback="captchasuggestion"></div>
        </p>
        <?php endif; ?>

        <p ng-show="!data.suggestionStatus">
            <button ng-click="data.showForm = false" class="button-form-compliant-suggestion suggestion btn-default" ><?php i::_e('Cancelar'); ?></button>
            <button class="js-submit-button suggestion-form btn-success" ng-click="send()"><?php i::_e("Enviar mensagem");?></button>
        </p>

        <p ng-show="data.suggestionStatus == 'sending'">
            <img src="<?php $this->asset('img/spinner.gif') ?>" /> <?php i::_e('Enviando mensagem'); ?>
        </p>
    </form>
<?php endif;?>
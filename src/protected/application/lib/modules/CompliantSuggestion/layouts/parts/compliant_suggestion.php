<?php
$app = \MapasCulturais\App::i();
$user = $app->user;
if($this->controller->action === 'create')
    return false;
?>

<div class="compliant-suggestion-box">
    <?php if(isset($compliant)): ?>
    <input class="button-form-compliant-suggestion compliant btn-primary" type="button" name="compliant" value="<?php \MapasCulturais\i::esc_attr_e('Denunciar'); ?>">
    <?php endif;?>
    <?php if(isset($suggestion)): ?>
    &nbsp;
    <input class="button-form-compliant-suggestion suggestion btn-success" type="button" name="suggestion" value="<?php \MapasCulturais\i::esc_attr_e('Contato'); ?>">
    <?php endif;?>
</div>


<?php if(isset($compliant)): ?>

<form class="form-complaint-suggestion js-compliant-form hidden" ng-controller="CompliantController">
    <?php if($user->is('guest')):?>
    <p>
        <?php \MapasCulturais\i::_e("Nome");?>:<br />
        <input ng-model="data.name" type="text" rows="5" name="nome" class="input-name">
    </p>
    <p>
        <?php \MapasCulturais\i::_e("E-mail");?>:<br />
        <input ng-model="data.email" type="text" rows="5" name="email" class="input-email">
    </p>
    <?php endif;?>
    <p>
        <?php \MapasCulturais\i::_e("Tipo");?>:</br>
        <select ng-model="data.type" ng-options="item for item in compliant_type" class="compliant-type"></select>
    </p>
    <p>
        <?php \MapasCulturais\i::_e("Mensagem");?>:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
    </p>
    <p>
        <input type='checkbox' ng-model="data.anonimous" name="anonimous">
        <label for="anonimous"><?php \MapasCulturais\i::_e("Denúncia anônima");?></label>
    </p>
    <p>
        <input type='checkbox' ng-model="data.copy" name="copy">
        <label for="copy"><?php \MapasCulturais\i::_e("Receber cópia da denúncia");?></label>
    </p>
    <p>
        <button class="js-submit-button compliant-form" ng-click="send()"><?php \MapasCulturais\i::_e("Enviar Denúncia");?></button>
    </p>
    <div class="widget compliant-box" hidden="hidden">
        <p class="alert sucess"><?php \MapasCulturais\i::_e("Enviado com Sucesso");?>.<span class="close compliant-form"></span></p>
    </div>
</form>
<?php endif;?>

<?php if(isset($suggestion)): ?>
<form class="form-complaint-suggestion js-suggestion-form hidden" ng-controller="SuggestionController">
    <?php if($user->is('guest')):?>
    <p>
        <?php \MapasCulturais\i::_e("Nome");?>:<br />
        <input ng-model="data.name" type="text" rows="5" name="name" class="input-name">
    </p>
    <p>
        <?php \MapasCulturais\i::_e("E-mail");?>:<br />
        <input ng-model="data.email" type="text" rows="5" name="email" class="input-email">
    </p>
    <?php endif;?>
    <p>
        <?php \MapasCulturais\i::_e("Tipo");?>:</br>
        <select ng-model="data.type" ng-options="item for item in suggestion_type" class="suggestion-type"></select>
    </p>
    <p>
        <?php \MapasCulturais\i::_e("Mensagem");?>:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
    </p>
    <p>
        <input id="anonimous" type='checkbox' ng-model="data.anonimous" name="anonimous">
        <label for="anonimous"><?php \MapasCulturais\i::_e("Mensagem anônima");?></label>
    </p>
    <p>
        <input id="only_owner" type='checkbox' ng-model="data.only_owner" name="only_owner">
        <label for="only_owner"><?php \MapasCulturais\i::_e("Enviar somente para o Responsável");?></label>
    </p>
    <p>
        <input type='checkbox' ng-model="data.copy" name="copy">
        <label for="copy"><?php \MapasCulturais\i::_e("Receber cópia da mensagem");?></label>
    </p>
    <p>
        <button class="js-submit-button suggestion-form" ng-click="send()"><?php \MapasCulturais\i::_e("Enviar mensagem");?></button>
    </p>
    <div class="widget suggestion-box" hidden="hidden">
        <p class="alert sucess"><?php \MapasCulturais\i::_e("Enviado com Sucesso");?>.<span class="close suggestion-form"></span></p>
    </div>
</form>
<?php endif;?>

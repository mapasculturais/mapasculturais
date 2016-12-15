<?php
$app = \MapasCulturais\App::i();
$user = $app->user;
if($this->controller->action === 'create')
    return false;
?>
<style>
    .input-name {
        width: 350px;
    }

    .input-email {
        width: 350px;
    }
</style>

<div class="compliant-suggestion-box">
    <?php if($compliant): ?>
    <input class="button-form-compliant-suggestion compliant btn-primary" type="button" name="compliant" value="Denuncia">
    <?php endif;?>
    <?php if($suggestion): ?>
    &nbsp;
    <input class="button-form-compliant-suggestion suggestion btn-success" type="button" name="suggestion" value="Contato">
    <?php endif;?>
</div>


<?php if($compliant): ?>

<form class="form-complaint-suggestion js-compliant-form hidden" ng-controller="CompliantController">
    <?php if($user->is('guest')):?>
    <p>
        Nome:<br />
        <input ng-model="data.name" type="text" rows="5" name="nome" class="input-name">
    </p>
    <p>
        E-mail:<br />
        <input ng-model="data.email" type="text" rows="5" name="email" class="input-email">
    </p>
    <?php endif;?>
    <p>
        Tipo:</br>
        <select ng-model="data.type" ng-options="item for item in compliant_type"></select>
    </p>
    <p>
        Mensagem:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
    </p>
    <p>
        <input type='checkbox' ng-model="data.anonimous" name="anonimous">
        <label for="anonimous">Denúncia anônima</label>
    </p>
    <p>
        <input type='checkbox' ng-model="data.copy" name="copy">
        <label for="copy">Receber cópia da denúncia</label>
    </p>
    <p>
        <button class="js-submit-button compliant-form" ng-click="send()">Enviar Denúncia</button>
    </p>
    <div class="widget compliant-box" hidden="hidden">
        <p class="alert sucess">Enviado com Sucesso.<span class="close"></span></p>
    </div>
</form>
<?php endif;?>

<?php if($suggestion): ?>
<form class="form-complaint-suggestion js-suggestion-form hidden" ng-controller="SuggestionController">
    <p>
        Nome:<br />
        <input ng-model="data.name" type="text" rows="5" name="name" class="input-name">
    </p>
    <p>
        E-mail:<br />
        <input ng-model="data.email" type="text" rows="5" name="email" class="input-email">
    </p>
    <p>
    Tipo:</br>
        <select ng-model="data.type" ng-options="item for item in suggestion_type"></select>
    </p>
    <p>
        Mensagem:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="56" name="message"></textarea>
    </p>
    <p>
        <input id="anonimous" type='checkbox' ng-model="data.anonimous" name="anonimous">
        <label for="anonimous">Mensagem anônima</label>
    </p>
    <p>
        <input id="only_owner" type='checkbox' ng-model="data.only_owner" name="only_owner">
        <label for="only_owner">Enviar somente para o Responsável</label>
    </p>
    <p>
        <input type='checkbox' ng-model="data.copy" name="copy">
        <label for="copy">Receber cópia da mensagem</label>
    </p>
    <p>
        <button class="js-submit-button suggestion-form" ng-click="send()">Enviar mensagem</button>
    </p>
    <div class="widget suggestion-box" hidden="hidden">
        <p class="alert sucess">Enviado com Sucesso.<span class="close"></span></p>
    </div>
</form>
<?php endif;?>

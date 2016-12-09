<?php
if($this->controller->action === 'create')
    return false;
?>

<div class="compliant-suggestion-box">
    <input class="button-form-compliant-suggestion compliant btn-primary" type="button" name="Envia" value="Denuncia">
    &nbsp;
    <input class="button-form-compliant-suggestion suggestion btn-success" type="button" name="Envia" value="Contato">
</div>

<form class="form-complaint-suggestion js-compliant-form hidden" ng-controller="CompliantController">
    <p>
        Nome:<br />
        <input ng-model="data.name" type="text" rows="5" name="nome">
    </p>
    <p>
        E-mail:<br />
        <input ng-model="data.email" type="text" rows="5" name="email">
    </p>
    <p>
        Denuncia anônima:
        <input type='checkbox' ng-model="data.anonimous" name="anonimous">
    </p>
    <p>
    Tipo:</b>
        <select ng-model="data.type" ng-options="item for item in compliant_type"></select>
    </p>
    <p>
        Mensagem:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="40" name="message"></textarea>
    </p>
    <p>
        <button class="js-submit-button" ng-click="send()">Enviar Denúncia</button>
    </p>
</form>

<form class="form-complaint-suggestion js-suggestion-form hidden" action="" ng-controller="SuggestionController">
    <p>
        Nome:<br />
        <input ng-model="data.name" type="text" rows="5" name="nome">
    </p>
    <p>
        E-mail:<br />
        <input ng-model="data.email" type="text" rows="5" name="email">
    </p>
    <p>
    Tipo:</br>
        <select ng-model="data.type">
            <option value="0">Sugestão</option>
            <option value="1">Crítica</option>
            <option value="2">Comentários</option>
            <option value="3">Reclamações</option>
            <option value="4">Outros</option>
        </select>
    </p>
    <p>
        <input id="anonimous" type='checkbox' ng-model="data.anonimous" name="anonimous">
        <label for="anonimous">Sugestão anônima</label>
        &nbsp;
        <input id="only_owner" type='checkbox' ng-model="data.only_owner" name="only_owner">
        <label for="only_owner">Enviar somente para o Responsável</label>
    </p>
    <p>
        Mensagem:<br />
        <textarea ng-model="data.message" type="text" rows="5" cols="40" name="message"></textarea>
    </p>
    <p>
        <button class="js-submit-button" ng-click="send()">Enviar</button>
    </p>
</form>

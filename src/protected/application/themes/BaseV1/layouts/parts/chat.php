<?php
// require $thread_id

use MapasCulturais\i;

?>

<div ng-controller="ChatController" ng-init="setThreadId(<?= $thread_id ?>)" class="chat">
    <header></header>
    <div ng-repeat="item in data.messages" class="message">
        <p class="name">Nome</p>
        <p>Mensagem</p>
    </div>
    <footer>
        <input type="text" ng-model="data.newMessage" ng-keypress="($event.which === 13 && data.newMessage.length) ? sendMessage(data.newMessage) : ''" placeholder="<?php i::__("Escreva uma mensagem"); ?>">
        <button ng-disabled="!data.newMessage" ng-click="sendMessage(data.newMessage)"><?php i::_e("Enviar"); ?></button>
    </footer>
</div>
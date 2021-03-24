<?php
// require $thread_id

use MapasCulturais\i;

$closed = $closed ?? 'false';
?>

<div ng-controller="ChatController" ng-if="<?= $thread_id ?>" ng-hide="data.messages.length == 0 && isChatClosed()" ng-init="init(<?= $thread_id ?>)" class="chat">
    <div ng-repeat="(key, item) in data.messages" class="message" ng-class="(data.currentUserId != item.user.profile.id) ? '' : 'received'">
        <span class="date" ng-if="item.date != data.messages[key - 1].date">{{item.date}}</span>

        <div class="container">
            <p class="name" ng-if="item.user.profile.id != data.messages[key - 1].user.profile.id">{{item.user.profile.name}}</p>
            <span class="time">{{item.time}}</span>
            <p>{{item.payload}}</p>
        </div>
    </div>
    <footer ng-if="!isChatClosed()" ng-style="{'border-top-width': (data.messages.length == 0) ? '0px' : '1px'}">
        <textarea class="new-message" ng-model="data.newMessage" ng-style="{'height': (!data.newMessage) ? 'auto' : ''}" placeholder="<?php i::_e("Escreva uma mensagem"); ?>"></textarea>
        <button ng-disabled="!data.newMessage || data.sending" ng-click="sendMessage(data.newMessage)"><?php i::_e("Enviar"); ?></button>
    </footer>
</div>
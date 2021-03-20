<?php
// require $thread_id

use MapasCulturais\i;

?>

<div ng-controller="ChatController" ng-init="setThreadId(<?= $thread_id ?>)" ng-class="(data.messages.length) ? 'chat' : 'hidden'">
    <div ng-repeat="(key, item) in data.messages" class="message" ng-class="(data.currentUserId != item.user.profile.id) ? '' : 'received'">
        <span class="date" ng-if="item.date != data.messages[key - 1].date">{{item.date}}</span>

        <div class="container">
            <p class="name" ng-if="item.user.profile.id != data.messages[key - 1].user.profile.id">{{item.user.profile.name}}</p>
            <span class="time">{{item.time}}</span>
            <p>{{item.payload}}</p>
        </div>
    </div>
    <footer>
        <input type="text" ng-model="data.newMessage" ng-keypress="($event.which === 13 && data.newMessage.length) ? sendMessage(data.newMessage) : ''" placeholder="<?php i::_e("Escreva uma mensagem"); ?>">
        <button ng-disabled="!data.newMessage" ng-click="sendMessage(data.newMessage)"><?php i::_e("Enviar"); ?></button>
    </footer>
</div>
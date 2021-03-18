<?php
// require $thread_id

?>

<div ng-controller="ChatController" ng-init="setThreadId(<?= $thread_id ?>)">
    {{data.threadId}}
    
</div>
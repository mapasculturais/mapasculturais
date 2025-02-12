<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\ChatThread;
use MapasCulturais\Entities\Registration;
use OpportunityAppealPhase\Module;

/**
 * @var MapasCulturais\Entities\Registration
 */
$registration = $this->controller->requestedEntity;
$registration_number = $registration->number;

$registration_query = new ApiQuery(Registration::class, [
    'number' => API::EQ($registration_number),
]);

$thread_query = new ApiQuery(ChatThread::class, [
    '@select'    => '*',
    'objectType' => API::EQ(Registration::class),
    'type'       => API::EQ(Module::CHAT_THREAD_TYPE),
]);

$thread_query->addFilterByApiQuery($registration_query, 'id', 'objectId');

$chat_threads = $thread_query->find();

$result = [];

foreach( $chat_threads as $chat_thread ) {
    $result[$chat_thread['objectId']] = $chat_thread;
}

$this->jsObject['config']['appealPhaseChat'] = $result;

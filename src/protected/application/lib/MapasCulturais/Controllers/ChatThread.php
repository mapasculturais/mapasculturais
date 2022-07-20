<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;
use MapasCulturais\Entities;

/**
 * @property-read Entities\ChatThread $requestedEntity
 */
class ChatThread extends Controller {
    /**
     * @return Entities\ChatThread
     */
    function getRequestedEntity() 
    {
        $app = App::i();

        $chat_id = $this->data['id'];

        return $app->repo('ChatThread')->find($chat_id);
    }

    function POST_close ()
    {
        $this->requireAuthentication();

        $chat = $this->requestedEntity;

        $chat->checkPermission('@control');

        $chat->setStatus(Entities\ChatThread::STATUS_DISABLED);

        $chat->save(true);

        $this->json($chat);
    }

    function POST_open ()
    {
        $this->requireAuthentication();

        $chat = $this->requestedEntity;

        $chat->checkPermission('@control');

        $chat->setStatus(Entities\ChatThread::STATUS_ENABLED);

        $chat->save(true);

        $this->json($chat);
    }
}
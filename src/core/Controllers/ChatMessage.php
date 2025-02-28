<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;
use MapasCulturais\API;

class ChatMessage extends EntityController
{
    use Traits\ControllerAPI,
        Traits\ControllerAPINested,
        Traits\ControllerUploads;

    function __construct()
    {
        parent::__construct();

        $app = \MapasCulturais\App::i();

        $app->hook('API.find(chatmessage).result', function() use($app) {
            /** @var Controller $this */
            $params = $this->data;
            if (!empty($params['thread']) && preg_match('/^EQ\(\d+\)$/', $params['thread'])) {
                $threadId = intval(substr($params['thread'], 3, -1));
                $thread = $app->repo('ChatThread')->findOneBy(['id' => $threadId]);

                header('MC-Thread-Status: ' . $thread->status);
            }
            
        });
    }
}
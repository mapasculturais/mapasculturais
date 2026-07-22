<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\Traits;
use MapasCulturais\API;

/**
 * Controlador de Mensagens de Chat
 *
 * Este controlador gerencia as operações relacionadas a mensagens de chat (ChatMessage)
 * no sistema Mapas Culturais. As mensagens de chat são usadas para comunicação
 * entre usuários dentro de threads de conversa.
 *
 * @property-read \MapasCulturais\Entities\ChatMessage $newEntity Nova instância de mensagem de chat
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório de mensagens de chat
 * @property-read array $fields Campos da entidade ChatMessage
 * @property-read \MapasCulturais\Entities\ChatMessage $requestedEntity Mensagem de chat solicitada
 * 
 * @package MapasCulturais\Controllers
 */
class ChatMessage extends EntityController
{
    use Traits\ControllerAPI,
        Traits\ControllerAPINested,
        Traits\ControllerUploads;

    /**
     * Construtor do controlador de mensagens de chat
     *
     * Configura hooks para adicionar cabeçalhos HTTP com status da thread
     * nas respostas da API de busca.
     */
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
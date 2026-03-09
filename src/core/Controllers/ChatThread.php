<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;
use MapasCulturais\Entities;

/**
 * Controlador de Threads de Chat
 *
 * Este controlador gerencia as operações relacionadas a threads de chat (ChatThread)
 * no sistema Mapas Culturais. Threads de chat são conversas entre usuários
 * que podem ser abertas ou fechadas conforme necessário.
 *
 * @property-read Entities\ChatThread $requestedEntity Thread de chat solicitada
 * 
 * @package MapasCulturais\Controllers
 */
class ChatThread extends Controller {
    /**
     * Retorna a thread de chat solicitada
     *
     * Busca a thread de chat pelo ID fornecido nos dados da requisição.
     *
     * @return Entities\ChatThread|null Thread de chat encontrada ou null se não existir
     */
    function getRequestedEntity() : ?Entities\ChatThread
    {
        $app = App::i();

        $chat_id = $this->data['id'];

        return $app->repo('ChatThread')->find($chat_id);
    }

    /**
     * Fecha uma thread de chat
     *
     * Esta ação requer autenticação e permissão de controle sobre a thread.
     * Altera o status da thread para DESABILITADA, impedindo novas mensagens.
     *
     * @api {POST} /chatthread/close Fechar thread de chat
     * @apiDescription Fecha uma thread de chat, impedindo novas mensagens
     * @apiGroup CHATTHREAD
     * @apiName close
     * @apiPermission @control
     * @apiParam {Number} id ID da thread de chat
     *
     * @return void
     */
    function POST_close ()
    {
        $this->requireAuthentication();

        $chat = $this->requestedEntity;

        $chat->checkPermission('@control');

        $chat->setStatus(Entities\ChatThread::STATUS_DISABLED);

        $chat->save(true);

        $this->json($chat);
    }

    /**
     * Abre uma thread de chat
     *
     * Esta ação requer autenticação e permissão de controle sobre a thread.
     * Altera o status da thread para HABILITADA, permitindo novas mensagens.
     *
     * @api {POST} /chatthread/open Abrir thread de chat
     * @apiDescription Abre uma thread de chat, permitindo novas mensagens
     * @apiGroup CHATTHREAD
     * @apiName open
     * @apiPermission @control
     * @apiParam {Number} id ID da thread de chat
     *
     * @return void
     */
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
<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Traits;

/**
 * Controlador de Presenças em Eventos
 *
 * Este controlador gerencia as operações relacionadas a presenças em eventos (EventAttendance)
 * no sistema Mapas Culturais. As presenças registram a participação de usuários em eventos.
 *
 * Por padrão, este controlador é registrado com o ID 'eventattendance'.
 *
 * @property-read \MapasCulturais\Entities\EventAttendance $newEntity Nova instância de presença em evento
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório de presenças em eventos
 * @property-read array $fields Campos da entidade EventAttendance
 * @property-read \MapasCulturais\Entities\EventAttendance $requestedEntity Presença em evento solicitada
 * 
 * @package MapasCulturais\Controllers
 */
class EventAttendance extends EntityController {
    use Traits\ControllerAPI;
    
    /**
     * Redireciona requisições para criação de presenças
     *
     * Este método impede o acesso direto à criação de presenças via interface web,
     * redirecionando a requisição para o próximo handler disponível.
     *
     * @return void
     */
    function GET_create() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições para edição de presenças
     *
     * Este método impede o acesso direto à edição de presenças via interface web,
     * redirecionando a requisição para o próximo handler disponível.
     *
     * @return void
     */
    function GET_edit() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições para listagem de presenças
     *
     * Este método impede o acesso direto à listagem de presenças via interface web,
     * redirecionando a requisição para o próximo handler disponível.
     *
     * @return void
     */
    function GET_index() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições para visualização individual de presenças
     *
     * Este método impede o acesso direto à visualização individual de presenças via interface web,
     * redirecionando a requisição para o próximo handler disponível.
     *
     * @return void
     */
    function GET_single() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições POST para criação/atualização de presenças
     *
     * Este método impede o acesso direto à criação/atualização de presenças via interface web,
     * redirecionando a requisição para o próximo handler disponível.
     *
     * @return void
     */
    function POST_single() {
        App::i()->pass();
    }
}

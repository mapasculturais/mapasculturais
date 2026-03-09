<?php
namespace MapasCulturais\Traits;

/**
 * Trait para controladores que suportam alteração de proprietário de entidades
 * 
 * Este trait fornece um método para alterar o proprietário (owner) de uma entidade,
 * permitindo transferir a propriedade para outro agente.
 * 
 * @package MapasCulturais\Traits
 */
trait ControllerChangeOwner{
    
    /**
     * Altera o proprietário de uma entidade
     * 
     * Este método altera o agente proprietário da entidade para o agente
     * especificado pelo ID fornecido. Requer autenticação do usuário.
     * 
     * @api POST changeOwner
     * @return void Finaliza a requisição com a entidade atualizada
     * 
     * @throws \MapasCulturais\Exceptions\PermissionDenied Se o usuário não tiver permissão
     * @throws \Exception Se o ownerId não for fornecido ou o agente não for encontrado
     * 
     * @uses \MapasCulturais\Controllers\EntityController::_finishRequest()
     */
    function POST_changeOwner(){
        $this->requireAuthentication();

        $app = \MapasCulturais\App::i();

        if(!key_exists('ownerId', $this->postData))
            $this->errorJson(\MapasCulturais\i::__('The ownerId is required.'));

        $owner = $app->repo('Agent')->find($this->postData['ownerId']);

        if(!$owner)
            $this->errorJson(sprintf (\MapasCulturais\i::__('The agent with id %s not found.'), $this->postData['ownerId']));

        $entity = $this->requestedEntity;

        if(!$entity)
            $app->pass();

        $entity->owner = $owner;

        $this->_finishRequest($entity, true);
    }
}

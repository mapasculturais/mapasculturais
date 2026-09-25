<?php
namespace MapasCulturais\Exceptions;

use MapasCulturais\Entity;
use MapasCulturais\i;

/**
 * Exceção lançada quando um usuário não tem permissão para executar uma ação
 * 
 * Esta exceção é usada pelo sistema de controle de permissões do Mapas Culturais
 * para indicar que um usuário não possui as permissões necessárias para realizar
 * uma determinada ação sobre uma entidade.
 * 
 * @package MapasCulturais\Exceptions
 */
class PermissionDenied extends \Exception{
    use \MapasCulturais\Traits\MagicGetter;
    
    /**
     * Código de erro para entidade bloqueada
     */
    const CODE_ENTITY_LOCKED = 1;

    /**
     * Usuário que tentou executar a ação
     * @var mixed
     */
    protected $user;

    /**
     * Objeto alvo da ação
     * @var mixed
     */
    protected $targetObject;

    /**
     * Ação que foi tentada
     * @var string
     */
    protected $action;

    /**
     * Construtor da exceção PermissionDenied
     * 
     * @param mixed $user Usuário que tentou executar a ação
     * @param mixed $object Objeto alvo da ação
     * @param string $action Ação que foi tentada
     * @param string $message Mensagem personalizada (opcional)
     * @param int $code Código de erro (opcional)
     */
    public function __construct($user = null, $object = null, $action = '', $message = '', int $code = 0) {
        $this->user = $user;
        $this->targetObject = $object;
        $this->action = $action;
        $user_id = is_object($user) ? $user->id : 'guest';

        if(!$message) {
            if($object instanceof Entity && $action) {
                $entity_type = $object->entityTypeLabel ?: $object->entityType;
                if($object->id) {
                    $message = sprintf(i::__('O usuário %s não tem permissão para "%s" o %s de id %s'), $user_id, $action, $entity_type, $object->id);
                } else {
                    $message = sprintf(i::__('O usuário %s não tem permissão para "%s" o %s'), $user_id, $action, $entity_type);
                }
            } else if ($object instanceof Entity) {
                $entity_type = $object->entityTypeLabel ?: $object->entityType;
                $message = sprintf(i::__('O usuário %s não tem permissão sobre o %s de id %s'), $user_id, $entity_type, $object->id);
            } else if ($action) {
                $message = sprintf(i::__('O usuário %s não tem permissão para "%s"'), $user_id, $action);
            } else if(!$message) {
                $message = sprintf(i::__('O usuário %s não tem permissão para executar esta ação'), $user_id);
            }
        }

        parent::__construct($message, $code);
    }
}

<?php
namespace MapasCulturais\Exceptions;

use MapasCulturais\Entity;
use MapasCulturais\i;

class PermissionDenied extends \Exception{
    use \MapasCulturais\Traits\MagicGetter;

    protected $user;

    protected $targetObject;

    protected $action;

    public function __construct($user, $object = null, $action = '') {
        $this->user = $user;
        $this->targetObject = $object;
        $this->action = $action;
        $user_id = is_object($user) ? $user->id : 'guest';

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
        } else {
            $message = sprintf(i::__('O usuário %s não tem permissão para executar esta ação'), $user_id);
        }

        parent::__construct($message);
    }
}
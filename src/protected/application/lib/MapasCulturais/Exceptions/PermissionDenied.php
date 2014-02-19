<?php
namespace MapasCulturais\Exceptions;

class PermissionDenied extends \Exception{
    use \MapasCulturais\Traits\MagicGetter;

    protected $user;

    protected $targetObject;

    protected $action;

    public function __construct($user, $targetObject, $action) {
        $this->user = $user;
        $this->targetObject = $targetObject;
        $this->action = $action;
        $user_id = is_object($user) ? $user->id : 'guest';

        $class = str_replace('MapasCulturais\Entities\\', '', get_class($targetObject));
        $message = "User with id {$user_id} is trying to $action the $class with the id $targetObject->id";

        parent::__construct($message);
    }
}
<?php

namespace MapasCulturais\Traits;

use MapasCulturais\App;
use MapasCulturais\Entities\AgentRelation;
use MapasCulturais\Entities\User;
use MapasCulturais\Entity;

trait EntityPrivate
{
    use EntityDraft;

    const STATUS_PRIVATE = -100;

    /**
     * This entity uses Archive
     *
     * @return bool true
     */
    public static function usesPrivate()
    {
        return true;
    }

    function getMakePrivateUrl()
    {
        return App::i()->createUrl($this->controllerId, 'makePrivate', [$this->id]);
    }

    function makePrivate($flush = true)
    {
        $this->checkPermission('makePrivate');

        $hook_class_path = $this->getHookClassPath();

        $app = App::i();
        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').makePrivate:before');

        $this->setStatus(self::STATUS_PRIVATE);

        $this->save($flush);

        if ($this->usesFiles()) {
            $this->makeFilesPrivate();
        }

        $app->applyHookBoundTo($this, 'entity(' . $hook_class_path . ').makePrivate:after');
    }

    protected function canUserMakePrivate($user)
    {
        return $this->canUser('@control', $user);
    }

    protected function canUserMakePublic($user)
    {
        return $this->canUser('@control', $user);
    }

    protected function canUserView($user)
    {
        /** @var Entity $this */
        if ($this->status != self::STATUS_PRIVATE) {
            return parent::canUserView($user);
        }

        if ($user->is('guest')) {
            return false;
        }

        if ($this->isUserAdmin($user)) {
            return true;
        }

        if ($this->canUser('@control', $user)) {
            return true;
        }

        if ($this->usesAgentRelation()) {
            foreach ($this->agentRelations as $agent_relation) {
                /** @var AgentRelation $agent_relation */
                if ($agent_relation->agent->user->equals($user)) {
                    return true;
                }
            }
        }

        return false;
    }
}

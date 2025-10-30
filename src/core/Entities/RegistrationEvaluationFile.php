<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "MapasCulturais\Repository")]
class RegistrationEvaluationFile extends File{

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\RegistrationEvaluation")]
    #[ORM\JoinColumn(name: "object_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $owner;

    #[ORM\ManyToOne(targetEntity: "MapasCulturais\Entities\RegistrationEvaluationFile", fetch: "EAGER")]
    #[ORM\JoinColumn(name: "parent_id", referencedColumnName: "id", onDelete: "CASCADE")]
    protected $parent;

    protected function canUserCreate($user)
    {
        // não permite editar/atualizar o arquivo quando a avaliação tem status > 0
        if ($this->owner->status > 0) {
            return false;
        }

        return $this->owner->user->canUser("modify", $user);
    }

    protected function canUserModify($user)
    {
        // não permite editar/atualizar o arquivo quando a avaliação tem status > 0
        if ($this->owner->status > 0) {
            return false;
        }

        return $this->owner->user->canUser("modify", $user);
    }

    protected function canUserRemove($user)
    {
        // permite que o usuário que enviou o arquivo possa remover
        if ($this->getOwnerUser()->id == $user->id) {
            return true;
        }

        return $this->owner->canUser('modify');
    }
}
<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RegistrationEvaluationFile extends File{

    /**
     * @var \MapasCulturais\Entities\RegistrationEvaluation
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationEvaluation")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\RegistrationEvaluationFile
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationEvaluationFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;

    protected function canUserCreate($user)
    {
        return $this->owner->user->canUser("modify", $user);
    }

    protected function canUserModify($user)
    {
        return $this->owner->user->canUser("modify", $user);
    }
}
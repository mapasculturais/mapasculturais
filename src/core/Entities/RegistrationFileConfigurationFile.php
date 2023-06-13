<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RegistrationFileConfigurationFile extends File{

    /**
     * @var \MapasCulturais\Entities\RegistrationFileConfiguration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationFileConfiguration")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $owner;

    /**
     * @var \MapasCulturais\Entities\RegistrationFileConfigurationFile
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\RegistrationFileConfigurationFile", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $parent;
}
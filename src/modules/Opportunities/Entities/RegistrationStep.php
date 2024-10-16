<?php
namespace Oportunities\Entities;

use Doctrine\ORM\Mapping as ORM;

class RegistrationStep extends \MapasCulturais\Entity 
{   

    /**
     * @var integer
     *
     * @ORM\Column(name="step_id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     * 
     * @ORM\Column(name="step_name", type="string")
     */
    protected $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime")
     */
    protected $createTimestamp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_timestamp", type="datetime")
     */
    protected $updateTimestamp;

    private function __construct() 
    { 
    }

}
<?php

namespace MapasCulturais\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entity;
use stdClass;

/**
 * Job
 *
 * @property-read string $id
 * @property string $type
 * @property-read int $iterations
 * @property-read int $iterationsCount
 * @property-read string $intervalString
 * @property-read string $type
 * 
 * @ORM\Table(name="job", indexes={
 *      @ORM\Index(name="job_next_execution_timestamp_idx", columns={"next_execution_timestamp"}),
 *      @ORM\Index(name="job_search_idx", columns={"next_execution_timestamp", "iterations_count", "status"})
 *    }   
 * )
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class Job extends \MapasCulturais\Entity{

    const STATUS_WAITING = 0;
    const STATUS_PROCESSING = 1;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=32, nullable=false)
     */
    protected $type;

    /**
     * Número execuções.
     * 
     * 0 (zero) significa que deve executar infinitamente
     * 
     * @var integer
     *
     * @ORM\Column(name="iterations", type="integer", nullable=false)
     */
    protected $iterations = 1;

    /**
     * Número de vezes que o processo já rodou
     * 
     * @var integer
     *
     * @ORM\Column(name="iterations_count", type="integer", nullable=false)
     */
    protected $iterationsCount = 0;

    /**
     * Número de vezes que o processo já rodou
     * 
     * @var integer
     *
     * @ORM\Column(name="interval_string", type="string", nullable=false)
     */
    protected $intervalString = '';    

    /**
     * @var DateTime
     *
     * @ORM\Column(name="create_timestamp", type="datetime", nullable=false)
     */
    protected $createTimestamp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="next_execution_timestamp", type="datetime", nullable=false)
     */
    protected $nextExecutionTimestamp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_execution_timestamp", type="datetime", nullable=true)
     */
    protected $lastExecutionTimestamp;

    /**
     * @var object
     *
     * @ORM\Column(name="metadata", type="json_array", nullable=false)
     */
    protected $_metadata = [];

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    protected $status = self::STATUS_WAITING;

    function __construct(JobType $type) {
        $this->type = $type->slug;

        parent::__construct();
    }

    
    function __set($name, $value) {
        if($name == '_metadata') {
            return;
        }

        if(property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $value = $this->serializeEntity($value);
            $this->_metadata[$name] = $value;
        }

    }
    
    function __get($name) {
        $value = $this->$name ?? $this->_metadata[$name] ?? null;

        $value = $this->unserializeEntity($value);

        return $value;
    }


    protected function serializeEntity($value) {
        if ($value instanceof Entity) {
            $value = "@entity:{$value->className}:{$value->id}"; // some entities override __toString and we need this particular format
        } else if(is_array($value) || $value instanceof stdClass) {
            foreach($value as &$val) {
                $val = $this->serializeEntity($val);
            }
        }
        return $value;
    }

    protected function unserializeEntity($value) {
        if(is_string($value) && preg_match('#@entity:([^:]+):(\d+)#', $value, $matches)) {
            $app = App::i();
            $class = $matches[1];
            $id = $matches[2];

            $value = $app->repo($class)->find($id);
        } else if(is_array($value) || $value instanceof stdClass) {
            foreach($value as &$val) {
                $val = $this->unserializeEntity($val);
            }
        }

        return $value;
    }

    public function execute() {
        $app = App::i();

        $job_type = $app->getRegisteredJobType($this->type);

        $success = false;
        try {
            $success = $job_type->execute($this);
            
        } catch(\Exception $e) {
            $success = false;
        }

        if ($success){
            $this->iterationsCount++;
            
            if ($this->iterationsCount >= $this->iterations) {
                if($app->config['app.log.jobs']) {
                    $app->log->info("Job {$this->slug}:{$this->id}: SUCCESSFUL and TERMINATED");
                }

                $this->delete(true);
            } else {
                if($app->config['app.log.jobs']) {
                    $app->log->info("Job {$this->slug}:{$this->id}: SUCCESSFUL");
                }
                $this->status = 0;
                $this->lastExecutionTimestamp = new DateTime;
                $this->nextExecutionTimestamp = new DateTime(date('Y-m-d H:i:s', strtotime($this->intervalString, $this->nextExecutionTimestamp->getTimestamp())));
                
                $this->save(true);
            }

        } else {
            if($app->config['app.log.jobs']) {
                $app->log->info("Job {$this->slug}:{$this->id}: ERROR");
            }
        }

        return $success;
    }

    
    //============================================================= //
    // The following lines ara used by MapasCulturais hook system.
    // Please do not change them.
    // ============================================================ //

    /** @ORM\PrePersist */
    public function prePersist($args = null){ parent::prePersist($args); }
    /** @ORM\PostPersist */
    public function postPersist($args = null){ parent::postPersist($args); }

    /** @ORM\PreRemove */
    public function preRemove($args = null){ parent::preRemove($args); }
    /** @ORM\PostRemove */
    public function postRemove($args = null){ parent::postRemove($args); }

    /** @ORM\PreUpdate */
    public function preUpdate($args = null){ parent::preUpdate($args); }
    /** @ORM\PostUpdate */
    public function postUpdate($args = null){ parent::postUpdate($args); }
}

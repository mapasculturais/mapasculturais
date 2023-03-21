<?php

namespace MapasCulturais\Definitions;

use Closure;
use InvalidArgumentException;
use MapasCulturais\App;
use MapasCulturais\Entities\Job;

/**
 * @property-read string $slug
 * @property-read Closure $handleFunction
 * @property-read Closure $idGeneratorFunction
 * 
 * @package MapasCulturais\Definitions
 */
abstract class JobType extends \MapasCulturais\Definition {
    
    protected $_slug;

    function __construct(string $slug) {
        $this->_slug = $slug;
    }

    /**
     * 
     * @return string 
     */
    function getSlug() {
        return $this->_slug;
    }

    /**
     * 
     * @param mixed $data 
     * @param mixed $start_string 
     * @param mixed $interval_string 
     * @param mixed $iterations 
     * @return string 
     */
    function generateId(array $data, string $start_string, string $interval_string, int $iterations) {
        $id = $this->_generateId($data, $start_string, $interval_string, $iterations);

        return md5("{$this->_slug}:{$id}");
    }

    /**
     * 
     * @param Job $job 
     * @return bool 
     */
    function execute(Job $job) {
        $app = App::i();

        $app->applyHookBoundTo($job, "job({$this->slug}).execute:before");
        
        $app->disableAccessControl();
        $result = $this->_execute($job);
        $app->enableAccessControl();

        $app->applyHookBoundTo($job, "job({$this->slug}).execute:after", [&$result]);

        return $result;
    }

    /**
     * 
     * @param mixed $data 
     * @param string $start_string 
     * @param string $interval_string 
     * @param int $iterations 
     * @return string 
     */
    abstract protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations);


    /**
     * 
     * @param Job $job 
     * @return bool
     */
    abstract protected function _execute(Job $job);

}
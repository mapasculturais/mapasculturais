<?php

namespace Spreadsheets\Jobs;

use MapasCulturais\Entities\Job;
use MapasCulturais\Definitions\JobType;

abstract class SpreadsheetJob extends JobType
{
    protected $file_group;

    public function __construct(string $slug)
    {
        parent::__construct($slug);
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
     * @return string
     */
    abstract protected function getFileGroup();

    /**
     * @return array
     */
    abstract protected function getTargetEntities();

    protected function _execute(Job $job)
    {
        
    }
}

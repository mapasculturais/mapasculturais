<?php
namespace MapasCulturais\Loggers\DoctrineSQL;

use MapasCulturais\App;

class SlimLog implements \Doctrine\DBAL\Logging\SQLLogger{
    protected $total_queries_time = 0;
    protected $time_start;

    protected $counter = 0;
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
    	$this->counter++;

        $this->time_start = microtime(true);

        $app = App::i();

        $app->log->debug("\n>>>>>>>>>>>>>>>>>>>>>>>>>>>> SQL Nº " . $this->counter);

        $app->log->debug($sql);

        if($params){
            $app->log->debug('PARAMS:');
            $app->log->debug(print_r($params, true));
        }

        if(false && $types){
            $app->log->debug('TYPES:');
            $app->log->debug(print_r($types, true));
        }


    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $app = App::i();

        $time_end = microtime(true);
        $execution_time = number_format($time_end - $this->time_start, 4);
        $this->total_queries_time += $execution_time;
        $app->log->debug("\nQUERY Nº {$this->counter} EXECUTED IN $execution_time seconds (TOTAL QUERIES TIME: $this->total_queries_time seconds)");
        $app->log->debug("<<<<<<<<<<<<<<<<<<<<<<<<<<<< SQL Nº {$this->counter}\n");
    }
}
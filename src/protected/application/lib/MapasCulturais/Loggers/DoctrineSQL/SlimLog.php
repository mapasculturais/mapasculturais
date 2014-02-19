<?php
namespace MapasCulturais\Loggers\DoctrineSQL;

use MapasCulturais\App;

class SlimLog implements \Doctrine\DBAL\Logging\SQLLogger{
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

//        if($sql == 'SELECT t0.id AS id1, t0.type AS type2, t0.name AS name3, t0.location AS location4, t0._geo_location AS _geo_location5, t0.short_description AS short_description6, t0.long_description AS long_description7, t0.create_timestamp AS create_timestamp8, t0.status AS status9, t0.is_user_profile AS is_user_profile10, t0.is_verified AS is_verified11, t0.user_id AS user_id12, t0.user_id AS user_id13 FROM agent t0 WHERE t0.id = ?')
//            die(var_dump(xdebug_get_function_stack()));
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
        $app->log->debug("\nQUERY Nº {$this->counter} EXECUTED IN $execution_time seconds");
        $app->log->debug("<<<<<<<<<<<<<<<<<<<<<<<<<<<< SQL Nº {$this->counter}\n");
    }
}
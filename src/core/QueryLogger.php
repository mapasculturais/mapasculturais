<?php
namespace MapasCulturais;

use DateTime;
use Doctrine\DBAL\Logging\SQLLogger;
use Respect\Validation\Rules\Date;

class QueryLogger implements SQLLogger {
    protected $lastQueryStartTimestamp;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $app = App::i();
        $app->log->debug("=============================================================");
        $current_datetime = new DateTime();
        $app->log->debug("QUERY STATRT at " . $current_datetime->format('Y-m-d H:i:s u'));
        $app->log->debug("");
        $app->log->debug($sql);
        $params && $app->log->debug(print_r($params, true));
        $types && $app->log->debug(print_r($types, true));

        $this->lastQueryStartTimestamp = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $app = App::i();
        $total_time = microtime(true) - $this->lastQueryStartTimestamp;
        
        $app->log->debug("");
        $app->log->debug("query executed in $total_time");
        $app->log->debug("=============================================================");
    }
}
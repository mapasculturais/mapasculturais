<?php
namespace MapasCulturais;

use Doctrine\DBAL\Logging\SQLLogger;

class QueryLogger implements SQLLogger {

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $app = App::i();
        $app->log->debug($sql);
        $params && $app->log->debug(print_r($params, true));
        $types && $app->log->debug(print_r($types, true));
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
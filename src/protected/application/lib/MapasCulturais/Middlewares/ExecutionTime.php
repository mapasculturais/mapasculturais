<?php
namespace MapasCulturais\Middlewares;
use \MapasCulturais\App;

/**
 * Calculates the request execution time and outputs it to application log.
 *
 * If on the construction of this object you pass true to $print param the request execution time will be
 * appended to the end of the page as a html comment.
 *
 * If you want tha the execution time be visible in the page, pass false to the $html_comment constrictor param.
 *
 */
class ExecutionTime extends \Slim\Middleware{
    protected $time_start;
    protected $html_comment;
    protected $print;


    /**
     * Creates the middleware.
     *
     * @param boolean $print print the execution time to the html body
     * @param boolean $html_comment print the execution time as a html comment
     */
    public function __construct($print = true, $html_comment = true) {
        $this->time_start = microtime(true);
        $this->html_comment = $html_comment;
        $this->print = $print;
    }

    /**
     * Executes the middleware.
     */
    public function call()
    {
        // Get reference to application
        $app = App::i();
        $app->log->info('');
        $app->log->info('=========================================================================');
        $app->log->info('(' . $app->request->getMethod() . ') ' . $app->request->getResourceUri() . ' - timestamp: ' . $this->udate('Y-m-d H:i:s.u'));

        // Run inner middleware and application
        $this->next->call();

        // Capitalize response body
        $res = $app->response;
        $body = $res->body();

        $time_end = microtime(true);

        $execution_time = number_format($time_end - $this->time_start, 4);
        $mem = memory_get_usage(true) / 1024 / 1024;
        $log_string = '(' . $app->request->getMethod() . ') ' . $app->request->getResourceUri() . ' - executed in ' . $execution_time . ' seconds. (MEM: ' . $mem . 'MB)';
        
        if($this->print){
            $append = $this->html_comment ?
                    "<!-- $log_string -->":
                    "<div class=\"log-execution-time\"> $log_string </div>";

            $body = preg_replace('#(</[ ]*html[ ]*>)#i', $append.'$1', $body);

            $res->body($body);
        }

        $app->log->info($log_string);
        $app->log->info('=========================================================================');

    }

    /**
     *
     * @param type $format
     * @param type $utimestamp
     *
     * @see http://stackoverflow.com/questions/169428/php-datetime-microseconds-always-returns-0
     *
     * @return type
     */
    function udate($format, $utimestamp = null){
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
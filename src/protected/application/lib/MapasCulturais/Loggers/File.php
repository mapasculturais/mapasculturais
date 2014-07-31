<?php
namespace MapasCulturais\Loggers;

class File{
    protected $uid;
    
    protected $first = true;

    protected $filenameGenerator;
    
    protected static $levels = array(
        \Slim\Log::EMERGENCY => 'EMERGENCY: ',
        \Slim\Log::ALERT     => 'ALERT:     ',
        \Slim\Log::CRITICAL  => 'CRITICAL:  ',
        \Slim\Log::ERROR     => 'ERROR:     ',
        \Slim\Log::WARN      => 'WARNING:   ',
        \Slim\Log::NOTICE    => 'NOTICE:    ',
        \Slim\Log::INFO      => 'INFO:      ',
        \Slim\Log::DEBUG     => 'DEBUG:     '
    );
    
    function __construct($filename_generator = null) {
        $this->uid = uniqid();
        
        if($filename_generator && is_callable($filename_generator))
            $this->filenameGenerator = $filename_generator;
        else
            $this->filenameGenerator = function(){
                return 'mapasculturais.log';
            };
    }
    
    function getLogLevel($level){
        
    }
    
    function write($message, $level){
        $filename_generator = $this->filenameGenerator;
        $filename = \MapasCulturais\App::i()->config['app.log.path'] . $filename_generator();
        
        if($this->first){
            $timestamp = date('Y-m-d H:i:s');
            $this->first = false;
            file_put_contents($filename, "\n\n===============================================", FILE_APPEND);
            file_put_contents($filename, "\n{$this->uid} > TIMESTAMP: $timestamp  \n", FILE_APPEND);
        }
        
        file_put_contents($filename, $this->uid . ' > ' . $message . "\n", FILE_APPEND);
    }
}
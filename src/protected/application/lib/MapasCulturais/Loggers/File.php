<?php
namespace MapasCulturais\Loggers;

use Slim\Log;

class File{
    protected $uid;
    
    protected $first = true;

    protected $filenameGenerator;
    
    protected static $levels = [
        Log::EMERGENCY => 'EMERGENCY: ',
        Log::ALERT     => 'ALERT:     ',
        Log::CRITICAL  => 'CRITICAL:  ',
        Log::ERROR     => 'ERROR:     ',
        Log::WARN      => 'WARNING:   ',
        Log::NOTICE    => 'NOTICE:    ',
        Log::INFO      => 'INFO:      ',
        Log::DEBUG     => 'DEBUG:     '
    ];
    
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
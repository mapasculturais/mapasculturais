<?php
namespace MapasCulturais\Loggers\Slim;

use MapasCulturais\App;

class DebugBar extends \Slim\LogWriter{

    public function __construct(){

    }

    public function write($message, $level = null)
    {
        \MapasCulturais\App::i()->debugbar['messages']->addMessage($message);
    }
}
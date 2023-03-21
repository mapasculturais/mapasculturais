<?php
namespace MapasCulturais\Middlewares;

class ErrorHandler extends \Slim\Middleware{
    protected $filenameGenerator = null;
    
    public function __construct($filename_generator = null) {
        if($filename_generator && is_callable($filename_generator))
            $this->filenameGenerator = $filename_generator;
        else
            $this->filenameGenerator = function(){
                return 'error.log';
            };
    }
    
    public function call(){
        $filename_generator = $this->filenameGenerator;
        $filename = $this->app->config['app.log.path'] . $filename_generator();
        
        $app = $this->app;
        
        $this->app->error(function(\Exception $e) use($filename, $app){
            file_put_contents($filename, "\n====================================================================\n", FILE_APPEND);
            file_put_contents($filename, "\n TIMESTAMP: " . date('Y-m-d H:i:s') . " \n", FILE_APPEND);
            file_put_contents($filename, "\n--------------------------------------------------------------------\n", FILE_APPEND);
            file_put_contents($filename, $e, FILE_APPEND);
            file_put_contents($filename, "\n====================================================================\n", FILE_APPEND);
        });
        
        // Call next middleware
        $this->next->call();
    }
}
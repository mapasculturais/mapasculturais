<?php 

return [
    'slim.middlewares' => 
        env('APP_MODE', 'production') == 'production' ? 
            [] :
            [new \MapasCulturais\Middlewares\ExecutionTime(true, false)]
];
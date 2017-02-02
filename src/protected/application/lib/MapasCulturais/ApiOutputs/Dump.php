<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use MapasCulturais;



class Dump extends \MapasCulturais\ApiOutput{

    protected function getContentType() {
        return 'text/html';
    }


    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $uriExplode = explode('/',$_SERVER['REQUEST_URI']);
        if($data && key_exists(2,$uriExplode) ){
            $singular_object_name = mb_convert_encoding($this->translate[$uriExplode[2]],"HTML-ENTITIES","UTF-8");
            $plural_object_name = $singular_object_name.'s';
        }
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title><?php echo sprintf(App::txts("%s $singular_object_name encontrado.", "%s $plural_object_name encontrados.", count($data)), count($data)) ?></title>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    table table th {text-align: left; white-space: nowrap; }
                </style>
            </head>
            <body>
               <?php \dump($data); ?>
            </body>
        </html>
        <?php
    }

    function _outputItem($data, $object_name = 'entity') {
        \dump($data); 
    }

    protected function _outputError($data) {
        \dump($data);
    }
}

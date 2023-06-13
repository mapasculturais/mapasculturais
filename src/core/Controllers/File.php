<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;

/**
 * File Controller
 *
 * By default this controller is registered with the id 'file'.
 *
 */
class File extends EntityController {
    public function POST_index($data = null) {
        App::i()->pass();
    }

    function GET_create() {
        App::i()->pass();
    }

    function GET_edit() {
        App::i()->pass();
    }

    function GET_index() {
        App::i()->pass();
    }

    function GET_single() {
        App::i()->pass();
    }

    function POST_single() {
        App::i()->pass();
    }
    
    function GET_privateFile() {
        $this->requireAuthentication();
        
        $app = App::i();
        
        $file = $this->requestedEntity;

        if(!$file){
            $app->pass();
        }

        $file->checkPermission('viewPrivateFiles');
        
        $file_path = $this->requestedEntity->getPath();
        
        if (file_exists($file_path)) {
            $headers = [
                'Content-Description' => 'File Transfer',
                'Content-Type' => mime_content_type($file_path),
                'Content-Disposition' => 'attachment; filename="' . $file->name . '"',
                'Content-Transfer-Encoding' => 'binary',
                'Expires' => '0',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Pragma' => 'public',
                'Content-Length' => filesize($file_path)
            ];

            $app->applyHookBoundTo($file, 'GET(file.privateFile).headers',[&$headers]);

            foreach($headers as $name => $value){
                header("{$name}: {$value}");    
            }
            
            readfile($file_path);
            
            exit;
        }
        
        $app->pass();
        
    }
}

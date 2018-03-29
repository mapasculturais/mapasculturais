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
        
        $file = $this->requestedEntity;

        $file->checkPermission('viewPrivateFiles');
        
        $file_path = $this->requestedEntity->getPath();
        
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: ' . mime_content_type($file_path));
            header('Content-Disposition: attachment; filename="' . $file->name . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            
            readfile($file_path);
            
            exit;
        }
        
        App::i()->pass();
        
    }
}

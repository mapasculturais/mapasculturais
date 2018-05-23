<?php

namespace MapasCulturais\Controllers;

use MapasCulturais\App;
/**
 * PermissionCache Controller
 * 
 */
class PermissionCache extends \MapasCulturais\Controller {
    public function GET_recreate() {
        try {
            $size = 100;
            if( isset($this->data['size']) ) {
                $size = $this->data['size'];
            }
            App::i()->recreatePermissionsCacheOfListedEntities($size);
            $this->json(['recreate' => true, 'size' => $size]);
        } catch (Exception $e) {
            $this->json(['recreate' => false, 'Trace' => $e->getTraceAsString()]);
        }
    }
}

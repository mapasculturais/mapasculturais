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

            $size = 250;
            if( isset($this->data['size']) ) {
                $size = (int) $this->data['size'];
            }
            $app = App::i();
            $app->recreatePermissionsCacheOfListedEntities($size);

            $this->json(['recreate' => true, 'size' => $size]);
        } catch (Exception $e) {
            $this->json(['recreate' => false, 'Trace' => $e->getTraceAsString()]);
        }
    }
}

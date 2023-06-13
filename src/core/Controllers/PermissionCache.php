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
            App::i()->recreatePermissionsCache();
            $this->json(['recreate' => true]);
        } catch (Exception $e) {
            $this->json(['recreate' => false, 'Trace' => $e->getTraceAsString()]);
        }
    }
}

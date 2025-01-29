<?php

namespace ProjectMonitoring;

use \MapasCulturais\App;
use \MapasCulturais\Entities;

class Controller extends \MapasCulturais\Controller {
    
    public function POST_reportingPhase() {
        $this->requireAuthentication();

        $app = App::i();

        return null;
    }
}
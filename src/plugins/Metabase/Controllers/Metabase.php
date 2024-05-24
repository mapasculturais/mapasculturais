<?php

namespace Metabase\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controller;

class Metabase extends Controller
{
    function __construct()
    {
    }

    public function GET_dashboard()
    {
        $panel_id = $this->data['panelId'];
        $app = App::i();
        // $app->view->enqueueStyle('app-v2', 'metabase', 'css/app.css');
        $this->render("single", ['panelId'=>$panel_id]);
    }

    public function GET_panel()
    {
        $app = App::i();
        // $app->view->enqueueStyle('app-v2', 'metabase', 'css/app.css');
        $this->render("panel");
    }
    
   
}

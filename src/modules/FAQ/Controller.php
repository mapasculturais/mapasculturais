<?php

namespace FAQ;

use MapasCulturais\App;

class Controller  extends \MapasCulturais\Controller
{
    function __construct()
    {

    }

    public function GET_questions() {
        // var_dump('ola mundo');
        $this->layout = 'faq-layout';
        $this->render('view');
    }
}

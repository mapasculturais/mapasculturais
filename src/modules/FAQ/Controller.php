<?php

namespace FAQ;

use MapasCulturais\App;

class Controller  extends \MapasCulturais\Controller
{
    public Module $module;

    function __construct()
    {
        $this->layout = 'faq-layout';
    }

    public function GET_index() {
        $app = App::i();

        $section = $this->data[0] ?? null;

        $faq = $this->module->getFAQ($section);

        $app->view->jsObject['faq'] = $faq;

        if($section) {
            $this->render('section', ['faq' => $faq, 'active_section' => $section, 'active_header' => false]);
        } else {
            $this->render('index', ['faq' => $faq, 'active_header' => true]);
        }
    }
}

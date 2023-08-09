<?php

namespace LGPD;

use DateTime;
use MapasCulturais\App;

class Controller  extends \MapasCulturais\Controller
{

    function __construct()
    {
    }

    public function GET_view() {
        $app = App::i();
        $this->layout = 'lgpdV2';
        $config_lgpd = $app->config['module.LGPD'];

        $slug = $this->data[0] ?? false;
        
        if(!isset($config_lgpd[$slug])) {
            $app->pass();
        }

        $this->render('view', ['config' => $config_lgpd[$slug]]);
    }

    public function GET_accept()
    {
        $app = App::i();
        $config_lgpd = $app->config['module.LGPD'];
        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $term_slug = $this->data[0] ?? null;
            /** @todo Verificar term_slug */
            $config = $config_lgpd[$term_slug];
    
            $url = $this->createUrl('accept', [$term_slug]);
            $title = $config['title'];
            $text = $config['text'];
            $hashText =  Module::createHash($text);
            $accepted = false;
            if (!$app->user->is('guest')) {
                $metadata_key = 'lgpd_' . $term_slug;
                $_accept_lgpd = $app->user->$metadata_key;
                if (is_object($_accept_lgpd)) {
                    foreach ($_accept_lgpd as $key => $value) {
                        if ($key == $hashText) {
                            $accepted = $value;
                            continue;
                        }
                    }
                }
            }
    
            $this->layout = 'lgpd';
            $app->view->enqueueStyle('app', 'lgpd-file', 'css/lgpd.css');
            $this->render('accept', ['url' => $url, 'title' => $title, 'text' => $text, 'accepted' => $accepted]);
        } else {
            $this->layout = 'lgpdV2';
            $this->render('terms', ['terms' => $config_lgpd]);
        }
       
    }

    public function POST_accept()
    {
        $app = App::i();        
        $terms_slug = null;
        $terms_slug = !is_array($this->data[0]) ? [$this->data[0]] : $this->data[0];
        $app->modules['LGPD']->acceptTerms($terms_slug);

        if ($app->view instanceof \MapasCulturais\Themes\BaseV1\Theme) {
            $url = $app->createUrl("painel", "index");
            $app->redirect($url);
        }else{
            $url =  $_SESSION[Module::key] ?? "/";
            $this->json(['redirect' =>  $url]);
        }
    }
}
